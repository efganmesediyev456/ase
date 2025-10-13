<?php

namespace App\Console\Commands;

use App\Models\Extra\Notification;
use App\Models\NotificationQueue;
use App\Models\Track;
use Artisan;
use Illuminate\Console\Command;
use Log;


class NoDeclarationsResend extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nodeclaration {--type=ozon}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend notifications for nodeclaration packages';


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
        if ($this->option('type') == 'ozon') {
            $this->ozon();
        }

        if ($this->option('type') == 'iherb') {
            $this->iherb();
        }

        if ($this->option('type') == 'taobao') {
            $this->taobao();
        }
    }

    public function ozon()
    {
        $tracks = Track::where('partner_id', 3)->whereNotNull('airbox_id')->where('status',5)->where('created_at', '>=', '2024-12-01')->where('declaration_sms_count', '<', 3)->get();
//        $tracks = Track::where('id', 247608)->get();
        foreach ($tracks as $track) {
            Notification::sendTrack($track->id,  5);
//            Notification::sendTrack($track->id, 'OZON_RUS_SMART');
            $track->declaration_sms_count += 1;
            $track->save();

        }
        $this->line('Notification resend for ozon packages');
    }

    public function iherb()
    {
        $tracks = Track::where('partner_id', 1)->where('status',5)->where('created_at', '>=', '2025-01-01')->whereNotNull('airbox_id')->where('declaration_sms_count', '<', 3)->get();
//        $tracks = Track::where('id', 247607)->get();

        foreach ($tracks as $track) {
            Notification::sendTrack($track->id,  1);
//            Notification::sendTrack($track->id, 'IHERB_RUS_SMART');

            $track->declaration_sms_count += 1;
            $track->save();

        }
        $this->line('Notification resend for iherb packages');
    }


    public function taobao()
    {
        $tracks = Track::where('partner_id', 9)->where('status',5)->whereNotNull('airbox_id')->where('created_at', '>=', '2025-01-01')->where('declaration_sms_count', '<', 3)->get();

        foreach ($tracks as $track) {
            Notification::sendTrack($track->id, 5);

            $track->declaration_sms_count += 1;
            $track->save();

        }
        $this->line('Notification resend for taobao packages');
    }

}


