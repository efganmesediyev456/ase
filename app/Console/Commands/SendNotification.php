<?php

namespace App\Console\Commands;

use App\Models\Extra\Notification;
use App\Models\NotificationQueue;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;

class SendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:send {--type=}';

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
        $hour = (int)Carbon::now()->format('H');

        $type = $this->option('type');
        //dd($type);
        if (9 <= $hour && $hour <= 21) { // && ($type == 'SMS' || $type == "WHATSAPP")) || $type == 'EMAIL' || $type == 'MOBILE') {
            $count = 100;//$type == 'SMS' ? 40 : 24;
            //$queues = NotificationQueue::where('sent', 0)->where('type', $type)->orderBy('id', 'asc')->take($count)->get();
//            $queues = NotificationQueue::where('sent', 0)->where('type', $type)->orderBy('id', 'asc')->get(); evvelki

            $queues = NotificationQueue::where('sent', 0)
                ->where('type', $type)
                ->where(function($query) {
                    $query->whereNull('scheduled_at')
                        ->orWhere('scheduled_at', '<=', Carbon::now());
                })
                ->orderBy('id', 'asc')
                ->get();


//            $queues = NotificationQueue::where('id', 706715)->where('type', $type)->orderBy('id', 'asc')->get();
            $num = 0;
            foreach ($queues as $queue) {
                $num++;
                $this->line($num . '  ' . $queue->to . " => " . $queue->subject);
                try {
                  Notification::sendBothForQueue($queue);
                    $queue->sent = 1;
                    $queue->save();
                    $this->line('success' . $queue->to);
                } catch (Exception $exception) {
                    $message = null;
                    $message .= "🆘 <b>Error by sending notification</b> " . $queue->to;
                    $message .= chr(10) . $exception->getMessage();
                    $queue->error_message = $exception->getMessage();

                    if ($type == "WHATSAPP") {
                        $content = json_decode($queue->content);
                        $content = $content->sms;
                        $queue->type = 'SMS';
                        $queue->content = $content;
                        $queue->sent = 0;
                        // this message should not send with SMS notification
                        if (strpos($content, 'çatdırılma ünvanınızın konumunu') !== false) {
                            $queue->sent = 1;
                        }

                    }else{
                        $queue->sent = 2;
                    }
                    $queue->save();
//                    sendTGMessage($message);
                }

                if (($num % $count) == 0)
                    sleep(5);
            }

            if (count($queues)) {
                /* Send notification */
                $message = null;
                $message .= "🆘 <b>Error by sending notification</b>";
                //sendTGMessage($message);
            }
        } else {
            $this->error("Out of time");
        }
    }
}
