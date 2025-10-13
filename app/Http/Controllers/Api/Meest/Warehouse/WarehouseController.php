<?php

namespace App\Http\Controllers\Api\Meest\Warehouse;

use App\Http\Controllers\Controller;
use App\Http\Resources\Meest\WarehouseResource;
use App\Models\AzeriExpress\AzeriExpressOffice;
use App\Models\Azerpost\AzerpostOffice;
use App\Models\DeliveryPoint;
use App\Models\Surat\SuratOffice;
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
        $azerpostB = AzerpostOffice::with(['city'])->get();
        $azerpostB->map(function ($warehouse) use ($warehouses) {
            $warehouses->push([
                'uid' => "AZ" . $warehouse->id,
                'name' => $warehouse->name,
                'name_en' => $warehouse->name_en,
                'city' => $warehouse->city ? $warehouse->city->translate('az')->name : null,
                'city_en' => $warehouse->city ? $warehouse->city->translate('en')->name??null : null,
                'description' => $warehouse->description,
                'description_en' => $warehouse->description_en,
                'address' => $warehouse->address,
                'address_en' => $warehouse->address_en,
                'data' => $warehouse,
                'zip_code' => $warehouse->name,
            ]);
        });

        //Azeriexpress
        $azeriExpressB = AzeriExpressOffice::with(['city'])->get();
        $azeriExpressB->map(function ($warehouse) use ($warehouses) {
            $warehouses->push([
                'uid' => "EX" . $warehouse->id,
                'name' => $warehouse->name,
                'name_en' => $warehouse->name_en,
                'city' => $warehouse->city ? $warehouse->city->translate('az')->name : null,
                'city_en' => $warehouse->city ? $warehouse->city->translate('en')->name??null : null,
                'description' => $warehouse->description,
                'description_en' => $warehouse->description_en,
                'address' => $warehouse->address,
                'address_en' => $warehouse->address_en,
                'data' => $warehouse,
                'zip_code' => ''
            ]);
        });

        //Precinct
        $precinctB = DeliveryPoint::with('city')
            ->where('id', '!=', 23)
            ->get();

        $precinctB->map(function ($warehouse) use ($warehouses) {
            $warehouses->push([
                'uid' => "DP" . $warehouse->id,
                'name' => $warehouse->name,
                'name_en' => $warehouse->name_en,
                'city' => $warehouse->city ? $warehouse->city->translate('az')->name : null,
                'city_en' => $warehouse->city ? $warehouse->city->translate('en')->name??null : null,
                'description' => $warehouse->description,
                'description_en' => $warehouse->description_en,
                'address' => $warehouse->address,
                'address_en' => $warehouse->address_en,
                'data' => $warehouse,
                'zip_code' => ''
            ]);
        });

        //Precinct
        $suratB = SuratOffice::with(['city'])->get();
        $suratB->map(function ($warehouse) use ($warehouses) {
            $warehouses->push([
                'uid' => "SR" . $warehouse->id,
                'name' => $warehouse->name,
                'name_en' => $warehouse->name_en,
                'city' => $warehouse->city ? $warehouse->city->translate('az')->name : null,
                'city_en' => $warehouse->city ? $warehouse->city->translate('en')->name??null : null,
                'description' => $warehouse->description,
                'description_en' => $warehouse->description_en,
                'address' => $warehouse->address,
                'address_en' => $warehouse->address_en,
                'data' => $warehouse,
                'zip_code' => ''
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
