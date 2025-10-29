<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;

class Currency extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'currency';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'update currency rates';

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

        $ldate = date('Y-m-d H:i:s');
        echo $ldate;
        DB::delete("delete from currency_rate where TIMESTAMPDIFF(MONTH, created_at, '" . $ldate . "')>3");

        $url = "http://apilayer.net/api/live?access_key=" . env('APILAYER') . "&currencies=TRY,AZN,GBP,RUB,EUR,AED,CNY,KZT,USD,KRW&source=USD&format=1";
        //$url = "http://api.currencylayer.com/live?access_key=d2674a1e7f00bc353c8229fe6430a721&&currencies=TRY,AZN,GBP,RUB,EUR,AED,CNY,USD&source=USD&format=1";
        $currency = 'TRY';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $server_output = curl_exec($ch);
        $content = json_decode($server_output, true);
        $try_count = 3;
        while ($try_count > 0 && !(isset($content['success']) && $content['success'] && isset($content['quotes']) && isset($content['quotes']['USD' . $currency]) && $content['quotes']['USD' . $currency] != 0)) {
            sleep(1);
            $server_output = curl_exec($ch);
            $content = json_decode($server_output, true);
            $try_count--;
            echo $try_count . " error: " . $server_output . "\n";
        }
        curl_close($ch);

        if (isset($content['success']) && $content['success'] && isset($content['quotes']) && isset($content['quotes']['USD' . $currency]) && $content['quotes']['USD' . $currency] != 0) {
            echo " ok:";
            foreach ($content['quotes'] as $code => $rate) {
                $rate = round($rate, 5);
                if ($code == 'USDAZN' && $rate > 1.71)
                    $rate = 1.71;
                DB::insert("insert into currency_rate(code,rate,created_at) values(?,?,?)", [$code, $rate, $ldate]);
                echo " " . $code . " " . $rate;
            }
        } else {
            echo " error: " . $server_output . "\n";
        }
        echo "\n";
    }
}
