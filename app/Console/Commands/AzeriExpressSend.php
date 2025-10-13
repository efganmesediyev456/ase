<?php

namespace App\Console\Commands;

use App\Models\AzeriExpress\AzeriExpressPackage;
use App\Models\Package;
use App\Models\Track;
use Artisan;
use Illuminate\Console\Command;

class AzeriExpress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'azeriexpress {--type=send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AzeriExpress integration';


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
        if ($this->option('type') == 'send') {
            $this->send();
        }

    }

    private function getToken(){
        $login = array(
            "email" => "asebaku@ase.az",
            "password" => "ase@azex!2024",
        );
        $arrLogin = json_encode($login);

        $urlLogin = 'https://api.azeriexpress.com/login';
        $multiCurl = curl_init();
        curl_setopt($multiCurl, CURLOPT_URL, $urlLogin);
        curl_setopt($multiCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($multiCurl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($multiCurl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($multiCurl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($multiCurl, CURLOPT_POSTFIELDS, $arrLogin);
        curl_setopt($multiCurl, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            "lang: az",
            "Content-Type: application/json",
        ));

        $data = curl_exec($multiCurl);

        if ($data === false) {
            $resultLogin = curl_error($multiCurl);
        } else {
            $resultLogin = $data;
        }

        $resultLogin = array_values(json_decode($resultLogin, true));
        return $resultLogin;
    }

    public function send()
    {
        echo "1"; exit;
        $tracks = AzeriExpressPackage::with(['container.azeriExpressOffice','track.customer'])
            ->whereIn('status', [
                AzeriExpressPackage::STATUSES['NOT_SENT'],
                AzeriExpressPackage::STATUSES['HAS_PROBLEM'],
            ])
            ->where('type', 'track')
            //->whereIn('barcode',['ASE6610789637312','ASE7268085537839','ASE8819048248917','ASE3697932617339','ASE0974783936006','ASE7874056812100','ASE7215757049788'])
            ->get();

        $packages = collect();
        $packages = $tracks->merge($packages);
        dd($packages);
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

            $body  = [
                "post_office_id" => $_package->azeriexpress_office->foreign_id,
                "payment_method" => 1,
                "is_paid" => ($package->type == 'package') ? $package->paid : $package->payment_status,
                "customer_number" => $package->type == 'package' ? $customer->customer_id : "ASE" . $customer->id,
                "customer_passport" => $customer->passpot,//
                "customer_fincode" => $customer->fin,
                "customer_name" => $customer->first_name ?: explode(' ', $customer->fullname)[0],
                "customer_surname" => $customer->last_name ?: explode(' ', $customer->fullname)[1],
                "customer_mobile" => $customer->phone != "" ? $customer->phone : $_package->phone,
                "address" => $customer->address,
                "barcode" => $package->barcode,
                "weight" => $_package->weight ?? 0.111,
                "price" => 0.8,
                "package_contents" => $_package->tracking_code,
            ];

            $token = $this->getToken();
            if (!$token[1]['api_token']){
                dd('3rd party token error');
            }
            $postfield = json_encode($body);
            $url = 'https://api.azeriexpress.com/integration/orders';
            $multiCurl = curl_init();
            curl_setopt($multiCurl, CURLOPT_URL, $url);
            curl_setopt($multiCurl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($multiCurl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($multiCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($multiCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($multiCurl, CURLOPT_POSTFIELDS, $postfield);
            curl_setopt($multiCurl, CURLOPT_HTTPHEADER, array(
                'accept: text/plain',
                "lang: az",
                "Content-Type: application/json",
                "Authorization: Bearer " . $token[1]['api_token']
            ));

            $data = curl_exec($multiCurl);
            $httpCode = curl_getinfo($multiCurl, CURLINFO_HTTP_CODE);
            curl_close($multiCurl);
            if ($data === false) {
                $result = curl_error($multiCurl);
            } else {
                $result = $data;
            }
            $response = json_decode($data, true);

            if ($httpCode == 201) {
                AzeriExpressPackage::query()
                    ->where('id', $package->id)
                    ->update([
                        'status' => AzeriExpressPackage::STATUSES['SENT'],
                        'sent_at' => now()
                    ]);

                if ($package->type == 'package') {
                    $_package = Package::find($package->package_id);
                    $_package->bot_comment = "Bağlama AzerExpress-ə göndərildi";
                    $_package->save();

                } else if ($package->type == 'track') {
                    $_track = Track::find($package->package_id);
                    $_track->bot_comment = "Bağlama AzerExpress-ə göndərildi";
                    $_track->save();
                }
                $this->line("success . Tracking Number: " . $package->id);
            } else {
                AzeriExpressPackage::query()
                    ->where('id', $package->id)
                    ->update([
                        'status' => AzeriExpressPackage::STATUSES['HAS_PROBLEM'],
                        'comment' => $package->comment . '| ' . json_encode([$response['data']])
                    ]);
                $this->warn("Order submission failed for Tracking Number: " . $package->id . "--- Error: " . json_encode($result));
            }




    }


}
}


