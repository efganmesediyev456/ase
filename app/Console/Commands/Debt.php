<?php

namespace App\Console\Commands;

use App\Models\CD;
use App\Models\DebtLog;
use App\Models\Package;
use App\Models\Setting;
use App\Models\Track;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Debt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debt';

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


        $packages = Package::where('status', 4)
            ->where('paid_debt', 0)
            ->whereNotNull('customs_at')
            ->whereNull('deleted_at')
            ->get();

        $tracks = Track::whereIn('status', [18, 45])
            ->where('paid_debt', 0)
            ->where('partner_id', '!=', 3)
            ->whereNotNull('customs_at')
            ->whereNull('deleted_at')
            ->get();

        $now = Carbon::now();

        foreach ($packages as $package) {
            $customsTime = Carbon::parse($package->customs_at);
            $lastDebtLog = DebtLog::where('custom_id', $package->tracking_code)->latest()->first();
            $dueTime = $lastDebtLog ? Carbon::parse($lastDebtLog->created_at)->addHours(24) : $customsTime->addHours(24);

            if ($dueTime <= $now) {
                $priceToAdd = $lastDebtLog ? Setting::find(1)->debt_price_day : Setting::find(1)->debt_price_first_day;

                $debtLog = new DebtLog();
                $debtLog->custom_id = $package->tracking_code;
                $debtLog->price = $package->debt_price;
                $debtLog->after_price = $package->debt_price + $priceToAdd;
                $debtLog->save();

                $package->debt_price += $priceToAdd;
                $package->save();
            }
        }

        foreach ($tracks as $track) {


            $customsTime = Carbon::parse($track->customs_at);
            $lastDebtLog = DebtLog::where('custom_id', $track->custom_id)->latest()->first();
            $initialDueTime = ($track->partner_id == 3) ? $customsTime->addHours(72) : $customsTime->addHours(24);
            $dueTime = $lastDebtLog ? Carbon::parse($lastDebtLog->created_at)->addHours(24) : $initialDueTime;

            if ($dueTime <= $now) {
                $priceToAdd = $lastDebtLog ? Setting::find(1)->debt_price_day : Setting::find(1)->debt_price_first_day;

                $debtLog = new DebtLog();
                $debtLog->custom_id = $track->custom_id;
                $debtLog->price = $track->debt_price;
                $debtLog->after_price = $track->debt_price + $priceToAdd;
                $debtLog->save();

                $track->debt_price += $priceToAdd;
                $track->save();

                if (in_array($track->partner_id, [1, 9]) && $track->courier_delivery) {
                    CD::removeTrack($track->courier_delivery, $track);
                }

            }
        }

        $this->info('Success');


//        $packages = Package::where('custom_id','ASE3908019478272')->where('status',4)->where('paid_debt',0)->where('customs_at','!=',null)->where('deleted_at',null)->get();
//        $tracks = Track::where('status',18)->where('paid_debt',0)->where('customs_at')->where('deleted_at',null)->get();
//
//        $yesterday = Carbon::yesterday()->toDateString();
//
//        foreach ($packages as $package){
//
//            if(Carbon::parse($package->customs_at)->format('Y-m-d') == $yesterday){
//
//                $firstDayPrice = Setting::find(1)->debt_price_first_day;
//
//                $debtLog = new DebtLog();
//                $debtLog->custom_id = $package->tracking_code;
//                $debtLog->price = $package->debt_price;
//                $debtLog->after_price = $package->debt_price + $firstDayPrice;
//                $debtLog->save();
//
//                $package->debt_price += $firstDayPrice;
//                $package->save();
//            }elseif (Carbon::parse($package->customs_at)->format('Y-m-d') < $yesterday){
//
//                $dayPrice = Setting::find(1)->debt_price_day;
//
//                $debtLog = new DebtLog();
//                $debtLog->custom_id = $package->tracking_code;
//                $debtLog->price = $package->debt_price;
//                $debtLog->after_price = $package->debt_price + $dayPrice;
//                $debtLog->save();
//
//                $package->debt_price += $dayPrice;
//                $package->save();
//
//            }
//
//        }
//
//        foreach ($tracks as $track){
//
//            if(Carbon::parse($track->customs_at)->format('Y-m-d') == $yesterday){
//
//                $firstDayPrice = Setting::find(1)->debt_price_first_day;
//
//                $debtLog = new DebtLog();
//                $debtLog->custom_id = $track->custom_id;
//                $debtLog->price = $track->debt_price;
//                $debtLog->after_price = $track->debt_price + $firstDayPrice;
//                $debtLog->save();
//
//                $track->debt_price += $firstDayPrice;
//                $track->save();
//            }elseif (Carbon::parse($track->customs_at)->format('Y-m-d') < $yesterday){
//
//                $dayPrice = Setting::find(1)->debt_price_day;
//
//                $debtLog = new DebtLog();
//                $debtLog->custom_id = $track->custom_id;
//                $debtLog->price = $track->debt_price;
//                $debtLog->after_price = $track->debt_price + $dayPrice;
//                $debtLog->save();
//
//                $track->debt_price += $dayPrice;
//                $track->save();
//
//            }
//
//        }
//
//        echo 'Success';

    }
}
