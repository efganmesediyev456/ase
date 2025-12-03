<?php

namespace App\Models;

use App\Models\Extra\Notification;
use App\Models\Extra\SMS;
use App\Services\AzeriExpress\CourierService;
use App\Services\Kuryera\KuryeraService;
use App\Services\Package\PackageService;
use App\Traits\ModelEventLogger;
use Auth;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Log;

class CD extends Model
{
    use SoftDeletes;
    use ModelEventLogger;

    protected $table = 'courier_deliveries';
    protected $query_packages_str;
    public $dates = ['deleted_at'];
    protected $fillable = ['user_id'];
    public $uploadDir = 'uploads/cd/';

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($query) {
            $packages = null;
            $tracks = null;
            $courier = null;
            if (isset($query->courier_id))
                $courier = Courier::find($query->courier_id);
            if (isset($query->packages_txt) && $query->packages_txt) {
                $packages_arr = [];
                //foreach (explode(',', $query->packages_txt) as $packageStr) {
                foreach (preg_split('/[:,\s]+/', $query->packages_txt, -1, PREG_SPLIT_NO_EMPTY) as $packageStr) {
                    if (!empty($str)) $str .= ',';
                    $packages_arr[] = trim($packageStr);
                }
                $packages = Package::with(['user'])->whereNull('deleted_at')->whereIn('custom_id', $packages_arr)->get();
                $tracks = Track::with(['customer'])->whereNull('deleted_at')->whereIn('tracking_code', $packages_arr)->get();
            }
            if (isset($query->not_delivered_status) && $query->not_delivered_status) {
                $query->status = 7;
            }
            /*if (isset($query->courier_id) && $query->courier_id) {
                if ($query->direction != 3 || $query->status == 2) { // if iherb send sms only if courier assigned
                    //CD::sendSMSToCourier(null, $query);
                    $query->courier_assigned_at = date('Y-m-d H:i:s');
                }
	    }*/
            if (isset($query->courier_id) && $query->courier_id) {
                $query->courier_assigned_at = date('Y-m-d H:i:s');
                CD::courierGet($query, $courier, $tracks, $packages);
            }
            if (isset($query->status) && $query->status == 4) {
                $query->delivered_at = date('Y-m-d H:i:s');
                $query->paid = 1;
            }
            if (isset($query->status) && $query->status == 3) {
                $query->courier_get_at = date('Y-m-d H:i:s');
            }
            if (isset($query->recieved) && $query->recieved)
                $query->recieved_at = date('Y-m-d H:i:s');
            if (isset($query->id) && $query->id && isset($query->delivery_price) && $query->delivery_price && $query->delivery_price > 0 && ($packages && count($packages) > 0)
                && isset($query->paid) && $query->paid && isset($query->recieved) && $query->recieved) {
                if ($query->direction != 3) // IHERB is free
                    Transaction::addCD($query->id, 'CASH');
            }

            if ($tracks && $courier) {

                $service = App::make(CourierService::class);
                CD::assignExternalCourier($courier, $tracks, $service);

            } else {
                Log::channel('azeriexpress')->error("Azeriexpress Courier or Tracks not found", [
                    'body' => $tracks,
                    'courier' => $courier,
                ]);
            }
            //CD::sendCreate($query);
        });

        static::updating(function ($query) {
            $packages = null;
            $tracks = null;
            $courier = null;
            if (isset($query->courier_id))
                $courier = Courier::find($query->courier_id);
            if (isset($query->packages_txt) && $query->packages_txt) {
                $packages_arr = [];
                //foreach (explode(',', $query->packages_txt) as $packageStr) {
                foreach (preg_split('/[:,\s]+/', $query->packages_txt, -1, PREG_SPLIT_NO_EMPTY) as $packageStr) {
                    if (!empty($str)) $str .= ',';
                    $packages_arr[] = trim($packageStr);
                }
                $packages = Package::with(['user'])->whereNull('deleted_at')->whereIn('custom_id', $packages_arr)->get();
                $tracks = Track::with(['customer'])->whereNull('deleted_at')->whereIn('tracking_code', $packages_arr)->get();
            }
            $_cd = CD::withTrashed()->find($query->id);
            if (isset($query->not_delivered_status) && $query->not_delivered_status && ($query->not_delivered_status != $_cd->not_delivered_status)) {
                //if ($query->status == $_cd->status) {
                if ($query->status != 7) { //if not not_delivered status
                    $query->status = 7; //set not_delivered status
                    //} else {
                    //    if ($query->status != 7)
                    //        $query->not_delivered_status = 0;
                }
            }
            /*if (isset($query->courier_id) && $query->courier_id && (!$_cd || !$_cd->courier_id || $_cd->courier_id != $query->courier_id)) {
                if ($query->direction != 3 || $query->status == 2) { // if iherb send sms only if courier assigned
                    //CD::sendSMSToCourier($_cd, $query);
                    $query->courier_assigned_at = date('Y-m-d H:i:s');
                }
            } elseif (($query->direction == 3 && $query->status == 2) && ($_cd && ($_cd->direction != $query->direction || $_cd->status != $query->status))) {
                //CD::sendSMSToCourier($_cd, $query);
                $query->courier_assigned_at = date('Y-m-d H:i:s');
	    }*/
            if (isset($query->courier_id)
                && $query->courier_id
                && (!$_cd || !$_cd->courier_id || $_cd->courier_id != $query->courier_id)
                && $query->status != 3)
            {
                $query->courier_assigned_at = date('Y-m-d H:i:s');
                $query->status = 2;
            }

            if (isset($query->status) && $query->status == 4 && (!$_cd || $_cd->status != 4)) {
                $query->delivered_at = date('Y-m-d H:i:s');
                if ($_cd && !$_cd->paid) {
                    $query->paid = 1;
                }
            }
            if (isset($query->status) && $query->status == 3 && (!$_cd || $_cd->status != 3)) {
                $query->courier_get_at = date('Y-m-d H:i:s');
                CD::courierGet($query, $courier, $tracks, $packages);
            }
            if (isset($query->recieved) && $query->recieved && (!$_cd || !$_cd->recieved)) {
                $query->recieved_at = date('Y-m-d H:i:s');
            }
            if ($query->direction != 3) {// IHERB is free
                $tr = Transaction::where('custom_id', $query->id)->where('paid_for', 'COURIER_DELIVERY')->first();
                if (isset($query->id)
                    && $query->id
                    && isset($query->delivery_price) && $query->delivery_price && $query->delivery_price > 0
                    && ($packages && count($packages) > 0)
                    && isset($query->paid) && $query->paid
                    && isset($query->recieved) && $query->recieved
                ) {
                    if ($tr && $tr->paid_by == 'CASH') {
                        $tr->admin_id = Auth::user()->id;
                        $tr->save();
                    } else if (($tr && $tr->type == 'ERROR') || !$tr) {
                        $tr_paid_by = 'CASH';
                        if ($tr && $tr->type == 'ERROR') {
                            $tr_paid_by = $tr->paid_by;
                            $tr->delete();
                        }
                        Transaction::addCD($query->id, $tr_paid_by);
                    } else if ($tr && $tr->paid_by == 'PORTMANAT') {
                        if ($query->status == 4) { //If PORTMANAT and delivered then Done
                            $query->status = 6;
                        }
                    }
                } else {
                    if ($tr && $tr->paid_by == 'CASH') {
                        $tr->delete();
                    }
                }
            }
            if (isset($query->id) && $query->id && isset($query->status) && (!$_cd || $_cd->status != $query->status)) { // status changed
                if ($packages) {
                    foreach ($packages as $package) {
                        if ($query->status == 4 || $query->status == 6) { // Done
                            if ($package->status != 3) {
                                $package->status = 3;
                                $package->save();
                            }
                        }
                        if ($query->status == 3) { // Taken by Courier
                            if ($package->cell) {
                                $package->cell = NULL;
                                $package->save();
                            }
                            if ($package->shelf_id) {

                                $createLog = new CourierShelfLog();
                                $createLog->custom_id = $package->id;
                                $createLog->type = 'packages';
                                $createLog->admin_id = $query->courier_id ?? null;
                                $createLog->comment = 'packages' . ' ' . $package->custom_id . ' Out For Delivery';
                                $createLog->save();

                                $package->shelf_id = null;
                                $package->save();
                            }
                            Notification::sendPackage($package->id, 'courier_picked_up');
                        }
                    }
                }
                if ($tracks) {
                    foreach ($tracks as $track) {
                        if ($query->status == 4 || $query->status == 6) { // Done
                            if ($track->status != 17) { // Done
                                $track->status = 17;
                                $track->save();
                                Log::channel('azeriexpress')->debug("Azeriexpress GFS status update", [
                                    'track' => $track
                                ]);
                                if ($track->partner_id == 3) { //Ozon Unitrade
                                    (new PackageService())->updateStatus($track, 50);
                                } else {
                                    (new PackageService())->updateStatus($track, 17);
                                }
                            }
                        }
                        if ($query->status == 3) { // Taken by Courier
                            if ($track->status != 21 && !($track->status == 19 && $track->partner_id == 3)) { // Out For Delivery
                                $track->status = 21;
                                if ($track->cell) {
                                    $track->cell = NULL;
                                }
                                if ($track->shelf_id) {

                                    $createLog = new CourierShelfLog();
                                    $createLog->custom_id = $track->id;
                                    $createLog->type = 'tracks';
                                    $createLog->admin_id = $query->courier_id ?? null;
                                    $createLog->comment = 'tracks' . ' ' . $track->tracking_code . ' Out For Delivery';
                                    $createLog->save();

                                    $track->shelf_id = NULL;
                                }
                                $track->save();
                                (new PackageService())->updateStatus($track, 21);
                            }
                            if ($track->cell) {
                                $track->cell = NULL;
                                $track->save();
                            }
                            if ($track->shelf_id) {

                                $createLog = new CourierShelfLog();
                                $createLog->custom_id = $track->id;
                                $createLog->type = 'tracks';
                                $createLog->admin_id = $query->courier_id ?? null;
                                $createLog->comment = 'tracks' . ' ' . $track->tracking_code . ' Out For Delivery';
                                $createLog->save();

                                $track->shelf_id = NULL;
                                $track->save();
                                Notification::sendTrack($track->id, 'courier_picked_up');
                            }
                            Notification::sendTrack($track->id, 'courier_picked_up');
                        }
                        if ($query->status == 7) { // Not Delivered
                            if ($track->status != 22) { // Failed attempt
                                if ($query->not_delivered_status)
                                    (new PackageService())->updateStatus($track, $query->not_delivered_status);
                                else
                                    (new PackageService())->updateStatus($track, 22);
                                $track->status = 22;
                                $track->save();
                            }
                        }
                    }
                }
            }
            if ($tracks && $courier) {
                $service = App::make(CourierService::class);
                CD::assignExternalCourier($courier, $tracks, $service);
            } else {
                Log::channel('cs')->error("Courier or Tracks not found", [
                    'body' => $tracks,
                    'courier' => $courier,
                ]);
            }
        });
    }

    public static function courierGet($cd, $courier, $tracks, $packages)
    {
        return;
    }

    public static function assignExternalCourier($courier, $tracks, CourierService $service)
    {
        if ($courier->email == 'azeriexpress@ase.az') {
            $service = App::make(CourierService::class);
            self::assignAzeriexpressCourier($tracks, $service);
        }

        if ($courier->email == "kuryera@ase.az") {
            $service = App::make(KuryeraService::class);
            self::assignKuryeraCourier($tracks, $service);
        }

        return;
    }

    public static function assignKuryeraCourier($parcels, KuryeraService $service)
    {
        $messages = [];
        foreach ($parcels as $parcel) {
            $data[] = [
                "hub_uuid" => "SR1",
                "payment_method" => 1,
                "pickup" => [
                    "uuid" => "SR11",
                    "phone" => "994556130398",
                    "address" => "Baku, Azerbaijan, 11b",
                    "lat" => "40.413135",
                    "long" => "49.853529"
                ],
                "customer" => [
                    "code" => "" . $parcel->customer->id,
                    "firstname" => explode(" ", $parcel->fullname)[0],
                    "lastname" => explode(" ", $parcel->fullname)[1],
                    "fullname" => $parcel->customer->fullname,
                    "passport" => $parcel->customer->passport ?? "-",
                    "fincode" => $parcel->customer->fincode ?? "-",
                    "phone" => $parcel->phone
                ],
                "parcel" => [
                    "barcode" => $parcel->tracking_code,
                    "weight" => $parcel->weight,
                    "price" => $parcel->shipping_amount,
                    "currency" => $parcel->currency,
                    "cod_amount" => 0.00,
                    "cod_delivery" => $parcel->delivery_price,
                    "cash_collection" => 0.00,
                    "is_paid" => false,
                    "lat" => $parcel->latitude ?? null,
                    "long" => $parcel->longitude ?? null,
                    "address" => $parcel->address
                ]
            ];
            $response = $service->createOrder($data);

            if (!$response['success']) {
                $messages[] = ['success' => false, 'message' => $response['message']];
            } else {
                $_track = Track::find($parcel->id);
                $_track->bot_comment = "Bağlama Kuryera-ya göndərildi!";
                $_track->save();
                Log::channel('cs')->info("Kuryera Courier CreateOrder Success", [
                    'response' => $response,
                    'body' => $data
                ]);
            }
        }
        if (count($messages)) {
            Log::channel('cs')->error("Kuryera Courier CreateOrder Error", $messages);
        }
        return $messages;
    }

    public static function assignAzeriexpressCourier($tracks, CourierService $service)
    {
        $messages = [];
        foreach ($tracks as $track) {
            $data = [
                'pickup_latitude' => 40.413135,
                'pickup_longitude' => 49.853529,
                'delivery_latitude' => $track['latitude'] ?? 40.3777421,
                'delivery_longitude' => $track['longitude'] ?? 46.1231029,
                'transport' => 1,
                'weight' => $track['weight'],
                'priority' => 2,
                'sender_name' => 'Aseshop',
                'receiver_name' => $track['fullname'],
//                'sender_mobile' =>  Optional,
//                'sender_email' =>  Optional,
//                'receiver_email' =>  Optional,
                'receiver_mobile' => $track['phone'],
//                'pickup_instructions' =>  Optional,
                'delivery_instructions' => $track['address'],
//                'package_contents' =>  Optional,
                'barcode' => $track['tracking_code'],
            ];

            $response = $service->createOrder($data);
            if ($response['success'] == false) {
                $messages[] = ['success' => false, 'message' => $response['error_description']];
            } else {
//                burda kuryere teyin olunur containeri burda yarat
                (new  PackageService())->addPackageToCourierContainer('azeriexpress', $track->azeriexpress_office_id, $track->tracking_code);
                Log::channel('azeriexpress')->info("Azeriexpress Courier CreateOrder Success", [
                    'response' => $response,
                    'body' => $data
                ]);
            }
        }
        if (count($messages)) {
            Log::channel('azeriexpress')->error("Azeriexpress Courier CreateOrder Error", $messages);
        }
        return $messages;
    }

    public static function newCD($track, $courier_id, $cd_status)
    {

        $findCdMessage = CD::where('container_id', $track->container_id)->where('phone', $track->phone)->first();

        if (!$findCdMessage) {
            Notification::sendTrack($track->id, 'tracking_courier_delivery');
        }

        $cd = new CD();
        $cd->direction = 3; // iherb
        $cd->status = $cd_status;
        $cd->courier_id = $courier_id;
        $cd->delivery_price = 0; // iherb free
        $cd->address = $track->address;
        $cd->name = $track->fullname;
        $cd->phone = $track->phone;
        $cd->customer_id = $track->customer_id;
        $cd->container_id = $track->container_id;
        $cd->packages_txt = $track->tracking_code;
        $cd->delivery_price = $track->courier_delivery_price;
        if ($track->partner_id == 5 || $track->partner_id == 6) { //CSE RU or ASE Express TR
            $cd->direction = 1; //import
        }
        return $cd;
    }

    public function getPhotoUrlAttribute()
    {
        return $this->photo ? asset($this->uploadDir . $this->photo) : asset($this->uploadDir . 'no-image.png');
    }

    public function getLocationUrlAttribute()
    {
        return $this->latitude && $this->longitude ? 'https://maps.google.com/?q=' . $this->latitude . ',' . $this->longitude : NULL;
    }

    public function getAddrLocationUrlAttribute()
    {
        return $this->addr_latitude && $this->addr_longitude ? 'https://maps.google.com/?q=' . $this->addr_latitude . ',' . $this->addr_longitude : NULL;
    }

    public function getPhotoUrl2Attribute($value)
    {
        if ($this->photo) {
            $html = '<a target="_blank" style="text-decoration: none;" href="' . $this->photo_url . '"><font color="blue">Yes</font></a>';
            echo $html;
            return;
        }
        return 'No';
    }

    public function getLocationUrl2Attribute($value)
    {
        $url = $this->location_url;
        if ($url) {
            $html = '<a target="_blank" style="text-decoration: none;" href="' . $url . '"><font color="blue">Yes</font></a>';
            echo $html;
            return;
        }
        return 'No';
    }

    public function getAddrLocationUrl2Attribute($value)
    {
        $url = $this->addr_location_url;
        if ($url) {
            $html = '<a target="_blank" style="text-decoration: none;" href="' . $url . '"><font color="blue">Yes</font></a>';
            echo $html;
            return;
        }
        return 'No';
    }

    public static function popTrack($cd, $track)
    {
        if (!$cd) return NULL;
        if ($cd->packages_txt == $track->tracking_code) {
            return $cd;
        }
        $packages_txt = '';
        //foreach(explode(',',trim($cd->packages_txt)) as $tracking_code) {
        foreach (preg_split('/[:,\s]+/', $cd->packages_txt, -1, PREG_SPLIT_NO_EMPTY) as $tracking_code) {
            if ($tracking_code != $track->tracking_code) {
                if ($packages_txt == '') $packages_txt = $tracking_code;
                else $packages_txt .= ',' . $tracking_code;
            }
        }
        $cd->packages_txt = $packages_txt;
        $cd->save();
        $cd2 = new CD();
        $cd2->direction = $cd->direction;
        $cd2->status = $cd->status;
        $cd2->not_delivered_status = $cd->not_delivered_status;
        $cd2->courier_id = $cd->courier_id;
        $cd2->delivery_price = $cd->delivery_price;
        $cd2->address = $cd->address;
        $cd2->name = $cd->name;
        $cd2->phone = $cd->phone;
        $cd2->packages_txt = $track->tracking_code;
        $cd2->save();
        $track->courier_delivery_id = $cd2->id;
        $track->save();
        return $cd;
    }

    public static function removeTrack($cd, $track)
    {
        if (!$cd) return NULL;
        if ($cd->packages_txt == $track->tracking_code) {
            $cd->delete();
            return NULL;
        }
        $packages_txt = '';
        //foreach(explode(',',trim($cd->packages_txt)) as $tracking_code) {
        foreach (preg_split('/[:,\s]+/', $cd->packages_txt, -1, PREG_SPLIT_NO_EMPTY) as $tracking_code) {
            if ($tracking_code != $track->tracking_code) {
                if ($packages_txt == '') $packages_txt = $tracking_code;
                else $packages_txt .= ',' . $tracking_code;
            }
        }
        $cd->packages_txt = $packages_txt;
        $cd->save();
        $track->courier_delivery_id = NULL;
        $track->save();
        return $cd;
    }

    public static function updateTrack($cd, $track, $courier_id)
    {
        if (!$cd) return NULL;
        if ($cd->packages_txt == $track->tracking_code) {
            if ($cd->courier_id != $courier_id) {
                $cd->delete();
                $track->courier_delivery_id = NULL;
                $track->save();
                return NULL;
            }
            $cd->address = $track->address;
            //$cd->courier_id=$courier_id;
            return $cd;
        }
        $packages_txt = '';
        //foreach(explode(',',trim($cd->packages_txt)) as $tracking_code) {
        foreach (preg_split('/[:,\s]+/', $cd->packages_txt, -1, PREG_SPLIT_NO_EMPTY) as $tracking_code) {
            if ($tracking_code != $track->tracking_code) {
                if ($packages_txt == '') $packages_txt = $tracking_code;
                else $packages_txt .= ',' . $tracking_code;
            }
        }
        $cd->packages_txt = $packages_txt;
        $cd->save();
        $track->courier_delivery_id = NULL;
        $track->save();
        return NULL;
    }

    public static function sendCreate($query)
    {
        //file_put_contents('/var/log/ase_test.log',"create courier delivery\n",FILE_APPEND);
        $packages_str = '';
        $name = '';
        $address = '';
        $phone = '';
        $status = '';
        $paid = '';
        $delivery_price = null;
        $courier = null;
        if (isset($query->courier_id))
            $courier = Courier::find($query->courier_id);
        if (isset($query->package_with_cell_str) && !empty($query->package_with_cell_str))
            $packages_str = $query->package_with_cell_str;
        if (empty($packages_str) && isset($query->packages_txt) && !empty($query->packages_txt))
            $packages_str = $query->packages_txt;
        if (isset($query->name) && !empty($query->name))
            $name = $query->name;
        if (isset($query->address) && !empty($query->address))
            $address = $query->address;
        if (isset($query->phone) && !empty($query->phone))
            $phone = $query->phone;
        if (isset($query->status) && $query->status)
            $status = config('ase.attributes.cd.status')[$query->status];
        if (isset($query->paid))
            $paid = config('ase.attributes.package.paid')[$query->paid];
        if (isset($query->delivery_price) && $query->delivery_price)
            $delivery_price = $query->delivery_price;
        $str = "🔗 <a href='https://admin." . env('DOMAIN_NAME') . "/courier_deliveries'><b>New Courier Delivery</b></a>\n ";
        if (!empty($packages_str))
            $str .= "Packages: " . $packages_str . " ";
        if (!empty($name))
            $str .= "Recipient: " . $name . " ";
        if (!empty($address))
            $str .= "Address: " . $address . " ";
        if (!empty($phone))
            $str .= "Phone: " . $phone . " ";
        if (!empty($status))
            $str .= "Status: " . $status . " ";
        if (!empty($paid))
            $str .= "Paid: " . $paid . " ";
        if ($delivery_price)
            $str .= "Price: " . $delivery_price . " ";
        //file_put_contents('/var/log/ase_test.log',$str." \n",FILE_APPEND);
        sendTGMessage($str);
    }

    public static function sendSMSToCourier($_cd, $query)
    {
        $packages_str = '';
        $name = '';
        $address = '';
        $phone = '';
        $status = '';
        $paid = '';
        $delivery_price = null;
        $courier = null;
        if (isset($query->courier_id))
            $courier = Courier::find($query->courier_id);
        if (!$courier || empty($courier->phone)) return;
        if (isset($query->package_with_cell_str) && !empty($query->package_with_cell_str))
            $packages_str = $query->package_with_cell_str;
        if (empty($packages_str) && isset($query->packages_txt) && !empty($query->packages_txt))
            $packages_str = $query->packages_txt;
        else if ($_cd && !empty($_cd->package_with_cell_str))
            $packages_str = $_cd->package_with_cell_str;
        if (isset($query->name) && !empty($query->name))
            $name = $query->name;
        else if ($_cd && !empty($_cd->name))
            $name = $_cd->name;
        if (isset($query->address) && !empty($query->address))
            $address = $query->address;
        else if ($_cd && !empty($_cd->address))
            $address = $_cd->address;
        if (isset($query->phone) && !empty($query->phone))
            $phone = $query->phone;
        else if ($_cd && !empty($_cd->phone))
            $phone = $_cd->phone;
        if (isset($query->status) && $query->status)
            $status = config('ase.attributes.cd.status')[$query->status];
        else if ($_cd && $_cd->status)
            $status = config('ase.attributes.cd.status')[$_cd->status];
        if (isset($query->paid))
            $paid = config('ase.attributes.package.paid')[$query->paid];
        else if ($_cd)
            $paid = config('ase.attributes.package.paid')[$_cd->paid];
        if (isset($query->delivery_price) && $query->delivery_price)
            $delivery_price = $query->delivery_price;
        else if ($_cd && $_cd->delivery_price)
            $delivery_price = $_cd->delivery_price;
        $str = "New Courier Delivery ";
        if (!empty($packages_str))
            $str .= "Packages: " . $packages_str . " ";
        if (!empty($name))
            $str .= "Recipient: " . $name . " ";
        if (!empty($address))
            $str .= "Address: " . $address . " ";
        if (!empty($phone))
            $str .= "Phone: " . $phone . " ";
        if (!empty($status))
            $str .= "Status: " . $status . " ";
        if (!empty($paid))
            $str .= "Paid: " . $paid . " ";
        if ($delivery_price)
            $str .= "Price: " . $delivery_price . " ";
        SMS::sendPureTextByNumber($courier->phone, $str);
    }

    public function packages()
    {
        return $this->hasMany('App\Models\Package', 'courier_delivery_id');
    }

    public function tracks()
    {
        return $this->hasMany('App\Models\Track', 'courier_delivery_id');
    }

    public function getFirstTrackAttribute()
    {
        if ($this->tracks && count($this->tracks) > 0)
            return $this->tracks[0];
        return NULL;
    }

    public function getCourierIdWithLabelAttribute()
    {
        if (!$this->courier)
            return '';
        return $this->courier->name;
    }

    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }

    public function getStatusWithLabelAttribute()
    {
        $status = $this->attributes['status'];
        if (!$status)
            return '';
        $arr = config('ase.attributes.cd.statusCd');
        if (array_key_exists($status, $arr))
            return $arr[$status];
        $arr = config('ase.attributes.cd.status2');
        if (array_key_exists($status, $arr))
            return $arr[$status];
        return 'Unknown (' . $status . ')';

    }

    public function getNotDeliveredStatusWithLabelAttribute()
    {
        $status = $this->attributes['not_delivered_status'];
        if (!$status)
            return '';
        $arr = config('ase.attributes.cd.notDeliveredStatusCd');
        if (array_key_exists($status, $arr))
            return $arr[$status];
        $arr = config('ase.attributes.cd.notDeliveredStatus');
        if (array_key_exists($status, $arr))
            return $arr[$status];
        return 'Unknown (' . $status . ')';

    }

    public function getParcelNameAttribute()
    {
        foreach ($this->packages as $package) {
            return $package->parcel_name;
        }
        foreach ($this->tracks as $track) {
            return $track->container_name;
        }
        return NULL;
    }

    public function getPackagesStrAttribute()
    {
        $str = "";
        foreach ($this->packages as $package) {
            if (!empty($str)) $str .= ", ";
            $str .= $package->custom_id;
        }
        foreach ($this->tracks as $track) {
            if (!empty($str)) $str .= ", ";
            $str .= $track->tracking_code;
        }
        if (empty($str))
            $str = $this->packages_txt;
        return $str;
    }

    public function getPackagesWithCellsStrAttribute()
    {
        $str = "";
        foreach ($this->packages as $package) {
            if (!empty($str)) $str .= ", ";
            $str .= $package->custom_id;
            if ((($package->status == 2) || ($package->status == 8)) && !empty($package->cell)) {
                $str .= ' (' . $package->cell . ')';
            } else
                $str .= ' (' . $package->statusWithLabel . ')';
        }
        foreach ($this->tracks as $track) {
            if (!empty($str)) $str .= ", ";
            $str .= $track->tracking_code;
            //if (($track->status == 16) || ($track->status == 20)) {
            //    if (!empty($track->cell))
            //        $str .= ' (' . $track->cell . ')';
            //} else
            if ($track->status != 21)
                $str .= ' (' . $track->statusWithLabel . ')';
        }
        if (empty($str))
            $str = $this->packages_txt;
        return $str;
    }

    public function getPackagesWithCellsBrStrAttribute()
    {
        return $this->getPackagesWithCellsBrStr(2);
    }

    public function getPackagesWithCellsOneBrStrAttribute()
    {
        return $this->getPackagesWithCellsBrStr(1);
    }

    public function getPackagesWithCellsNlStrAttribute()
    {
        return $this->getPackagesWithCellsBrStr(2, "\n");
    }

    public function getDeliveryPriceWithColorAttribute()
    {
        if ($this->delivery_price > 0 && ($this->direction == 1 || $this->direction == 2) && $this->recieved == 0 && $this->status == 6)
            return '<b style="color:red;">' . strval(round($this->delivery_price, 2)) . ' azn</b>';
        if ($this->delivery_price > 0)
            return strval(round($this->delivery_price, 2)) . ' azn';
        return '--';
    }

    public function getPackagesWithCellsBrStr($maxBr, $brStr = '<br>')
    {

        $str = "";
        $numBr = 0;
        foreach ($this->packages as $package) {
            if (!empty($str)) {
                $str .= ", ";
                if (!$numBr)
                    $str .= $brStr;
            }
            if ($package->store_status && $package->store_status != 2) {
                $str .= '<b style="color:red;">' . $package->custom_id . "</b>";
            } else {
                $str .= $package->custom_id;
            }
            //$str .= $package->custom_id;
            if ((($package->status == 2) || ($package->status == 8)) && !empty($package->cell)) {
                $str .= ' (' . $package->cell . ')';
            } else
                $str .= ' (' . $package->statusWithLabel . ')';
            $numBr++;
            if ($numBr >= $maxBr) $numBr = 0;
        }
        foreach ($this->tracks as $track) {
            if (!empty($str)) {
                $str .= ", ";
                if (!$numBr)
                    $str .= $brStr;
            }
            $str .= $track->tracking_code;
            //if (($track->status == 16) || ($track->status == 20)) {
            //    if (!empty($track->cell))
            //        $str .= ' (' . $track->cell . ')';
            //} else
            $str .= ' (' . $track->statusWithLabel . ')';
            $numBr++;
            if ($numBr >= $maxBr) $numBr = 0;
        }
        if (empty($str))
            $str = $this->packages_txt;
        return $str;
    }

    public function getDirectionWithLabelAttribute($value)
    {
        //if(!$this->direction) return '';
        return config('ase.attributes.cd.direction')[$this->direction];
    }

    public function getInvoiceTypeWithLabelAttribute($value)
    {
        if (!$this->invoice_type) return '';
        return config('ase.attributes.cd.invoice')[$this->invoice_type];
    }

    public function updatePackages($packagesStr)
    {
        DB::update('update packages set courier_delivery_id=NULL where courier_delivery_id=?', [$this->id]);
        DB::update('update tracks set courier_delivery_id=NULL where courier_delivery_id=?', [$this->id]);
        if (empty($packagesStr))
            return;
        $str = '';
        //foreach (explode(',', $packagesStr) as $packageStr) {
        foreach (preg_split('/[:,\s]+/', $packagesStr, -1, PREG_SPLIT_NO_EMPTY) as $packageStr) {
            if (!empty($str)) $str .= ',';
            $str .= "'" . trim($packageStr) . "'";
        }
        if (!empty($str))
            //DB::update('update packages set courier_delivery_id=? where user_id = ? and custom_id in ('.$str.')',[$this->id,$this->user_id]);
            DB::update('update packages set courier_delivery_id=? where custom_id in (' . $str . ')', [$this->id]);
        DB::update('update tracks set courier_delivery_id=? where tracking_code in (' . $str . ')', [$this->id]);
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User')->withTrashed();
    }

    public function courier()
    {
        return $this->belongsTo('App\Models\Courier')->withTrashed();
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'custom_id')->where('type', 'OUT')->where('paid_for', 'COURIER_DELIVERY')->latest();
    }

    public function courierTrackOzonDeliveryTransactions()
    {
        return $this->hasMany(Transaction::class, 'custom_id')->where('type', 'OUT')->where('paid_for', 'COURIER_TRACK_OZON_DELIVERY')->latest();
    }

    public function getPaidByAttribute()
    {
        return $this->attributes['paid'] ? ($this->transaction ? $this->transaction->paid_by : '-') : "-";
    }
}

