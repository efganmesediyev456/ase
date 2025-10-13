<?php

namespace App\Http\Controllers\Api\Integration\Gfs\Package;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GfsTrackCreateRequest;
use App\Http\Requests\Api\TrackCreateRequest;
use App\Http\Requests\Api\TrackUpdateRequest;
use App\Models\Customer;
use App\Models\Track;
use App\Models\UnitradePackage;
use App\Services\Integration\BaseService;
use App\Services\Integration\GfsService;
use App\Services\Integration\UnitradeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class PackageController extends Controller
{
    private $gfsService;

    public function __construct(GfsService $gfsService)
    {
        $this->gfsService = $gfsService;
    }

    public function show($deliveryNumber)
    {
        $response = [
            'status' => true,
            'message' => "Api not ready for Prod!",
            'data' => []
        ];
        return response()->json($response);
    }

    public function store(GfsTrackCreateRequest $request, $id)
    {
        $data = json_decode($request->data);
        $data->partner_id = $this->gfsService::PARTNERS_MAP['GFS'];

        $phoneNumber = preg_replace('/[^0-9]/', '', $data->consigneeInfo->cellphone);
        $pin_code = $data->consigneeInfo->identify;
        $email = $data->consigneeInfo->email??'';


        $warehousePrefix = substr($data->pickUpSiteCode, 0, 2);
        $warehouseId = substr($data->pickUpSiteCode, -(strlen($data->pickUpSiteCode) - 2));

        $warehouseTable = array_search($warehousePrefix, BaseService::WAREHOUSE);
        if (!$pin_code) {
            return Response::json([
                "code" => 404,
                "message" => "Identify required!!",
                "data" => [],
            ], 400);
        }
        if ($warehouseTable === false || !DB::table($warehouseTable)->where('id', $warehouseId)->exists()) {
            return Response::json([
                "code" => 404,
                "message" => "Warehouse not found!",
                "data" => [],
            ], 400);
        }

        $track = Track::query()
            ->where('tracking_code', $data->waybillNo)
            ->first();
        if ($track) {
            $response = [
                'status' => false,
                'message' => "Package already created!",
                'data' => []
            ];
            return response()->json($response, 400);
        }

        $user = Customer::query()
            ->where('fin', $pin_code)
            ->where('phone', "994$phoneNumber")
            ->where('partner_id', BaseService::PARTNERS_MAP['GFS'])
            ->first();
        if ($user == null) {
            $user = $this->gfsService->createCustomer($data);
        }

        //handle package
        $package = $this->gfsService->createPackage($data, $user);
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

        // Extract request data
        $buyer = $request->buyer;
        $pin_code = $buyer['pin_code'];
        $email = $buyer['email_address'];

        $phoneNumber = preg_replace('/[^0-9]/', '', $request->buyer['phone_number']);
        $mobile = substr($phoneNumber, 3);

//         Find or create a user
//        $user = User::where('fin', $pin_code)
//            ->orWhere('email', $email)
//            ->orWhere('phone', $mobile)
//            ->firstOrFail();

        // Handle the package
        $package = $this->gfsService->updatePackage($unitradePackageQuery, $request);
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
