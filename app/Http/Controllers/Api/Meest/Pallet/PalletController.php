<?php

namespace App\Http\Controllers\Api\Meest\Pallet;

use App\Models\Airbox;
use App\Models\CargoBox;
use App\Models\Track;
use App\Services\Integration\MeestService;
use Illuminate\Http\Request;
use Validator;

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

        $box = Airbox::query()->updateOrCreate(
            [
                "name" => $request->barcode,
                "partner_id" => MeestService::PARTNERS_MAP['CHINA_MEEST'],
            ],
            [
                "container_id" => null,
                "total_weight" => $request->total_weight,
                "total_count" => $request->total_count,
            ]
        );

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

        if (!empty($exists)) {
            Track::query()->whereIn('id', $exists)->update([
                'airbox_id' => $box->id
            ]);
        }

        $response = [
            "status" => true,
            "message" => "Pallet created.",
            "data" => [
                "uuid" => $box->id,
                "barcode" => $box->name,
                "total_weight" => $box->total_weight,
                "total_count" => $box->total_count,
                "createdAt" => $box->created_at,
                "updatedAt" => $box->updated_at,
            ]
        ];

        if (!empty($notExistsTracks)) {
            $response['errors'] = [
                'message' => "Some packages not found",
                'data' => $notExistsTracks
            ];
        }

        return response()->json($response);
    }


    public function update(Request $request)
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
        $exists = [];

        foreach ($request->parcel_ids as $trackingCode) {
            $track = Track::where("tracking_code", $trackingCode)->first();
            if ($track) {
                $exists[] = $track->id;
            } else {
                $notExistsTracks[] = $trackingCode;
            }
        }

        if (!empty($exists)) {
            Track::query()->whereIn('id', $exists)->update([
                'airbox_id' => $box->id
            ]);
        }

        $response = [
            "status" => true,
            "message" => "Pallet updated successfully.",
            "data" => [
                "uuid" => $box->id,
                "barcode" => $box->name,
                "total_weight" => $box->total_weight,
                "total_count" => $box->total_count,
                "sorting_letter" => $box->sorting_letter,
                "createdAt" => $box->created_at,
                "updatedAt" => $box->updated_at,
            ]
        ];

        if (!empty($notExistsTracks)) {
            $response['errors'] = [
                'message' => "Some packages not found",
                'data' => $notExistsTracks
            ];
        }

        return response()->json($response);
    }

}
