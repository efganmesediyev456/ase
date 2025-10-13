<?php

namespace App\Console\Commands;

use App\Models\Extra\Notification;
use App\Models\NotificationQueue;
use App\Models\Package;
use App\Models\Track;
use Artisan;
use Illuminate\Console\Command;
use Log;


class NotPaidNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notPaidNotification {--type=send}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Not paid packages notifications';


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
        if ($this->option('type') == 'sendNotPaidNotification') {
            $this->sendNotPaidNotification();
        }

        if ($this->option('type') == 'sendNotPaidPackage') {
            $this->sendNotPaidPackage();
        }
    }


    public function sendNotPaidNotification()
    {
        $packages = Package::where('status',8)->whereNotIn('store_status',[1,5,6])->where('paid_sms_count','<',3)->where('paid',0)->where('created_at', '>=', '2025-01-15')->get();
        foreach ($packages as $package) {

            if (in_array($package->store_status,[1,5,6])){
                continue;
            }

            $text="$package->custom_id nömrəli bağlamanız artıq bizim çeşidləmə mərkəzindədir. Bağlamanı gəl-al məntəqəsinə göndərə bilməyimiz üçün aseshop.az hesabınızdan onlayn şəkildə daşınma xərcini ödəməyiniz xahiş olunur.";
            if (in_array($package->store_status,[3,4,7,8])){
                $content = json_encode([
                    'whatsapp' => $text . 'Ödənişi yalnız online şəkildə etməlisiniz',
                    'sms' => "$package->custom_id nömrəli bağlamanız artıq bizim çeşidləmə mərkəzindədir. Bağlamanı gəl-al məntəqəsinə göndərə bilməyimiz üçün aseshop.az hesabınızdan onlayn şəkildə daşınma xərcini ödəməyiniz xahiş olunur.Ödənişi yalnız online şəkildə etməlisiniz"
                ]);
            }else{
                $content = json_encode([
                    'whatsapp' => $text,
                    'sms' => "$package->custom_id nömrəli bağlamanız artıq bizim çeşidləmə mərkəzindədir. Bağlamanı gəl-al məntəqəsinə göndərə bilməyimiz üçün aseshop.az hesabınızdan onlayn şəkildə daşınma xərcini ödəməyiniz xahiş olunur."
                ]);
            }

            $phone = $package->user->dealer ? $package->user->dealer->phone : $package->user->phone;

            NotificationQueue::create([
                'user_id' => $package->user->id,
                'type' => 'WHATSAPP',
                'send_for' => 'PACKAGE',
                'to' => $phone,
                'sent' => 0,
                'content' => $content,
            ]);

            $package->paid_sms_count += 1;
            $package->save();
        }
        $this->line('Notification sended for not paid packages');
    }


    public function sendNotPaidPackage(){
        $packages = Package::where('status',8)->where('store_status',null)->where('paid_sms_count','<',3)->where('paid',0)->where('created_at', '>=', '2025-01-15')->get();

        foreach ($packages as $package){

            $package->paid_sms_count += 1;
            $package->save();

            Notification::sendPackage($package->id,'package_not_paid');

        }

        $this->line('Notification sended for not paid packages');
    }



}


