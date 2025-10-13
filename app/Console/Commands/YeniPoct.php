<?php

namespace App\Console\Commands;

use App\Models\Package;
use App\Models\Track;
use App\Models\YeniPoct\YenipoctOffice;
use App\Models\YeniPoct\YenipoctPackage;
use Artisan;
use Illuminate\Console\Command;
use Log;


class YeniPoct extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'yenipoct {--type=send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Yenipoct integration';


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
        if ($this->option('type') == 'getBranches') {
            $this->getBranches();
        }
    }


    public function send()
    {
        $packages = YenipoctPackage::with(['container.yenipoctOffice', 'track.customer'])
            ->whereIn('status', [
                YenipoctPackage::STATUSES['NOT_SENT'],
                YenipoctPackage::STATUSES['HAS_PROBLEM'],
            ])
            ->where('company_sent', 1)
            ->get();
//        $packages = YenipoctPackage::where('id',447)
//            ->get();
//        dd($packages);
//        $tracks = YenipoctPackage::with(['container.yenipoctOffice', 'track.customer'])
//           ->where('id',2)
//            ->get();
//        $packages = collect();
//        $packages = $tracks->merge($packages);
//        if ($packages->count() < 1) {
//            dd('boshdur');
//        }
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
            $operator = '';
            $number = '';
            $phone = str_replace('+','',$phone);
            if(preg_match('/9940/', $phone))
            {
                $operator = substr($phone,4,2);
                $number = substr($phone, 6);
            } elseif(preg_match('/994/', $phone) && !preg_match('/9940/', $phone)) {
                $operator = substr($phone,3,2);
                $number = substr($phone, 5);
            }else{
                $operator = substr($phone,0,3);
                if (!empty($operator) && $operator[0] === '0') {
                    $operator = substr($operator, 1);
                }
                $number = substr($phone, 3);
            }
            $postfields = [
                "pass" => 'l$k9z+HQ32~S',
                "weight" => $_package->weight ?? 0.111,
                "quantity" => 1,
                "cost" => 1,
                "currency" => 1,
                "to_branch" => $_package->yenipoct_office->foreign_id,
                "cargo_order_code" => $package->barcode,
                "rec_fullname" => substr(
                    $customer->name ? $customer->name . ' ' . $customer->surname : 'FullName not found',
                    0,
                    15
                ),                'cargo_paid' => $package->payment_status,
                "rec_id_series" => $customer->passpot ?  $customer->passpot : 'AA0000000',
                "rec_id_number" => $customer->fin ? $customer->fin : 1234567,
                "rec_phone_operator" => $operator,
                "rec_phone" => $number,
                "customer_code" => $package->type == 'package' ? $customer->customer_id : "ASE" . $customer->id
            ];

            $arr = json_encode($postfields);
            $this->info('Yp request  - ' . $arr);
            $url = 'https://yenipoct.az/api/createNewOrder';
            $multiCurl = curl_init();
            curl_setopt($multiCurl, CURLOPT_URL, $url);
            curl_setopt($multiCurl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($multiCurl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($multiCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($multiCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($multiCurl, CURLOPT_POSTFIELDS, $arr);
            curl_setopt($multiCurl, CURLOPT_HTTPHEADER, array(
                'accept: text/plain',
                "lang: az",
                "Content-Type: application/json",
                "Authorization: Bearer 3|JdRA17Kvz4DrTnZHhVIDrAq86vclDmqIY7VwynlV"
            ));

            $data = curl_exec($multiCurl);

            if ($data === false) {
                $result = curl_error($multiCurl);
            } else {
                $result = $data;
            }
            $response = json_decode($data, true);

            if (isset($response["status"]) and $response["status"]) {

                if ($response["status"] == 'ok') {
                    YenipoctPackage::query()
                        ->where('id', $package->id)
                        ->update([
                            'status' => YenipoctPackage::STATUSES['SENT'],
                            'sent_at' => now()
                        ]);

                    if ($package->type == 'package') {
                        $_package = Package::find($package->package_id);
                        $_package->bot_comment = "Bağlama YeniPoct-a göndərildi";
                        $_package->save();

                    } else if ($package->type == 'track') {
                        $_track = Track::find($package->package_id);
                        $_track->bot_comment = "Bağlama YeniPoct-a göndərildi";
                        $_track->save();
                    }
                    $this->line("success . Tracking Number: " . $package->barcode);
                } else {
                    YenipoctPackage::query()
                        ->where('id', $package->id)
                        ->update([
                            'status' => YenipoctPackage::STATUSES['HAS_PROBLEM'],
                            'comment' => $package->comment . '| ' . json_encode([$response["status"]])
                        ]);
                    $this->warn("Order submission failed for Tracking Number: " . $package->barcode . "--- Error: " . json_encode($result));
                }
            }

        }
    }

    public function getBranches() {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://yenipoct.az/api/allBranches',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $branchesData = json_decode($response, true);
        dd($branchesData);
        if ($branchesData) {
            foreach ($branchesData as $city) {
                foreach ($city['branches'] as $branch) {
//                    YenipoctOffice::query()->updateOrCreate([
//                        'foreign_id' => $branch['id'],
//                    ], [
//                        'foreign_id' => $branch['id'],
//                        'name' => $branch['name'],
//                        'description' => $branch['name'],
//                        'address' => $branch['address'],
//                        'contact_phone' => $branch['phone1'],
//                    ]);

                    $data= YenipoctOffice::where('foreign_id',$branch['id'])->whereNull('latitude')->first();

                    if (!$data){
                       $new_office =  YenipoctOffice::query()->create([
                            'foreign_id' => $branch['id'],
                            'name' => $branch['name'],
                            'description' => $branch['name'],
                            'address' => $branch['address'] . ' ' .$branch['address_detail'],
                            'contact_phone' => $branch['phone1'],
                            'latitude' => $branch['latitude'],
                            'longitude' => $branch['longitude'],
                        ]);
                        preg_match('/(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})/', $branch['work_hours'], $workMatches);
                        preg_match('/Nahar fasiləsi\s*(\d{2}:\d{2})\s*-\s*(\d{2}:\d{2})/', $branch['work_hours'], $lunchMatches);

                        $work_start = $workMatches[1] ?? null;
                        $work_end = $workMatches[2] ?? null;
                        $lunch_start = $lunchMatches[1] ?? null;
                        $lunch_end = $lunchMatches[2] ?? null;
//                    dd($work_start,$work_end,$lunch_start,$lunch_end);
//                    echo "Branch: {$branch['name']}, Work Hours: $work_start - $work_end, Lunch: $lunch_start - $lunch_end\n";
//                   $data= YenipoctOffice::where('foreign_id',$branch['id'])->first();
                        if(!$new_office) {
                            echo "Branch: {$branch['name']} yoxdur";
                            continue;
                        }

                        if ($work_start && $work_end ) {
//                         echo "Branch: {$branch['name']}, Work Hours: $work_start - $work_end, Lunch: $lunch_start - $lunch_end\n";
                            if($lunch_start && $lunch_end && $new_office ){
                                $new_office->lunch_break_opening_time = $lunch_start;
                                $new_office->lunch_break_closing_time = $lunch_end;
                            }
                            $new_office->monday_opening_time = $work_start;
                            $new_office->monday_closing_time = $work_end;
                            $new_office->tuesday_opening_time = $work_start;
                            $new_office->tuesday_closing_time = $work_end;
                            $new_office->wednesday_opening_time = $work_start;
                            $new_office->wednesday_closing_time = $work_end;
                            $new_office->thursday_opening_time = $work_start;
                            $new_office->thursday_closing_time = $work_end;
                            $new_office->friday_opening_time = $work_start;
                            $new_office->friday_closing_time = $work_end;
                            $new_office->saturday_opening_time = $work_start;
                            $new_office->saturday_closing_time = $work_end;
                            $new_office->save();
                        }
                    }
                }
            }
            $this->line("Branches inserted successfully!");
        } else {
            $this->warning("Failed to decode JSON response.");
        }
    }

}


