<?php

namespace App\Http\Controllers\Api\Unitrade\Ozon\Pallet;

use App\Models\Airbox;
use App\Models\CargoBox;
use App\Models\Container;
use App\Models\Flight;
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

        $validator = \Validator::make($request->all(), $this->rules);
        if ($validator->fails()) {
            return response([
                'code' => 400,
                'messages' => $validator->errors()->all()
            ], 400);
        }

        $from_country = $request->input('from_country', 'RU');
        $shipment = $request->input('shipment');

        $container = Container::query()
            ->where('name', $shipment['awb_number'])
            ->where('partner_id', UnitradeService::PARTNERS_MAP['OZON'])
            ->first();

        if (!$container) {
            $container = new Container();
            $container->name = $shipment['awb_number'];
            $container->partner_id = UnitradeService::PARTNERS_MAP['OZON'];
            $container->from_country = $from_country;
            $container->save();
        }

        $airboxes = Airbox::query()
            ->whereIn('name', $request->input('pallet_barcodes'))
            ->get();

        $airboxIds = $airboxes->pluck('id');

        $trackCount = Track::query()
            ->whereIn('airbox_id', $airboxIds)
            ->count();

        if ($trackCount === 0) {
            return response()->json([
                'code' => 400,
                'message' => 'A pallet cannot be created without any associated packages.'
            ], 400);
        }

        Airbox::query()
            ->whereIn('id', $airboxIds)
            ->update([
                'container_id' => $container->id
            ]);

        Track::query()
            ->whereIn('airbox_id', $airboxIds)
            ->update([
                'container_id' => $container->id
            ]);

        return response()->json(['success' => true], 200);
    }

}
