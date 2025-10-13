<?php

namespace App\Console\Commands;

use App\Models\Extra\SMS;
use App\Models\NotificationQueue;
use App\Models\Parcel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Campaign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaign:check';

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
        $campaigns = \App\Models\Campaign::where('sent', 0)->latest()->get();

        foreach ($campaigns as $campaign) {
            // 4:42
            // 5:42
            // 4:42 - 1 >= 4:42 + 1

            if ($campaign->created_at <= Carbon::now()->subHours($campaign->send_after)) {
                $this->info($campaign->title);
                $id = explode(",", $campaign->users);
                $users = User::whereIn('id', $id)->get();
                foreach ($users as $user) {
                    $content = $campaign->content;
                    $content = str_replace("{name}", $user->full_name, $content);
                    $content = str_replace("{code}", $user->customer_id, $content);
                    $content = str_replace("{email}", $user->email, $content);

                    if ($user->cleared_phone) {
                        $content = str_replace("{phone}", "+" . $user->cleared_phone, $content);
                    } else {
                        $content = str_replace("{phone}", "", $content);
                    }
                    if ($user->passport) {
                        $content = str_replace("{passport}", $user->passport, $content);
                    } else {
                        $content = str_replace("{passport}", "", $content);
                    }

                    if ($user->city_name) {
                        $content = str_replace("{city}", $user->city_name, $content);
                    } else {
                        $content = str_replace("{city}", "", $content);
                    }

                    $to = NULL;
                    if ($campaign->type == 'SMS')
                        $to = SMS::clearNumber($user->phone);
                    if ($campaign->type == 'EMAIL')
                        $to = $user->email;
                    if ($campaign->type == 'MOBILE')
                        $to = $user->id;
                    $type = $campaign->type == 'SMS' ?: 'EMAIL';
                    if ($campaign->type == 'MOBILE')
                        $type = 'MOBILE';


                    if ($to) {
                        NotificationQueue::create([
                            'to' => $to,
                            'subject' => $campaign->title,
                            'content' => $content,
                            'type' => $type,
                            'send_for_id' => $campaign->id,
                            'send_for' => 'CAMPAIGN',
                        ]);
                    }
                }

                $campaign->sent = 1;
                $campaign->save();
            }
        }
    }

    public function updatePackagesDelivery()
    {
        $parcels = Parcel::with('packages')->where('sent', 2)->latest()->get();

        foreach ($parcels as $parcel) {
            $delivered = Carbon::now();
            $count = 0;

            foreach ($parcel->packages as $package) {
                if ($package->scanned_at < $delivered && $package->scanned_at) {
                    $delivered = $package->scanned_at;
                    $count++;
                }

            }


            $showDate = ($delivered ? $delivered->toDateTimeString() : 'Not delivered') . " :: " . $count;

            $this->info($parcel->custom_id . "  ::  " . $showDate);


            if ($count) {
                foreach ($parcel->packages as $package) {
                    $this->line(" --- " . $package->custom_id . " :: from " . $package->scanned_at . " to " . $delivered);
                    $package->scanned_at = $delivered;
                    $package->save();
                }
            }
            $this->line("");
            $this->line("");
        }


        die;
    }
}
