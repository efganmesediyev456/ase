<?php

namespace App\Console\Commands;

use App\Models\Package;
use App\Models\Transaction;
use Illuminate\Console\Command;

class Payment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment';

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
        $transactions = Transaction::whereType('PENDING')->where('paid_by', 'PORTMANAT')->latest()->get();

        foreach ($transactions as $transaction) {
            $data = \GuzzleHttp\json_decode($transaction->extra_data, true);
            $rrn = $data['body']['psp_rrn'];
            $package = Package::find($transaction->custom_id);

            if ($package && !$package->paid) {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://psp.mps.az/check");
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['psp_rrn' => $rrn]));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $server_output = curl_exec($ch);
                curl_close($ch);
                $obj = json_decode($server_output, true);

                if ($obj['code'] == '0') {
                    // Success
                    $transaction->type = 'OUT';
                    $transaction->extra_data = json_encode($obj, true);
                    $transaction->save();

                    /* make paid */
                    $package->paid = true;
                    $package->save();

                    /* Send notification */
                    $message = null;
                    $message .= "💳 <b>" . $package->user->full_name . "</b> (" . $package->user->customer_id . ") ";
                    $message .= "#Pending Portmanat ilə <a href='https://admin." . env('DOMAIN_NAME') . "/packages/" . $package->id . "/edit'>" . $package->tracking_code . "</a> tracking kodu olan bağlaması üçün <b>" . $package->delivery_manat_price_discount . " AZN</b> ödəniş etdi.";

                    sendTGMessage($message);
                } else {
                    $transaction->type = 'ERROR';
                    $transaction->extra_data = json_encode($obj, true);
                    $transaction->save();
                }
            }
        }
    }

}
