<?php

namespace App\Console\Commands;

use App\Models\Extra\Notification;
use App\Models\Package;
use App\Models\Track;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Filial;

class DebtNotification extends Command
{
    protected $signature = 'debt:notification';
    protected $description = 'description';

    public function handle()
    {
        //debt_sms_count

        $tracks = Track::whereNotIn('status', [18, 45])->where('paid_debt',0)->where('debt_price','>',0)->whereNotNull('customs_at')->where('deleted_at',null)->get();
        $packages = Package::where('status','!=',4)->where('paid_debt',0)->where('debt_price','>',0)->whereNotNull('customs_at')->where('deleted_at',null)->get();

        foreach ($tracks as $track){
            //evvel 3 idi
            if($track->debt_sms_count != 1 && $track->debt_sms_count < 1){

                //Notification send
                Notification::sendTrack($track->id, 'customs_storage_fee');

                $track->debt_sms_count += 1;
                $track->save();
            }
        }

        //$this->info('success');

        foreach ($packages as $package){
            if($package->debt_sms_count != 1 && $package->debt_sms_count < 1){

                //Notification send
                Notification::sendPackage($package->id, 'customs_storage_fee');

                $package->debt_sms_count += 1;
                $package->save();
            }
        }

        $this->info('success');
    }
}
