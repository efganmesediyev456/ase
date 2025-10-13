<?php

namespace App\Console\Commands;

use App\Models\CD;
use App\Models\Customer;
use App\Models\Extra\Whatsapp;
use App\Models\NotificationQueue;
use App\Models\User;
use Illuminate\Console\Command;
use App\Models\Filial;
use Carbon\Carbon;
use App\Models\Extra\SMS;
use App\Models\Track;
use App\Models\CA;
use App\Models\Package;
use App\Models\CustomsModel;
use App\Services\Package\PackageService;
use App\Models\Extra\Notification;
use App\Models\ExclusiveLock;

class AutoCourierAssign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'courier:assign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto Courier Assign which if prev track address is equal to customer address';

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

        Track::query()
            ->where(function ($q) {
                $q->where('delivery_type', 'HD')
                    ->orWhereNull('delivery_type');
            })
            ->whereIn('partner_id', [9, 1])
            ->whereNull('courier_delivery_id')
            ->where('created_at', '>=', '2025-07-01 00:00:00')
            ->orderBy('id')
            ->chunk(100, function ($tracks) {
                foreach ($tracks as $track) {
                    try {
                        if ($track->debt_price > 0 && $track->paid_debt == 0) {
                            $this->error("Items have debt price for track: {$track->id}");
                            continue;
                        }

                        if ($track->customer_id) {
                            $customer = $track->customer;
                            $courier_id = null;

                            $prevTrack = Track::where('customer_id', $customer->id)
                                ->whereHas('courier_delivery.courier', function ($q) {
                                    $q->whereNull('deleted_at');
                                })
                                ->where('status', 17)
                                ->where('id', '!=', $track->id)
                                ->with('courier_delivery')
                                ->orderByDesc('id')
                                ->first();

                            if (
                                $prevTrack &&
                                $prevTrack->courier_delivery &&
                                $prevTrack->courier_delivery->address === $customer->address
                            ) {
                                $courier = null;

                                if ($customer->courier_id) {
                                    $customerCourier = \App\Models\Courier::withTrashed()->find($customer->courier_id);
                                    if ($customerCourier && $customerCourier->deleted_at === null) {
                                        $courier = $customerCourier;
                                    }
                                }

                                if (!$courier && $prevTrack->courier_delivery->courier && $prevTrack->courier_delivery->courier->deleted_at === null) {
                                    $courier = $prevTrack->courier_delivery->courier;
                                }

                                if (!$courier) {
                                    $this->info("Courier tapılmadı və ya silinib: {$track->id}, skipping courier assignment.");
                                    continue;
                                }

                                $courier_id = $courier->id;
                            } else {
                                $this->info("Adress fərqlidir və ya birinci trackidir.: {$track->id}, skipping courier assignment.");
                                continue;
                            }



                            $cd_status = 1;
                            $cd = $track->courier_delivery;

                            if ($cd) {
                                $cd_status = $cd->status;
                            }

                            $str = $track->worker_comments;

                            if (isOfficeWord($str)) {
                                CD::removeTrack($cd, $track);
                                $this->info("Track {$track->id} removed from delivery.");
                                continue;
                            }

                            if ($cd && (($cd->courier_id != $courier_id) || ($cd->address != $customer->address))) {
                                $cd = CD::updateTrack($cd, $track, $courier_id);
                            }

                            if (!$cd) {
                                $cd = CD::newCD($track, $courier_id, $cd_status);
                            }

                            $cd->save();
                            $track->courier_delivery_id = $cd->id;
                            $track->save();

                            if (!$customer->courier_id) {
                                $customer->courier_id = $courier_id;
                                $customer->save();
                            }

                            $this->info('Courier assigned');
                        }
                    } catch (\Throwable $e) {
                        $this->error("Exception at track {$track->id}: " . $e->getMessage());
                        \Log::error("Courier assign failed for track {$track->id}", [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }
                }
            });

    }
}
