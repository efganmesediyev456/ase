<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\HandleKuryeraCourierStatusUpdateJob;
use App\Models\CD;
use App\Models\Courier;
use App\Models\Package;
use App\Models\Track;
use Illuminate\Http\Request;
use Log;
use Milon\Barcode\DNS1D;

class KuryeraController extends Controller
{
    public function updateCourierStatus(Request $request)
    {
        $request->validate([
            'tracking_code' => ['required'],
            'status' => ['required', 'in:5,7,9,34,35'],
            'reason' => ['nullable']
        ]);

        $tracking = $request->input('tracking_code');
        $status = $request->input('status');
        $reason = $request->input('reason');

        HandleKuryeraCourierStatusUpdateJob::dispatch($tracking, $status, $reason);

        return response()->json(['status' => true, 'data' => []]);
    }

    public function getParcel($tracking, Request $request)
    {
        $parcel = Track::query()->with(['customer'])->where('tracking_code', $tracking)->first();
        if (!$parcel) {
            $parcel = Package::query()->with(['user'])->where('custom_id', $tracking)->first();
        }

        if (!$parcel) {
            return response()->json(['status' => false, 'message' => "Parcel not found!", 'data' => []]);
        }
        if (!$parcel->user) {
            return response()->json(['status' => false, 'message' => "Something went wrong!", 'data' => []]);
        }

//        $courier = Courier::where('email', 'kuryera@ase.az')->first();
        CD::newCD($parcel, 166, 2);

        $data = [
            "hub_uuid" => "",
            "payment_method" => 1,
            "pickup" => [
                "uuid" => "",
                "phone" => "",
                "address" => "",
                "lat" => "40.413135",
                "long" => "49.853529"
            ],
            "customer" => [
                "code" => "" . ($parcel->user->id ?? ""),
                "firstname" => (isset($parcel->fullname) && $parcel->fullname != "") ? explode(" ", $parcel->fullname)[0] : explode(" ", $parcel->user->fullname)[0],
                "lastname" => (isset($parcel->fullname) && $parcel->fullname != "") ? explode(" ", $parcel->fullname)[1] : explode(" ", $parcel->user->fullname)[1],
                "fullname" => $parcel->user->fullname,
                "passport" => $parcel->user->passport ?? "-",
                "fincode" => $parcel->user->fincode ?? "-",
                "phone" => $parcel->phone
            ],
            "parcel" => [
                "barcode" => $parcel->tracking_code,
                "weight" => $parcel->weight,
                "price" => $parcel->shipping_amount,
                "currency" => $parcel->currency,
                "cod_amount" => 0.00,
                "cod_delivery" => $parcel->delivery_price,
                "cash_collection" => 0.00,
                "is_paid" => true,
                "lat" => $parcel->latitude ?? null,
                "long" => $parcel->longitude ?? null,
                "address" => $parcel->address
            ]
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }
}
