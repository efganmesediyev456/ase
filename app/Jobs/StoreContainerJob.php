<?php

namespace App\Jobs;

use App\Models\Airbox;
use App\Models\Track;
use App\Services\Integration\GfsService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class StoreContainerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $container;
    private $pallet;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($container, $pallet)
    {
        $this->container = $container;
        $this->pallet = $pallet;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $exists = [];
        $tW = 0;
        $tC = 0;

        // Process each package within the pallet
        foreach ($this->pallet['unitPackages'] as $package) {
            $track = Track::where("tracking_code", $package['waybillNo'])->first();
            if ($track) {
                $exists[] = $track->id;
                foreach ($package['goodsInfos'] as $goodsInfo) {
                    $tW += $goodsInfo['weight'];
                    $tC++;
                }
            }
        }

        // Create Airbox for the current pallet
        $airbox = Airbox::updateOrCreate(
            [
                'name' => $this->pallet['waybillNo'],  // Matching condition
                'container_id' => $this->container->id
            ],
            [
                'partner_id'   => GfsService::PARTNERS_MAP['GFS'],
                'total_weight' => $tW,
                'total_count'  => $tC,
            ]
        );

        DB::table('tracks')->whereIn('id', $exists)->update([
            'airbox_id' => $airbox->id,
            'container_id' => $this->container->id
        ]);
    }
}
