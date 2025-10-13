<?php

namespace App\Http\Controllers\Api\Integration\Temu\Pallet;

use App\Models\Airbox;
use App\Models\Track;
use App\Services\Integration\UnitradeService;
use Illuminate\Http\Request;

class PalletController
{
    public function store(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string|max:255',
            'total_weight' => 'required',
            'total_count' => 'required',
            'sorting_letter' => 'nullable|string',
            'parcel_ids' => 'required|array',
        ]);

        $box = Airbox::updateOrCreate([
            "name" => $request->barcode,
            "partner_id" => UnitradeService::PARTNERS_MAP['TAOBAO'],
        ],[
            "container_id" => null,
            "total_weight" => $request->total_weight,
            "total_count" => $request->total_count,
        ]);

        $notExistsTracks = [];
        $exists = [];
        foreach ($request->parcel_ids as $trackingCode) {
            $track = Track::where("tracking_code", $trackingCode)->first();
            if ($track) {
                $exists[] = $track->id;
            } else {
                $notExistsTracks[] = $trackingCode;
            }
        }
        Track::query()->whereIn('id', $exists)->update([
            'airbox_id' => $box->id
        ]);

        return response()->json([
            "status" => true,
            "message" => "Pallet created.",
            "data" => [
                "uuid" => $box->id,
                "barcode" => $box->name,
                "total_weight" => $box->total_weight,
                "total_count" => $box->total_count,
                "createdAt" => $box->created_at,
                "updatedAt" => $box->updated_at,
            ],
            'errors' => [
                'message' => "Packages not found",
                'data' => $notExistsTracks
            ]
        ]);
    }

    public function update($partnerCompany, Request $request)
    {
        $request->validate([
            'barcode' => 'required|string|max:255|exists:airboxes,name',
            'total_weight' => 'required',
            'total_count' => 'required',
            'sorting_letter' => 'nullable|string',
            'parcel_ids' => 'required|array',
        ]);

        $box = Airbox::query()->where('name', $request->barcode)->first();

        $box->update([
            "total_weight" => $request->total_weight,
            "total_count" => $request->total_count,
            "sorting_letter" => $request->sorting_letter ?? '',
        ]);

        $notExistsTracks = [];
        foreach ($request->parcel_ids as $trackingCode) {
            $track = Track::where("tracking_code", $trackingCode)->first();
            if ($track) {
                $track->airbox_id = $box->id;
            } else {
                $notExistsTracks[] = $trackingCode;
            }
        }

        return response()->json([
            "status" => true,
            "message" => "Pallet updated Successfully.",
            "data" => [
                "uuid" => $box->id,
                "barcode" => $box->name,
                "total_weight" => $box->total_weight,
                "total_count" => $box->total_count,
                "sorting_letter" => "",
                "createdAt" => $box->created_at,
                "updatedAt" => $box->updated_at,
            ],
            'errors' => [
                'message' => "Packages not found",
                'data' => $notExistsTracks
            ]
        ]);
    }
}
