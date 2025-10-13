<?php

namespace App\Http\Controllers\Api\Meest\Pallet;

use App\Models\Airbox;
use App\Models\CargoBox;
use App\Models\Container;
use App\Models\Flight;
use App\Models\Track;
use App\Services\Integration\MeestService;
use Illuminate\Http\Request;
use Validator;

class ShipmentController
{
    public function finish(Request $request)
    {
        try {
            $this->rules = [
                'pallet_barcodes' => 'required|array',
//            'shipment.barcode' => 'required',
//            'shipment.is_finish' => 'required',
//            'shipment.total_count' => 'required',
//            'shipment.total_weight' => 'required',
                'shipment.awb_number' => 'required',
//            'shipment.air_transporter' => 'required',
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
                ->where('partner_id', MeestService::PARTNERS_MAP['CHINA_MEEST'])
                ->first();

            if(!$container) {
                $container = new Container();
                $container->name = $shipment['awb_number'];
                $container->partner_id = MeestService::PARTNERS_MAP['CHINA_MEEST'];
                $container->save();
            }

            Airbox::query()
                ->whereIn('name', $request->input('pallet_barcodes'))
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
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => explode("\n", $e->getTraceAsString()), // istəsən qısalda bilərik
            ], 500);
        }
    }
}
