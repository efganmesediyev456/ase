<?php

namespace App\Http\Controllers\Api\Integration\Temu\Pallet;

use App\Models\Airbox;
use App\Models\Container;
use App\Models\Track;
use App\Services\Integration\UnitradeService;
use Illuminate\Http\Request;
use Validator;

class ShipmentController
{
    public function finish(Request $request)
    {
        $this->rules = [
            'pallet_barcodes' => 'required|array',
            'shipment.awb_number' => 'required',
        ];

        $validator = Validator::make($request->all(), $this->rules);
        if ($validator->fails()) {
            return response([
                'code' => 400,
                'messages' => $validator->errors()->all()
            ], 400);
        }

        $shipment = $request->input('shipment');

        $container = Container::query()
            ->where('name', $shipment['awb_number'])
            ->where('partner_id', UnitradeService::PARTNERS_MAP['TAOBAO'])
            ->first();
        if(!$container) {
            $container = new Container();
            $container->name = $shipment['awb_number'];
            $container->partner_id = UnitradeService::PARTNERS_MAP['TAOBAO'];
            $container->save();
        }

        $palletBarcodes = $request->input('pallet_barcodes');
        if(!is_array($palletBarcodes)) {
            $palletBarcodes = json_decode($palletBarcodes, true);
        }
        Airbox::query()
            ->whereIn('name', $palletBarcodes)
            ->update([
                'container_id' => $container->id
            ]);

        $airboxes = Airbox::query()->whereIn('name', $request->input('pallet_barcodes'))->get()->pluck('id');
        Track::query()
            ->whereIn('airbox_id', $airboxes)
            ->update([
                'container_id' => $container->id
            ]);

        return response()->json(['success' => true], 200);
    }
}
