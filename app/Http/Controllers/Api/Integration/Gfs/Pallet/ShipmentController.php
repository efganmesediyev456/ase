<?php

namespace App\Http\Controllers\Api\Integration\Gfs\Pallet;

use App\Jobs\StoreContainerJob;
use App\Models\Container;
use App\Services\Integration\GfsService;
use Illuminate\Http\Request;
use Validator;

class ShipmentController
{
    public function store(Request $request, $id = 1)
    {
        $data = json_decode($request->data, true);

        if (!isset($data['outOrderNo']) || !is_string($data['outOrderNo'])) {
            return response()->json(['status' => false, 'message' => 'Invalid outOrderNo'], 400);
        }
        if (!isset($data['bigPackages']) || !is_array($data['bigPackages'])) {
            return response()->json(['status' => false, 'message' => 'Invalid bigPackages'], 400);
        }


        // Create Container
//        $wayBill = $data['outOrderNo'];
//        $container = new Container();
//        $container->name = $wayBill;
//        $container->partner_id = GfsService::PARTNERS_MAP['GFS'];
//        $container->created_at = now();
//        $container->save();

        $wayBill = $data['outOrderNo'];
        $container = Container::updateOrCreate(
            ['name' => $wayBill],
            [
                'partner_id'  => GfsService::PARTNERS_MAP['GFS'],
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );

        $messages = [];
        foreach ($data['bigPackages'] as $pallet) {
            if (!isset($pallet['waybillNo']) || !is_string($pallet['waybillNo'])) {
                $messages[] = ['bigPackages_waybill' => $pallet['waybillNo'], 'message' => 'Invalid pallet waybillNo'];
            } elseif (!isset($pallet['unitPackages']) || !is_array($pallet['unitPackages'])) {
                $messages[] = ['bigPackages_waybill' => $pallet['waybillNo'], 'message' => 'Invalid unitPackages'];
            } else {
                StoreContainerJob::dispatch($container, $pallet);
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Shipment created.',
            'data' => [
                'uid' => $container->id,
                'barcode' => $container->name,
                'createdAt' => $container->created_at,
            ],
            'errors' => [
                'message' => 'Packages not found',
                'data' => $messages,
            ]
        ]);
    }
}
