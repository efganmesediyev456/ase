<?php

namespace App\Services\Kargomat;

use App\Models\Package;
use App\Models\Track;
use App\Models\Kargomat\KargomatOffice;
use App\Models\Kargomat\KargomatOrder;
use App\Models\Kargomat\KargomatPackage;
use App\Services\Interfaces\PackageServiceInterface;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Log;
//use function App\Services\Surat\now;

class KargomatService implements PackageServiceInterface
{
    const STATUSES_IDS = [
        "WAITING" => 0,
        "SENT" => 1,
        "WAREHOUSE" => 2,
        "ONWAY" => 3,
        "ARRIVEDTOPOINT" => 4,
        "DELIVERED" => 5,
        "NOT_DELIVERED" => 6,
        "" => 7,
        "COURIER_ASSIGNED" => 8,
    ];
    const STATUS_MAP = [
        2 => "WAREHOUSE",
        3 => "ONWAY",
        4 => "ARRIVEDTOPOINT",
        5 => "DELIVERED",
        6 => "NOT_DELIVERED",
    ];

    const REASONS = [
        1 => 'Müştəriyə zəng çatmır',
        2 => 'Müştəri sifarişdən imtina etdi',
        3 => 'Müştəri ünvanda deyil',
        4 => 'Sifariş müştəriyə aid deyil',
        5 => 'Ünvan dəqiq/tam deyil'
    ];

    public function validatePackage($package, $type = null,$return=false)
    {
        $packageType = $package instanceof Package ? 'package' : 'track';
        $exists = KargomatPackage::where('package_id', $package->id)->where('type',$packageType)->exists();
        if ($exists) {
            return "Bağlama artıq konteynerə əlavə olunub!";
        }

        return null;
    }

    public function createPackage($container, $package)
    {
        $packageType = $package instanceof Package ? 'package' : 'track';
        KargomatPackage::updateOrCreate([
            'package_id' => $package->id,
            'barcode' => $package->tracking,
            'type' => $packageType
        ], [
            'kargomat_order_id' => $container->id,
            'user_id' => $package->user_id,
            'package_id' => $package->id,
            'status' => KargomatPackage::STATUSES['NOT_SENT'],
            'added_by' => Auth::user()->id,
            'barcode' => $package->tracking,
            'payment_status' => $package->paid
        ]);

        KargomatOrder::query()
            ->where('id', $container->id)
            ->update([
                'weight' => floatval($container->weight) + floatval($package->weight),
            ]);

        if ($packageType == 'package') {
            $_package = Package::find($package->id);
            $_package->bot_comment = "Bağlama Kargomat Konteynerə($container->id) əlavə olundu";
            $_package->save();
        }

        if ($packageType == 'track') {
            $_track = Track::find($package->id);
            $_track->comment_txt = "Bağlama Kargomat Konteynerə($container->id) əlavə olundu";
            $_track->save();
        }
    }

    public function updateOrderPayment($barcode)
    {

    }

    public function getContainer($officeId)
    {
        $office = KargomatOffice::where('id', $officeId)->first();
        $container = KargomatOrder::
//            where('kargomat_office_id', $office->id)
            where('status', KargomatOrder::STATUSES['WAITING'])
            ->latest()
            ->first();
        if (!$container) {
            $authId = Auth::user()->id;
            $container = KargomatOrder::create([
//                'name' => $office->name . '-' . now()->format('d-m-Y'),
                'name' => 'Kargomat' . '-' . now()->format('d-m-Y'),
                'user_id' => $authId,
                'kargomat_office_id' => $office->id,
                'status' => KargomatOrder::STATUSES['WAITING'],
                'barcode' => $this->generateNewBarcode(),
                'created_at' => now()
            ]);
        }

        return $container;
    }

    public function generateNewBarcode(): string
    {
        $newBarcode = "ASEX" . str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);

        $exist = KargomatOrder::where('barcode', $newBarcode)->exists();
        if ($exist) {
            return $this->generateNewBarcode();
        }

        return $newBarcode;
    }
}
