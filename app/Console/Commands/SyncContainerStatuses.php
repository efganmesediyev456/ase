<?php

namespace App\Console\Commands;

use App\Models\Package;
use App\Models\Precinct\PrecinctOrder;
use App\Models\Precinct\PrecinctPackage;
use App\Models\Request;
use App\Models\Track;
use Artisan;
use Illuminate\Console\Command;
use GuzzleHttp\Client;
use Log;
use Carbon\Carbon;


class SyncContainerStatuses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'syncContainer {--type=send}';

    /**
     * The console command description.
     *
     * @var string
     */

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

        if ($this->option('type') == 'syncPrecinct') {
            $this->syncPrecinct();
        }

        if ($this->option('type') == 'syncPackageWithPrecint') {
            $this->syncDeliveredWithPrecinctContainer();
        }

    }

    public function syncPrecinct()
    {
        $precintOrders = PrecinctOrder::where('status','!=',PrecinctOrder::STATUSES['DELIVERED'])->where('created_at', '>=', Carbon::now()->subMonths(3))->get();

        foreach ($precintOrders as $precintOrder) {
            // Get counts of each status
            $precinct_package_counts = PrecinctPackage::where('precinct_order_id', $precintOrder->id)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

//            dd($precinct_package_counts,$precintOrder->id);
            $totalPackageCount = array_sum($precinct_package_counts);

            foreach ($precinct_package_counts as $status => $count) {
                if ($count === $totalPackageCount) {
                    $statusName = array_search($status, PrecinctPackage::STATUSES);
                    if ($statusName == 'NOT_SENT'){
                        $statusName = 'WAITING';
                    }
                    if ($precintOrder->status == PrecinctOrder::STATUSES[$statusName]){
                        $this->line('status is same : '.$precintOrder->id);
                        continue;
                    }
                    //All packages have the same status, update the order
                    $precintOrder->status = PrecinctOrder::STATUSES[$statusName];
                    $precintOrder->save();
                    $this->line($precintOrder->id,$precintOrder->name);
                    $this->line(PrecinctOrder::STATUSES[$statusName]);
                    break; // no need to check further
                }
            }
        }
    }

    public function syncDeliveredWithPrecinctContainer(){
        $precinct_packages = PrecinctPackage::with(['package'])
            ->where('status', '!=', PrecinctPackage::STATUSES['DELIVERED'])
            ->where('type','package')
            ->where('created_at', '>=', Carbon::now()->subMonths(3))
            ->whereHas('package', function ($query) {
                $query->where('status', 3);
            })
            ->get();

        $precint_tracks = PrecinctPackage::with(['track'])
            ->where('status', '!=', PrecinctPackage::STATUSES['DELIVERED'])
            ->where('type','track')
            ->where('created_at', '>=', Carbon::now()->subMonths(3))
            ->whereHas('track', function ($query) {
                $query->where('status', 17);
            })
            ->get();

        $barcodes = [];

        foreach ($precint_tracks as $precint_track){
            $barcodes[]=$precint_track->barcode;
        }

        foreach ($precinct_packages as $precinct_package){
            $barcodes[]=$precinct_package->barcode;
        }

        PrecinctPackage::whereIn('barcode',$barcodes)->update([
            'status' => PrecinctPackage::STATUSES['DELIVERED']
        ]);
        dd('successfully synced');
    }

}


