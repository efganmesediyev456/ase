<?php

namespace App\Console\Commands;

use App\Models\CustomsModel;
use DB;
use Illuminate\Console\Command;
use App\Models\Track;
use App\Models\PackageCarrier;
use App\Models\Extra\Notification;

class CarriersNoDec extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'carriers:no_dec  {--cwb=} {--listOnly=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send no declartion notifications from tracks';

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
        $cwb = $this->option('cwb');
        $listOnly = $this->option('listOnly');
	$this->no_dec_send_notifications($cwb,$listOnly);
    }

    public function no_dec_send_notifications($cwb, $listOnly)
    {
        $ldate = date('Y-m-d H:i:s');
        $tracks = Track::with(['carrier'])->whereNull('tracks.deleted_at');
        $tracks = $tracks->select('tracks.*', 'package_carriers.created_at as pc_created_at');
        $tracks = $tracks->leftJoin('package_carriers', 'tracks.id', 'package_carriers.track_id');
        if ($cwb) {
            $tracks = $tracks->where('tracks.tracking_code', $cwb);
        } else {
            $tracks = $tracks->where('tracks.partner_id', 8);
            $tracks = $tracks->where('tracks.status', 18);
            //$tracks = $tracks->where('tracks.status',5);
            $tracks = $tracks->whereNull('package_carriers.deleted_at');
            $tracks = $tracks->whereRaw('(package_carriers.status is NULL or package_carriers.status=0)');
            $tracks = $tracks->whereNull('package_carriers.depesH_NUMBER');
            $tracks = $tracks->whereRaw("(TIME_TO_SEC(TIMEDIFF('" . $ldate . "',package_carriers.created_at))>=20*3600)");
            $tracks = $tracks->whereRaw("(tracks.no_dec_notification_at is null or TIME_TO_SEC(TIMEDIFF('" . $ldate . "',tracks.no_dec_notification_at))>=20*3600)");
        }
        $tracks = $tracks->get();
        echo " " . count($tracks) . " notifications to send " . ($listOnly ? "List Only " : "") . "\n";
        $num = 0;
        foreach ($tracks as $track) {
            $num++;
            $track->no_dec_notification_at = $ldate;
            $track->no_dec_notification_count++;
            $track->bot_comment = "Sent no declaration notification " . $track->no_dec_notification_count;
            if (!$listOnly) {
                $track->save();
                Notification::sendTrack($track->id, 'no_dec');
                //Notification::sendTrack($track->id, 5);
            }
            echo "$num " . $track->tracking_code . "  " . ($track->carrier ? $track->carrier->created_at : 'no carrier') . "  " . $track->no_dec_notification_at . " (" . $track->no_dec_notification_count . ")" . "\n";
        }
    }
}
