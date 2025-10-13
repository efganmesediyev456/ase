<?php

namespace App\Http\Controllers\Api\Integration\Temu\Package;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TrackCreateRequest;
use App\Http\Requests\Api\TrackUpdateRequest;
use App\Models\Customer;
use App\Models\Track;
use App\Models\UnitradePackage;
use App\Services\Integration\BaseService;
use App\Services\Integration\EquickService;
use App\Services\Integration\UnitradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class PackageController extends Controller
{
    private $equickService;

    public function __construct(EquickService $equickService)
    {
        $this->equickService = $equickService;
    }

    public function show($deliveryNumber)
    {
        $unitradePackage = UnitradePackage::query()
            ->with(['package', 'user'])
            ->where('delivery_number', $deliveryNumber)
            ->first();
        if (!$unitradePackage) {
            return response()->json([
                "status" => false,
                "message" => "Package with this deliveryNumber not found",
                "data" => [
                    "deliveryNumber" => $deliveryNumber
                ]
            ], 404);
        }

        $response = [
            'status' => true,
            'message' => "Package successfully fetched!",
            'data' => $this->equickService->prepareResponse($unitradePackage)
        ];
        return response()->json($response);
    }

    public function store(TrackCreateRequest $request)
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $request->buyer['phone_number']);

        $pin_code = $request->buyer['pin_code'];
        $email = $request->buyer['email_address'];

        $warehousePrefix = substr($request->warehouse_id, 0, 2);
        $warehouseId = substr($request->warehouse_id, -(strlen($request->warehouse_id) - 2));

        $warehouseTable = array_search($warehousePrefix, BaseService::WAREHOUSE);
        if ($warehouseTable === false || !DB::table($warehouseTable)->where('id', $warehouseId)->exists()) {
            return Response::json([
                "code" => 404,
                "message" => "Warehouse not found!",
                "data" => [],
            ], 400);
        }

        $unitradePackage = UnitradePackage::query()
            ->with(['package', 'user'])
            ->where('delivery_number', $request->input('delivery_number'))
            ->first();
        if ($unitradePackage) {
            $response = [
                'status' => false,
                'message' => "Package already created!",
                'data' => $this->equickService->prepareResponse($unitradePackage)
            ];
            return response()->json($response, 400);
        }

        $user = null;

        if($pin_code){
            $user = Customer::query()
                ->where('fin', $pin_code)
                ->where('phone', "994$phoneNumber")
                ->where('partner_id', BaseService::PARTNERS_MAP['TAOBAO'])
                ->first();
        }

        if (!$user) {
            $request->merge(['partner_id' => BaseService::PARTNERS_MAP['TAOBAO']]);
            $user = $this->equickService->createCustomer($request);
        }

        $request->merge(['partner' => 'TAOBAO']);
        //handle package
        $package = $this->equickService->createPackage($request, $user);
        $response = [
            'status' => true,
            'message' => "Package successfully created!",
            'data' => $package
        ];

        return response()->json($response, 201);
    }

    public function update(TrackUpdateRequest $request): JsonResponse
    {
        $unitradePackageQuery = UnitradePackage::with(['package', 'user'])->where([
            'delivery_number' => $request->input('delivery_number'),
            'track_id' => $request->input('package_id'),
        ]);
        if (!$unitradePackageQuery->first()) {
            $response = [
                'status' => false,
                'message' => "Package with following credentials not Found!",
                'data' => [
                    'delivery_number' => $request->input('delivery_number'),
                    'package_id' => $request->input('package_id'),
                ]
            ];
            return response()->json($response, 404);
        }

        // Handle the package
        $package = $this->equickService->updatePackage($unitradePackageQuery, $request);
        $response = [
            'status' => true,
            'message' => "Package successfully updated!",
            'data' => $package
        ];

        return response()->json($response, 201);
    }

    public function delete($trackingCode)
    {
        $track = Track::query()->where('tracking_code', $trackingCode)->first();
        if (!$track) {
            return response()->json([
                "code" => 404,
                "message" => "Package not found",
                "data" => []
            ]);
        }

        Track::query()->where('tracking_code', $trackingCode)->delete();

        return response()->json([
            "code" => 200,
            "message" => "Package deleted from system",
            "data" => []
        ]);
    }
}
