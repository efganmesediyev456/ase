<?php

namespace App\Http\Controllers\Api\Meest\Package;


use App\Models\PackageStatus;
use App\Models\Track;
use App\Models\TrackStatus;
use App\Models\Trendyol\TrendyolPackage;
use App\Models\UnitradePackage;
use App\Repositories\PackageRepository;
use App\Services\Integration\MeestService;
use Illuminate\Http\Request;

class PackageStateController
{
    public function update($partnerCompany, $tracking, Request $request)
    {
        //TODO: Take into consideration //Deleted//REJECTED

        $track = Track::query()->where(['tracking_code' => $tracking])->first();
        $status = MeestService::STATES[$request->state];

        if (!$track) {
            return \response()->json([
                "code" => 404,
                "message" => "Package with provided Tracking code not found!",
                "data" => []
            ]);
        }

        if (!$status) {
            return \response()->json([
                "code" => 404,
                "message" => "State not found!",
                "data" => []
            ]);
        }

        $track->update(['status' => $status]);
        if ($request->state == "Deleted" || $request->state == "Rejected") {
            $track->delete();
        }

        TrackStatus::create([
            'track_id' => $track->id,
            'user_id' => 1,
            'status' => $status,
            'note' => $request->author . ' ' . $request->comment
        ]);

        UnitradePackage::where([
            'track_id' => $track->id
        ])->update([
            'status' => $status
        ]);

        return \response()->json([
            "code" => 200,
            "message" => "success",
            "data" => []
        ]);
    }


    public function show( $tracking, Request $request)
    {
        $track = Track::query()->where('tracking_code', $tracking)->first();
        if ($track) {

            $statuses = TrackStatus::query()->where('track_id', $track->id)->get();
            $statuses->transform(function ($status) {
                $st = array_search($status->status, MeestService::STATES);
                return [
                    'status' => $st,
                    'date' => $status->created_at->format('d-m-Y H:i'),
                    'note' => null,
                ];
            });
            return \response()->json([
                "status" => true,
                "message" => "Statuses successfully fetched!",
                "data" => $statuses
            ], 200);
        }

        return \response()->json([
            "status" => false,
            "message" => "Package not exists!",
            "data" => []
        ], 404);
    }

}
