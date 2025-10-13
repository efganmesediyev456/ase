<?php

namespace App\Http\Controllers\Api\Integration\Temu\Package;

use App\Models\PackageLog;
use App\Models\Track;
use App\Models\TrackStatus;
use App\Models\UnitradePackage;
use App\Services\Integration\UnitradeService;
use Illuminate\Http\Request;

class PackageStateController
{
    public function update($tracking, Request $request)
    {
        //REJECTED STATUS todo: Take into consideration //Deleted
//        return response()->json([
//            "code" => 403,
//            "message" => "Forbidden",
//            "data" => []
//        ]);

        $package = Track::query()->where(['tracking_code' => $tracking])->first();
        $status = UnitradeService::STATES[$request->state];

        if (!$package) {
            return response()->json([
                "code" => 404,
                "message" => "Package with provided Tracking code not found!",
                "data" => []
            ]);
        }

        if (!$status) {
            return response()->json([
                "code" => 404,
                "message" => "State not found!",
                "data" => []
            ]);
        }

        $data['status'] = [
            'before' => $package->status,
            'after' => $status,
            'changed_by' => null,
        ];
        $data['note'] = "Status $request->state dəyişdirildi (INBOUND comment: $request->comment)";
        $package->update(['status' => $status]);
        if($request->state == "Deleted" || $request->state == "Rejected"){
            $package->delete();
        }
        TrackStatus::create([
            'track_id' => $package->id,
            'user_id' => 1,
            'status' => $status,
            'note' => $request->author . ' ' . $request->comment
        ]);

        UnitradePackage::where([
            'track_id' => $package->id
        ])->update([
            'status' => $status
        ]);

        PackageLog::create([
            'package_id' => $package->id,
            'admin_id' => 1,
            'data' => json_encode($data),
        ]);

        return response()->json([
            "code" => 200,
            "message" => "success",
            "data" => []
        ]);
    }


    public function show($barcode, Request $request)
    {
        $track = Track::query()->where('tracking_code', $barcode)->first();
        if ($track) {

            $status = array_search($track->status, UnitradeService::STATES);
            return response()->json([
                "status" => true,
                "message" => "Status successfully fetched!",
                "data" => [
                    [
                        'status' => $status,
                        'date' => now()->format('d-m-Y H:i'),
                        'note' => null,
                    ]
                ]
            ], 404);
        }

        return response()->json([
            "status" => false,
            "message" => "Package not exists!",
            "data" => []
        ], 404);
    }

}
