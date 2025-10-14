<?php

namespace App\Console\Commands;

use App\Models\CD;
use App\Models\Track;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class AssignCourierCommand extends Command
{

    protected $signature = 'assign:courier';

    protected $description = 'Assign a courier to a track';

    public function handle()
    {

        $tracks = Track::query()
            ->where('delivery_type', 'HD')
            ->where('partner_id',3)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNull('courier_delivery_id')
            ->whereDate('created_at', '>=', '2025-06-01')
            ->limit(300)
            ->get();

//        dd($tracks);

        foreach ($tracks as $track) {
            if($track->debt_price > 0 && $track->paid_debt == 0){
                $this->error("Track ID {$track->id} borcu ödənməyib.");
                continue;
            }

            $res = $this->findCourier($track->latitude,$track->longitude);

//            $this->info($res['courier']);

            $courier_id = $res['id'];
            $this->line("Courier ID: {$courier_id} tapıldı.");
            if(!$courier_id){
                $this->line("Problem: {$track->tracking_code} Tapilmadi.");
            }
            $cd_status = 1; //accepted
            $cd = $track->courier_delivery;
            if ($cd) {
                $cd_status = $cd->status;
            }
            $str = $track->worker_comments;
            if (!$courier_id || isOfficeWord($str)) {
                CD::removeTrack($cd, $track);
                continue;
            }
            if ($cd && (($cd->courier_id != $courier_id) || ($cd->address != $track->address))) {
                $cd = CD::updateTrack($cd, $track, $courier_id);
            }

            $new_cd = false;
            if (!$cd) {
                $new_cd = true;
                $cd = CD::newCD($track, $courier_id, $cd_status);
            }
            $cd->save();
            $track->courier_delivery_id = $cd->id;

            $track->save();
//            $this->line("AUTO Courier: {$track->tracking_code} Yaradildi.");
        }
    }

    public function findCourier($lat, $lon)
    {
        $regions = json_decode(file_get_contents(storage_path('app/regions.json')), true);

        foreach ($regions as $region) {
            if ($this->pointInPolygon([$lat, $lon], $region['points'])) {
                return ['courier' => $region['name'], 'id' => $region['id']];
            }
        }

        return ['courier' => null, 'id' => null, 'message' => 'No region found'];
    }

    private function pointInPolygon($point, $polygon)
    {
        $x = $point[1];
        $y = $point[0];
        $inside = false;
        $n = count($polygon);
        for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
            $xi = $polygon[$i][1]; $yi = $polygon[$i][0];
            $xj = $polygon[$j][1]; $yj = $polygon[$j][0];

            $intersect = (($yi > $y) != ($yj > $y)) &&
                ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi + 0.0000001) + $xi);
            if ($intersect) $inside = !$inside;
        }
        return $inside;
    }
}
