<?php

namespace App\Console\Commands;

use App\Models\Extra\Notification;
use App\Models\Kargomat\KargomatOrder;
use App\Models\Kargomat\KargomatPackage;
use App\Models\Package;
use App\Models\Track;
use App\Services\Package\PackageService;
use Artisan;
use Illuminate\Console\Command;
use Log;
use Carbon\Carbon;


class Kargomat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kargomat {--type=send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kargomat integration';

    const STATUSES_IDS = [
        "WAITING" => 0,
        "SENT" => 1,
        "WAREHOUSE" => 8,
        "ONWAY" => 3,
        "ARRIVEDTOPOINT" => 4,
        "DELIVERED" => 5,
        "NOT_DELIVERED" => 6,
        "" => 7,
//        "COURIER_ASSIGNED" => 8,
    ];

    const STATUS_MAP = [
        3 => "ARRIVEDTOPOINT",
        2 => "DELIVERED",
        4 => "NOT_DELIVERED",
        5 => "NOT_DELIVERED",
        7 => "NOT_DELIVERED",
    ];

    //integration statuses
    // 0,1 - Sifarish yaradilib
    //2 - Elde edilib
    //3 - Yerleshdirilib
    //4 - Geri qaytarilib
    //5 - Sifarish legv edilib
    //7 - Geri tehvil verilecek

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->option('type') == 'sent') {
            $this->send();
        }

        if ($this->option('type') == 'test') {
            $this->test();
        }

        if ($this->option('type') == 'getStatuses') {
            $this->getStatuses();
        }
        if ($this->option('type') == 'getStatusesTest') {
            $this->getStatusesTest();
        }
    }

    public function test(){
        $phone = '559218470';
        $phone = clearNumber($phone,true);
        $phone = '+' . $phone;
        dd($phone);
        $body = [
            'externalId' =>'ASE0505915236232',
            'postamatId' => 5452,
            'number' => 'ASE0505915236232',
            'parcels' => [
                ['barcode' => 'ASE0505915236232']
            ],
            'notification' => [
                'phones' => ['+994559218470'],
                'emails' => ['sahin.hesenov.1999@gmail.com'],
            ],
        ];
        $this->info('kargomat request  - ' . json_encode($body));
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.smartix.pro/marketplace/v4/auth/delivery/order/create',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => array(
                'X-Smartix-Api-Key: fcf6f463-d436-4d1f-afd1-0a71ac4aef5a',
                'Content-Type: application/json'
            ),
        ));
        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        dd($response,$httpcode);
    }

    public function send()
    {
        $packages = KargomatPackage::with(['container.kargomatOffice', 'track.customer'])
            ->whereIn('status', [
                KargomatPackage::STATUSES['NOT_SENT'],
                KargomatPackage::STATUSES['HAS_PROBLEM'],
            ])
            ->where('company_sent', 1)
            ->get();

        if ($packages->count() < 1) {
            dd('boshdur');
        }
        $body = [];

        foreach ($packages as $package) {
            if ($package->type == 'package') {
                $_package = $package->package;
                $customer = $_package->user;
            } else {
                $_package = $package->track;
                $customer = $_package->customer;
            }
            $phone = $customer->phone != "" ? $customer->phone : $_package->phone;
            $phone = clearNumber($phone,true);
            $phone = '+' . $phone;
            $email = !empty($customer->email) ? $customer->email : ($_package->email ?? 'user' . rand(1000, 9999) . '@example.com');
            $body = [
                'externalId' => $package->barcode,
                'postamatId' => $_package->kargomat_office->foreign_id,
                'number' => $package->barcode,
                'parcels' => [
                    ['barcode' => $package->barcode]
                ],
                'notification' => [
                    'phones' => [$phone],
                    'emails' => [$email],
                ],
            ];
            $this->info('kargomat request  - ' . json_encode($body));
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.smartix.pro/marketplace/v4/auth/delivery/order/create',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => json_encode($body),
                CURLOPT_HTTPHEADER => array(
                    'X-Smartix-Api-Key: fcf6f463-d436-4d1f-afd1-0a71ac4aef5a',
                    'Content-Type: application/json'
                ),
            ));
            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($httpcode == 200) {
                KargomatPackage::query()
                    ->where('id', $package->id)
                    ->update([
                        'status' => KargomatPackage::STATUSES['SENT'],
                        'sent_at' => now()
                    ]);

                if ($package->type == 'package') {
                    $_package = Package::find($package->package_id);
                    $_package->bot_comment = "Bağlama Kargomat-a göndərildi";
                    $_package->save();

                } else if ($package->type == 'track') {
                    $_track = Track::find($package->package_id);
                    $_track->bot_comment = "Bağlama Kargomat-a göndərildi";
                    $_track->save();
                }
                $this->line("success . Tracking Number: " . $package->barcode);
            } else {
                KargomatPackage::query()
                    ->where('id', $package->id)
                    ->update([
                        'status' => KargomatPackage::STATUSES['HAS_PROBLEM'],
                        'comment' => $package->comment . '| ' . json_encode([$response])
                    ]);
                $this->warn("Order submission failed for Tracking Number: " . $package->barcode . "--- Error: " . json_encode($response));
            }
            curl_close($curl);

        }
    }

    public function getStatuses()
    {
        // 0,1 - Sifarish yaradilib
        //2 - Elde edilib
        //3 - Yerleshdirilib
        //4 - Geri qaytarilib
        //5 - Sifarish legv edilib
        //7 - Geri tehvil verilecek

        $packages = KargomatPackage::with(['container.kargomatOffice', 'track.customer'])
            ->whereNotIn('status', [
                KargomatPackage::STATUSES['DELIVERED'],
                KargomatPackage::STATUSES['NOT_SENT'],
                KargomatPackage::STATUSES['HAS_PROBLEM'],
            ])
            ->where('company_sent', 1)
            ->where('created_at', '>=', Carbon::now()->subMonths(2))
            ->get();

        if ($packages->count() < 1) {
            dd('boshdur');
        }

        $body = [];

        foreach ($packages as $package) {
            $body['externalIds'][] = $package->barcode;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.smartix.pro/marketplace/v4/auth/delivery/order/info-list?findSubOperation=false',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => array(
                'accept: application/json',
                'X-Smartix-Api-Key: fcf6f463-d436-4d1f-afd1-0a71ac4aef5a',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response);
        $orders = $response->orders;

        foreach ($orders as $order) {
            if (isset($order->info)) {
                if (isset($order->info->state)) {
                    $status = $order->info->state;
                    $order_id = $order->externalId;
                    $package = KargomatPackage::where('barcode', $order_id)->first();
                    $order = KargomatOrder::find($package->kargomat_order_id);
                    $order->update([
                        'status' => KargomatOrder::STATUSES['IN_PROCESS']
                    ]);

                    $entity = $this->getEntity($package->type, $package->package_id);
                    $dates = [];
//                    ARRIVEDTOPOINT
                    if ($status == 3) {
                        //arrived to point
                        $dates['arrived_at'] = now();
                        $this->updateArrivedToPudoStatus($entity);
                    }

                    if ($status == 2) {
                        //delivered
                        $dates['delivered_at'] = now();
                        $this->updateDeliveredStatus($entity, $order);
                    }

                    if (in_array($status, [0, 1, 4, 5, 7])) {
                        continue;
                    }

                }

                $package->update(
                    array_merge([
                        'status' => KargomatPackage::STATUSES[self::STATUS_MAP[$status]],
                        'comment' => "",
                    ],
                        $dates
                    )
                );

            }
        }
        dd($response);
    }

    public function getStatusesTest()
    {
        // 0,1 - Sifarish yaradilib
        //2 - Elde edilib
        //3 - Yerleshdirilib
        //4 - Geri qaytarilib
        //5 - Sifarish legv edilib
        //7 - Geri tehvil verilecek


            $body['externalIds'][] = 'ASE0505915236232';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.smartix.pro/marketplace/v4/auth/delivery/order/info-list?findSubOperation=false',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($body),
            CURLOPT_HTTPHEADER => array(
                'accept: application/json',
                'X-Smartix-Api-Key: fcf6f463-d436-4d1f-afd1-0a71ac4aef5a',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response);
        $orders = $response->orders;
        foreach ($orders as $order) {
            if (isset($order->info)) {
                if (isset($order->info->state)) {
                    $status = $order->info->state;
                    dd($status);
                    $order_id = $order->externalId;
//                    $package = KargomatPackage::where('barcode', $order_id)->first();
//                    $order = KargomatOrder::find($package->kargomat_order_id);
//                    $order->update([
//                        'status' => KargomatOrder::STATUSES['IN_PROCESS']
//                    ]);
//
//                    $entity = $this->getEntity($package->type, $package->package_id);
                    $dates = [];
                    if ($status == 3) {
                        //arrived to point
                        $dates['arrived_at'] = now();
                        $this->updateArrivedToPudoStatus($entity);
                    }

                    if ($status == 2) {
                        //delivered
                        $dates['delivered_at'] = now();
                        $this->updateDeliveredStatus($entity, $order);
                    }

                    if (in_array($status, [0, 1, 4, 5, 7])) {
                        continue;
                    }

                }

                $package->update(
                    array_merge([
                        'status' => KargomatPackage::STATUSES[self::STATUS_MAP[$status]],
                        'comment' => "",
                    ],
                        $dates
                    )
                );

            }
        }
        dd($response);
    }


    private
    function getEntity(string $type, int $packageId)
    {
        return $type === 'package' ? Package::find($packageId) : Track::find($packageId);
    }

    private
    function updateAcceptedStatus($entity)
    {
        (new PackageService())->updateStatus($entity, 20);
        $comment = "Bağlama(Kargomat) çeşidləmə mərkəzindədir.";
        $this->updateEntityStatus($entity, $comment, "");
    }

    private
    function updateEntityStatus($entity, string $comment, string $newStatus)
    {
        if ($entity instanceof Package) {
            $entity->bot_comment = $comment;
            if ($newStatus) {
                $entity->status = Package::STATES[$newStatus];
            }
            $entity->save();
            if ($newStatus) {
                if ($newStatus == 'InBaku') {
                    Notification::sendPackage($entity->id, 'PUDO_DELIVERED_STATUS_PACKAGES');
                } elseif ($newStatus == 'Done') {
                    Notification::sendPackage($entity->id, 'Kargomat_Pudo_delivered');
                } else {
                    Notification::sendPackage($entity->id, Package::STATES[$newStatus]);
                }
            }
        } elseif ($entity instanceof Track) {
            $entity->comment_txt = $comment;
            if ($newStatus) {
                $entity->status = Track::STATES[$newStatus];
            }
            $entity->save();
            if ($newStatus) {
                if ($newStatus == 'InBaku') {
                    Notification::sendTrack($entity->id, 'PUDO_DELIVERED_STATUS');
                } elseif ($newStatus == 'Done') {
                    Notification::sendTrack($entity->id, 'Kargomat_Pudo_delivered');
                } else {
                    Notification::sendTrack($entity->id, Track::STATES[$newStatus]);
                }
            }
        }
    }

    private
    function updateArrivedToPudoStatus($entity)
    {
        $comment = "Bağlama(Kargomat) məntəgəyə çatdı.";
        $this->updateEntityStatus($entity, $comment, "InBaku");
        (new PackageService())->updateStatus($entity, 16);
    }

    private
    function updateDeliveredStatus($entity, $order)
    {
        $comment = "Bağlama(Kargomat) müştəriyə təhvil verildi.";

        (new PackageService())->updateStatus($entity, 17);
        $this->updateEntityStatus($entity, $comment, 'Done');

//        if ($entity instanceof Package) {
//            $entity->bot_comment = $comment;
//            $entity->status = Package::STATES['Done'];
//            $entity->save();
//
//            Notification::sendPackage($entity->id, 'Kargomat_Pudo_delivered');
//
//        } elseif ($entity instanceof Track) {
//            $entity->comment_txt = $comment;
//            $entity->status = Track::STATES['Done'];
//            $entity->save();
//
//            Notification::sendTrack($entity->id, 'Kargomat_Pudo_delivered');
//        }

        $undeliveredPackagesCount = KargomatPackage::where('kargomat_order_id', $order->id)
            ->whereIn('status', [KargomatPackage::STATUSES['IN_PROCESS'], KargomatPackage::STATUSES['WAREHOUSE'], KargomatPackage::STATUSES['ARRIVEDTOPOINT']])
            ->count();
        if ($undeliveredPackagesCount == 0) {
            $order->update([
                'status' => KargomatOrder::STATUSES['DELIVERED']
            ]);
        }
    }


}



