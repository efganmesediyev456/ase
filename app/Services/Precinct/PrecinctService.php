<?php

namespace App\Services\Precinct;

use App\Models\DeliveryPoint;
use App\Models\Extra\Notification;
use App\Models\Package;
use App\Models\Precinct\PrecinctOrder;
use App\Models\Precinct\PrecinctPackage;
use App\Models\Track;
use App\Services\Interfaces\PackageServiceInterface;
use App\Services\Package\PackageService;
use Illuminate\Support\Facades\Auth;

class PrecinctService implements PackageServiceInterface
{
    public function acceptPackage($barcode)
    {
        $package = PrecinctPackage::query()
            ->where([
                'barcode' => $barcode,
            ])->first();

        if (!$package) {
            return false;
        }

//        if ($package->status == PrecinctPackage::STATUSES['HAS_PROBLEM']) {
//            return false;
//        }
//        [PrecinctPackage::STATUSES['IN_PROCESS'], PrecinctPackage::STATUSES['WAREHOUSE'],

        if (in_array($package->status, [PrecinctPackage::STATUSES['ARRIVEDTOPOINT']])) {
            return false;
        }

        $package->update([
            'accepted_at' => now(),
            'status' => PrecinctPackage::STATUSES['ARRIVEDTOPOINT']
        ]);

        if ($package->type == 'package') {
            $_package = Package::find($package->package_id);
            $_package->bot_comment = "Bağlama Precinct(" . Auth::id() . '-' . Auth::user()->email . ") tərəfindən qəbul edildi";
            $_package->status = Package::STATES['InBaku'];
            $_package->save();
            $cd = $_package->courier_delivery;

            if(in_array($_package->store_status,[3,4,7,8]) && $_package->paid == 0){
                //niyese burda bildirim gedir asagidaki 2 filiala ona esasen yazilib
                if ($_package->store_status != 2 || $_package->store_status != 1){
                    Notification::sendPackage($package->package_id, 'Precint_notpaid');
                }
            }

            if ( !($cd && $cd->courier &&  Auth::user()->store_status == 2 && $_package->store_status == 2) ) { // don't do it if package have courier and filial kobia and scanning admin is kobia
            	Notification::sendPackage($package->package_id, 2);//todo :must change to PrecinctPackage::STATUSES["ARRIVEDTOPOINT"]
	        }
        }

        if ($package->type == 'track') {
            $_track = Track::find($package->package_id);
            $_track->comment_txt = "Bağlama Precinct(" . Auth::id() . '-' . Auth::user()->email . ") tərəfindən qəbul edildi";
            $_track->status = Track::STATES['InBaku'];
            $_track->save();

                (new PackageService())->updateStatus($_track, 16);
            Notification::sendTrack($package->package_id, 16);//todo :must change to PrecinctPackage::STATUSES["ARRIVEDTOPOINT"]
        }

        $order = PrecinctOrder::query()->withCount(['acceptedPackages', 'packages'])->find($package->precinct_order_id);
        if ($order->accepted_packages_count == $order->packages_count) {
            $order->update([
                'status' => PrecinctOrder::STATUSES['ACCEPTED']
            ]);
        }
        return true;
    }

    public function updateOrderPayment($barcode)
    {
        // TODO: Implement updateOrderPayment() method.
    }

    public function getContainer($officeId)
    {

        $office = DeliveryPoint::query()->where('id', $officeId)->first();
        $container = PrecinctOrder::query()
            ->where('precinct_office_id', $office->id)
            ->where('status', PrecinctOrder::STATUSES['WAITING'])
            ->latest()
            ->first();
        if (!$container) {
            $authId = Auth::user()->id;
            $container = PrecinctOrder::query()->create([
                'name' => $office->name . '-' . now()->format('d-m-Y'),
                'user_id' => $authId,
                'precinct_office_id' => $office->id,
                'status' => PrecinctOrder::STATUSES['WAITING'],
                'barcode' => (new PrecinctService())->generateNewBarcode(),
                'created_at' => now()
            ]);
        }

        return $container;
    }

    public function generateNewBarcode(): string
    {
        // Generate a random 8-digit number
        $newBarcode = "ASEX" . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);

        $exist = PrecinctOrder::query()->where('barcode', $newBarcode)->exists();
        if ($exist) {
            return $this->generateNewBarcode();
        }

        return $newBarcode;
    }

    public function validatePackage($package, $type = null,$return = false)
    {
        if ($return){
           PrecinctPackage::query()->where('package_id', $package->id)->delete();
        }
        $packageType = $package instanceof Package ? 'package' : 'track';

        $exists = PrecinctPackage::query()->where('package_id', $package->id)->where('type',$packageType)->exists();
        if ($exists) {
            return "Bağlama artıq konteynerə əlavə olunub!";
        }

        return null;
    }

    public function createPackage($container, $package)
    {
        $packageType = $package instanceof Package ? 'package' : 'track';
        PrecinctPackage::query()->updateOrCreate([
            'package_id' => $package->id,
            'barcode' => $package->tracking,
            'type' => $packageType
        ], [
            'precinct_order_id' => $container->id,
            'user_id' => $package->user_id,
            'package_id' => $package->id,
            'status' => PrecinctPackage::STATUSES['NOT_SENT'],
            'added_by' => Auth::user()->id,
            'barcode' => $package->tracking,
            'payment_status' => $package->paid
        ]);

        PrecinctOrder::query()
            ->where('id', $container->id)
            ->update([
                'weight' => floatval($container->weight) + floatval($package->weight),
            ]);

        if ($packageType == 'package') {
            $_package = Package::find($package->id);
            $_package->bot_comment = "Bağlama Precinct Konteynerə($container->id) əlavə olundu";
            $_package->save();
        }

        if ($packageType == 'track') {
            $_track = Track::find($package->id);
            $_track->comment_txt = "Bağlama Precinct Konteynerə($container->id) əlavə olundu";
            $_track->save();
        }
    }
}
