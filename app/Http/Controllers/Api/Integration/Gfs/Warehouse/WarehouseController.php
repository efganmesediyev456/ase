<?php

namespace App\Http\Controllers\Api\Integration\Temu\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Resources\Ozon\WarehouseResource;
use App\Models\AzeriExpress\AzeriExpressOffice;
use App\Models\Azerpost\AzerpostOffice;
use App\Models\DeliveryPoint;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function __construct()
    {

    }

    public function index(Request $request)
    {
        $request->headers->set('Content-Type', 'application/json');
        $request->headers->set('Accept', 'application/json');

        $warehouses = collect();

        //Azerpost
        $azerpostB = AzerpostOffice::where('is_active',1)->get();
        $azerpostB->map(function ($warehouse) use ($warehouses) {
            $warehouses->push([
                'uid' => "AZ" . $warehouse->id,
                'name' => $warehouse->name,
                'description' => $warehouse->description,
                'address' => $warehouse->address,
            ]);
        });

        //Azeriexpress
        $azeriExpressB = AzeriExpressOffice::where('is_active',1)->get();
        $azeriExpressB->map(function ($warehouse) use ($warehouses) {
            $warehouses->push([
                'uid' => "EX" . $warehouse->id,
                'name' => $warehouse->name,
                'description' => $warehouse->description,
                'address' => $warehouse->address,
            ]);
        });

        //Precinct
        $precinctB = DeliveryPoint::where('is_active',1)->whereNotIn('id',[10,11,12,13,14,15,16,17,18,19])->get();
        $precinctB->map(function ($warehouse) use ($warehouses) {
            $warehouses->push([
                'uid' => "DP" . $warehouse->id,
                'name' => $warehouse->name,
                'description' => $warehouse->description,
                'address' => $warehouse->address,
            ]);
        });

        return new WarehouseResource($warehouses);
    }

    public function show(Request $request, $id)
    {
        return response()->json([
            "status" => false,
            "message" => "API is not ready for prod yet!",
            "data" => []
        ], 400);
    }
}
