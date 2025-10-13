<?php

namespace App\Console\Commands;

use App\Models\Extra\Logic;
use App\Models\Extra\SMS;
use App\Models\Package;
use App\Models\Parcel;
use App\Models\Track;
use Carbon\Carbon;
use Illuminate\Console\Command;

class AseLogic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logic {--type=insert} {--cwb=} {--parcel_id=} {--package_cwb=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return mixed
     */
    public function handle()
    {
        $this->info(date('Y-m-d H:i:s') . "  Begin");
        $time_start = microtime(true);

        if ($this->option('type') == 'insert') {
            $this->insert();
        } elseif ($this->option('type') == 'test') {
            $this->test();
        } elseif ($this->option('type') == 'again') {
            $this->again();
        } elseif ($this->option('type') == 'sync') {
            $this->sync();
        }
        elseif ($this->option('type') == 'closeTracks'){
            $this->closeTracks();
        }
        else {
            $this->close();
        }

        $time_end = microtime(true);

        $time = ceil(($time_end - $time_start));
        $this->error($time . ' secs');
        $this->info(date('Y-m-d H:i:s') . "  End");
    }

    public function again()
    {

        /* $parcels = Parcel::with(['packages'])->where('warehouse_id', env('LOGIC_ID'))->where('inserted', 2)->where('sent', 0)->get();

         foreach ($parcels as $parcel) {
             $this->line($parcel->custom_id . " ----> ");
             foreach ($parcel->packages as $package) {
                 if ($package->status < 1) {
                     $package->status = 1;
                     $package->save();

                     Notification::sendPackage($package->id, '1');
                 }
             }

             $parcel->sent = true;
             $parcel->save();
         }

         die;*/

        if ($this->option('cwb')) {
            $parcel = Parcel::whereCustomId($this->option('cwb'))->first();
            $packages = $parcel->packages;
        } else if ($this->option('parcel_id')) {
            $parcel = Parcel::whereId($this->option('parcel_id'))->first();
            $packages = $parcel->packages;
        } else if ($this->option('package_cwb')) {
            $packages = Package::whereCustomId($this->option('package_cwb'))->get();
            //echo "again package_cwb ".$this->option('package_cwb')." ".count($packages)."\n";
        } else {
            $packages = Package::whereNotNull('logic_comment')->whereNull('scanned_at')->get();
        }

        $this->line("Count : " . $packages->count());

        foreach ($packages as $package) {
            $data = [
                'parcels' => [],
            ];
            $data['parcels'][] = $this->getData($package);

            $inserted = Logic::insertParcel($data);

            if ($inserted['status'] == 200) {
                $package->logic_status = 1;
                $this->info("Inserted " . $package->custom_id);
                $package->save();
            } else {
                if ($inserted['cwb']) {
                    $this->warn("Removed " . $inserted['cwb']);

                    $this->error("Error " . $inserted['error']);

                    if ($package) {
                        $package->logic_status = 2;
                        $package->logic_comment = $inserted['error'];
                        $package->save();
                    }
                } else {
                    if ($inserted['error']) {
                        $this->error('Cannot find cwb : Package :: ' . $package->custom_id);
                        $this->error("Error " . $inserted['error']);
                        //        Logic::errorTgMessage('Cannot find cwb : Parcel :: ' . $parcel->custom_id. " Error : " . $inserted['error']);
                    } else {
                        $this->error('Cannot find cwb : Package :: ' . $package->custom_id);
                        $this->error("Error " . $inserted['error']);
                        //        Logic::errorTgMessage('Cannot find cwb : Parcel :: ' . $parcel->custom_id. " Error : " . $inserted['error']);
                    }
                }
            }
        }
    }

    public function getData(Package $package)
    {
        $round = 3;

        return [
            'sendercompany' => $package->website_name ? getOnlyDomain($package->website_name) : 'Trendyol',
            'senderperson' => $package->website_name ? getOnlyDomain($package->website_name) : 'Trendyol',
            'sendercountry' => 'tr',
            'sendercity' => 'ist',
            'senderadress' => $package->fake_address ?: 'Çobançeşme Mah. Kalender Sk. No:8 Bahçelievler',
            'senderpostcode' => '34306',
            'senderphone' => '.',
            'senderemail' => '.',//'info@ase.com.tr',
            'ParcelCode' => null,
            'B2CParcelCode' => $package->custom_id,
            'OrderNumber' => NULL,
            'PackageCount' => 1,
            'InvoiceAmount' => round(round($package->shipping_converted_price / $package->number_items_goods, $round) * $package->number_items_goods, $round),
            'InvoiceValueExtra1' => null,
            'InvoiceValueExtra2' => NULL,
            'InvoiceCurrency' => 'USD',
            'ShippingFee' => 0,
            'CustomsFee' => 0,
            'DiscountAmount' => 0,
            'InsuranceFee' => 0,
            'CODValue' => NULL,
            'CODCurrencyCode' => NULL,
            'LabelType' => NULL,
            'InvoiceNumber' => $package->tracking_code,
            'InvoiceDate' => $package->created_at->toDateTimeString(),
            'MasterType' => 2,
            'IncoTerms' => NULL,
            'InvoiceType' => NULL,
            'ShippingCalculatorTransactionId' => NULL,
            'DutyVATCalculatorTransactionId' => NULL,
            'DutyVATTerms' => false,
            'DefermentFee' => 0,
            'Consignee' => [
                'FirstName' => NULL,
                'MiddleName' => NULL,
                'LastName' => NULL,
                'FullName' => $package->user ? $package->user->full_name : '-',
                'Email' => $package->user ? $package->user->email : "-",
                'Email2' => NULL,
                'PhoneNumber' => NULL,
                'MobilePhoneNumber' => $package->user ? SMS::clearNumber($package->user->phone, true, " ") : null,
                'TaxOffice' => NULL,
                'TaxNumber' => NULL,
                'Address' => [
                    'Title' => $package->user ? str_replace("ASE", "", $package->user->customer_id) : '-',
                    'AddressLine1' => $package->user ? $package->user->address : 'Üzeyir Hacıbəyov, 61b',
                    'AddressLine2' => NULL,
                    'ZipCode' => ($package->user && $package->user->fin) ? $package->user->fin : (($package->user && $package->user->zip_code) ? $package->user->zip_code : '1000'),
                    'Country' => 'Azerbaijan',
                    'CountryISO' => 'AZ',
                    'City' => 'BAKU',
                    'State' => NULL,
                    'District' => NULL,
                    'Region' => NULL,
                    'Street' => NULL,
                    'PudoCode' => $package->user ? $package->user->fin : '-',
                ],
                'ConsigneeDocument' => NULL,
            ],
            'Packages' => [
                [
                    'ProductCount' => 1,//$package->number_items_goods,
                    'Height' => NULL,
                    'Width' => NULL,
                    'Length' => NULL,
                    'GrossWeight' => round(round($package->weight_goods / $package->number_items_goods, $round) * $package->number_items_goods, $round),
                    'VolumetricWeight' => NULL,
                    'ChargeableWeight' => NULL,
                    'Products' => [
                        [
                            'Barcode' => null,
                            'Sku' => md5($package->custom_id),
                            //'Description'         => str_limit($package->detailed_type_one,50),
                            'Description' => str_limit($package->detailed_type_first, 50),
                            'Content' => NULL,
                            'Brand' => NULL,
                            'MainMaterial' => NULL,
                            'CareInstruction' => NULL,
                            'Type' => str_limit($package->detailed_type_one, 96),
                            'Quantity' => $package->number_items_goods,
                            'UnitPrice' => round($package->shipping_converted_price / $package->number_items_goods, $round),
                            'HsCode' => NULL,
                            'CountryOfOriginCode' => 'TR',
                            'UnitWeight' => round($package->weight_goods / $package->number_items_goods, $round),
                            'VolumetricWeight' => NULL,
                            'ChargeableWeight' => NULL,
                            'Length' => NULL,
                            'Width' => NULL,
                            'Height' => NULL,
                            'PhotoUrl' => NULL,
                            'WebUrl' => $package->website_name,
                            'Gender' => NULL,
                            'SalesType' => 1,
                            'Returnable' => NULL,
                        ],
                    ],
                ],
            ],
            'Services' => [
                0 => [
                    'ServiceId' => 3,
                ],
                1 => [
                    'ServiceId' => 6,
                ],
            ],
        ];
    }

    public function sync()
    {
        $parcel_id = 0;
        $res = Logic::SyncParcel('12131324');
        //$res=Logic::SyncParcel('23548301573');
        //$res=Logic::SyncParcel('2203292607');
        if (!isset($res['status']) || $res['status'] != 200) {
            $this->error("Parcel sync error");
            if (isset($res['error']))
                $this->error("Error:" . $res['error']);
            if (isset($res['cwb']))
                $this->error("CWB:" . $res['cwb']);
            return;
        }
        if (!isset($res['response'])) {
            $this->error("Parcel sync no response");
            return;
        }
        $res = $res['response'];
        if (!isset($res['result'])) {
            $this->error("Parcel sync no result");
            return;
        }
        //$res=$res['result'];
        dd($res);
        $res = json_decode($res['result'], true);
        if (!isset($res['ShipmentsShipmentDetail'])) {
            $this->error("Parcel sync no ShipmentsShipmentDetail");
            return;
        }
        foreach ($res['bags'] as $bag) {
            echo $bag['code'] . "\n";
        }
        foreach ($res['ShipmentsShipmentDetail'] as $ship) {
            echo $ship['cwb_kod'] . ' ' . $ship['bag_code'] . "\n";
        }
    }

    public function test()
    {
        $cwb = $this->option('cwb');
        //$parcel = Parcel::withCount('packages')->with(['packages'])->where('warehouse_id', env('LOGIC_ID'))->where('custom_id', $cwb)->first();
        $parcel = Parcel::withCount('packages')->with(['packages'])->where('custom_id', $cwb)->first();
        if (!$parcel) {
            $this->error("No any parcel");
        }
        $this->info("Parcel packages_count: " . $parcel->packages_count);
        $this->info("Parcel count(packages): " . count($parcel->packages));
        $pn = 0;
        foreach ($parcel->packages as $package) {
            $pn++;
            $this->info("   " . $pn . " " . $package->custom_id);
        }
        /*$package = Package::whereCustomId($cwb)->first();

        $data = [
            'parcels' => [],
        ];
        $data['parcels'][] = $this->getData($package);

        //dd(\GuzzleHttp\json_encode($data));

        $inserted = Logic::insertParcel($data);

        dd($data, $inserted);
	 */
    }

    public function insert()
    {
        $this->line("Stared AseLogic");
        while (true) {

            $parcel = Parcel::withCount('packages')->with(['packages'])->where('warehouse_id', env('LOGIC_ID'))->where('inserted', 1)->first();

            if (!$parcel) {
                $this->error("No any parcel");

                break;
            }

            $this->warn($parcel->custom_id . " is under control ...");
            $data = [
                'parcels' => [],
            ];

            $totalPackage = 0;
            $alreadyExists = 0;
            $this->warn("Number of packages : " . $parcel->packages->count());
            foreach ($parcel->packages as $package) {
                if ($package->logic_status != 2) {
                    $this->info("Added " . $package->custom_id);
                    $totalPackage++;
                    $data['parcels'][] = $this->getData($package);
                } elseif ($package->logic_status == 2) {
                    $alreadyExists++;
                }
            }

            if (!$parcel->packages->count() || !$totalPackage || ($parcel->packages->count() && ($alreadyExists / $parcel->packages->count()) > 0.2)) {
                $this->error("No any packages");
                $parcel->inserted = 2;
                $parcel->save();

                break;
            }

            $this->line("");
            $this->info("Insert was started...");
            $inserted = Logic::insertParcel($data);

            if (is_array($inserted)) {
                if (isset($inserted['status']) && $inserted['status'] == 200) {
                    $this->info("Parcel inserted : " . $totalPackage . " packages");
                    $parcel->inserted = 2;
                    $parcel->save();

                    foreach ($parcel->packages as $package) {
                        if (!$package->logic_status) {
                            $package->logic_status = 1;
                            $package->save();
                        }
                    }

                    $message = "✅✅✅ #AseLogic -de " . $parcel->custom_id . " id-li parcel üçün " . $totalPackage . " bağlama sisteme elave edildi.";
                    sendTGMessage($message);

                    break;
                } else {
                    if ($inserted['cwb']) {
                        $package = Package::whereCustomId(trim($inserted['cwb']))->first();

                        if ($package) {
                            $package->logic_status = 2;
                            $package->logic_comment = $inserted['error'];
                            $package->save();

                            $this->warn("Removed :" . $inserted['cwb']);
                            $this->error("Error " . $inserted['error']);

                            Logic::errorTgMessage('Error : Parcel :: ' . $parcel->custom_id . " Error : " . $inserted['error']);
                        }
                    } else {
                        if ($inserted['error']) {
                            $this->error('Cannot find cwb : Parcel :: ' . $parcel->id);
                            $this->error("Error " . $inserted['error']);
                            Logic::errorTgMessage('Cannot find cwb : Parcel :: ' . $parcel->custom_id . " Error : " . $inserted['error']);
                        } else {
                            $this->error('Cannot find cwb : Parcel :: ' . $parcel->id);
                            $this->error("Error " . $inserted['error']);
                            Logic::errorTgMessage('Cannot find cwb : Parcel :: ' . $parcel->custom_id . " Error : " . $inserted['error']);
                        }
                        if (strpos($inserted['error'], 'Authorization has been denied for this request') !== false) {
                            $this->error("   Clear cache!");
                            Artisan::call('cache:clear');
                        }
                        break;
                    }
                }
            } else {
                dump($inserted);
            }
        }
    }

    public function close()
    {
        $this->line("Closing..");

        $packages = Package::where('warehouse_id', env('LOGIC_ID'))->whereNotNull('scanned_at')->where('logic_status', '=', 1)->where('scanned_at', '>=', Carbon::now()->subDays(3))->orderBy('scanned_at', 'desc')->take(1000)->get();
        $closeCount = 0;
        $alreadyClosed = 0;

        foreach ($packages as $key => $package) {
            $this->info("closing: " . $key . ") " . $package->custom_id . " " . $package->logic_status);
            $closed = Logic::closePackage($package->custom_id);

            if (isset($closed['IsSuccess']) && $closed['IsSuccess']) {
                $this->info($key . ") " . $package->custom_id . " was closed!");
                $package->logic_status = 3;
                $closeCount++;
                $alreadyClosed = 0;
                $package->save();
            } else {
                if (isset($closed['Message'])) {
                    $this->error($key . ") " . $package->custom_id . " :: " . $closed['Message']);
                    $alreadyClosed++;
                } else {
                    $this->error($key . ") " . $package->custom_id . " :: Error with no message");
                }
            }

            if ($alreadyClosed > 100) {
                break;
            }
        }

        if ($closeCount) {
            $message = "🔴🔴🔴 #AseLogic -den " . $closeCount . " ədəd bağlama bağlanıldı. ";
            sendTGMessage($message);
        }
    }
    public function closeTracks()
    {
        $this->line("Closing..");

        $tracks = Track::whereNotNull('scanned_at')->whereNotNull('second_tracking_code')->where('logic_status', '=', 0)->where('partner_id',1)->where('status',17)->take(1000)->get();
        $closeCount = 0;
        $alreadyClosed = 0;

        foreach ($tracks as $key => $track) {
            $this->info("closing: " . $key . ") " . $track->second_tracking_code . " " . $track->logic_status);
            $closed = Logic::closePackage($track->second_tracking_code);

            if (isset($closed['IsSuccess']) && $closed['IsSuccess']) {
                $this->info($key . ") " . $track->second_tracking_code . " was closed!");
                $track->logic_status = 3;
                $closeCount++;
                $alreadyClosed = 0;
                $track->save();
            } else {
                if (isset($closed['Message'])) {
                    $track->logic_status = 5; // gonderilmeyib
                    $track->save();
                    $this->error($key . ") " . $track->second_tracking_code . " :: " . $closed['Message']);
                    $alreadyClosed++;
                } else {
                    $this->error($key . ") " . $track->second_tracking_code . " :: Error with no message");
                }
            }

//            if ($alreadyClosed > 100) {
//                break;
//            }
        }

        if ($closeCount) {
            $message = "🔴🔴🔴 #AseLogic -den " . $closeCount . " ədəd bağlama bağlanıldı. ";
            sendTGMessage($message);
        }
    }
}
