<?php

namespace App\Http\Controllers\Api\Unitrade\Ozon\Package;


use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TrackCreateRequest;
use App\Http\Requests\Api\TrackUpdateRequest;
use App\Models\Customer;
use App\Models\ScGood;
use App\Models\Track;
use App\Models\Trendyol\TrendyolPackage;
use App\Models\UnitradePackage;
use App\Repositories\Trendyol\PackageTrendyolRepository;
use App\Repositories\Trendyol\UnitedRepository;
use App\Repositories\Trendyol\UserTrendyolRepository;
use App\Services\Integration\BaseService;
use App\Services\Integration\UnitradeService;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Validator;


class PackageController extends Controller
{
    /**
     * @var UnitradeService
     */
    private $unitradeService;

    public function __construct(UnitradeService $unitradeService)
    {
        $this->unitradeService = $unitradeService;
    }

    public function show($partnerCompany, $deliveryNumber)
    {
        $track = Track::query()
            ->with(['customer'])
            ->where('tracking_code', $deliveryNumber)
            ->first();
        if (!$track) {
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
            'data' => $this->unitradeService->prepareResponse($track)
        ];
        return response()->json($response);
    }

    public function store(TrackCreateRequest $request)
    {
        try {
            $phoneNumber = preg_replace('/[^0-9]/', '', $request->buyer['phone_number']);

            $pin_code = $request->buyer['pin_code'];
            $email = $request->buyer['email_address'];

            $warehousePrefix = substr($request->warehouse_id, 0, 2);
            $warehouseId = substr($request->warehouse_id, -(strlen($request->warehouse_id) - 2));

            $warehouseTable = array_search($warehousePrefix, BaseService::WAREHOUSE);
            if ($warehouseTable === false || !DB::table($warehouseTable)->where('id', $warehouseId)->exists()) {
//                return Response::json([
//                    "code" => 404,
//                    "message" => "Warehouse not found!",
//                    "data" => [],
//                ], 400);
                $request->merge(['warehouse_id' => 'DP23']);
            }

            $unitradePackage = UnitradePackage::query()
                ->with(['package', 'user'])
                ->where('delivery_number', $request->input('delivery_number'))
                ->first();

            if ($unitradePackage) {
                $response = [
                    'status' => false,
                    'message' => "Package already created!",
                    'data' => $this->unitradeService->prepareResponse($unitradePackage)
                ];
                return response()->json($response, 400);
            }

            $user = null;

            if($pin_code){
                $user = Customer::query()
                    ->where('fin', $pin_code)
                    ->where('phone', "994$phoneNumber")
                    ->where('partner_id', BaseService::PARTNERS_MAP['OZON'])
                    ->first();
            }

            if ($user == null) {
                $request->merge(['partner_id' => BaseService::PARTNERS_MAP['OZON']]);
                $user = $this->unitradeService->createCustomer($request);
            }

            //handle package
            $request->merge(['partner' => 'OZON']);
            $package = $this->unitradeService->createPackage($request, $user);
            $response = [
                'status' => true,
                'message' => "Package successfully created!",
                'data' => $package
            ];

            return response()->json($response, 201);
        }catch (\Exception $exception) {
            $botToken = "7784139238:AAGfstOZANbUgTV3hYKV8Xua8xQ_eJs5_wU";
            $website = "https://api.telegram.org/bot" . $botToken;
            $chatId = "-1002397303546";
            file_get_contents($website . "/sendMessage?chat_id=" . $chatId . "&text= ‼️ ASE Unitrade create" . json_encode([$exception->getMessage(), $exception->getLine()]));
            file_get_contents($website . "/sendMessage?chat_id=" . $chatId . "&text= ‼️ ASE Unitrade create response" . json_encode([$request->all()]));
        }

    }

    public function update(TrackUpdateRequest $request): JsonResponse
    {
        $unitradePackageQuery = UnitradePackage::with(['package', 'user'])->where([
            'delivery_number' => $request->input('delivery_number'),
            'track_id' => $request->input('package_id'),
        ]);
        $track = Track::query()->where('tracking_code', $request->input('delivery_number'))->first();

        if (!$unitradePackageQuery->first() || !$track) {
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
        $package = $this->unitradeService->updatePackage($unitradePackageQuery, $request);
        $response = [
            'status' => true,
            'message' => "Package successfully updated!",
            'data' => $package
        ];

        return response()->json($response, 201);
    }

    public function updatetest(TrackUpdateRequest $request): JsonResponse
    {
        $unitradePackageQuery = UnitradePackage::with(['package', 'user'])->where([
            'delivery_number' => $request->input('delivery_number'),
            'track_id' => $request->input('package_id'),
        ]);
        $track = Track::query()->where('tracking_code', $request->input('delivery_number'))->first();

        if (!$unitradePackageQuery->first() || !$track) {
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
//        $buyer = $request->buyer;
//        $pin_code = $buyer['pin_code'];
//        $email = $buyer['email_address'];
//
//        $phoneNumber = preg_replace('/[^0-9]/', '', $request->buyer['phone_number']);
//        $mobile = substr($phoneNumber, 3);

//         Find or create a user
//        $user = User::where('fin', $pin_code)
//            ->orWhere('email', $email)
//            ->orWhere('phone', $mobile)
//            ->firstOrFail();

        // Handle the package
        $package = $this->unitradeService->updatePackagetest($unitradePackageQuery, $request);
        $response = [
            'status' => true,
            'message' => "Package successfully updated!",
            'data' => $package
        ];

        return response()->json($response, 201);
    }

    public function etgb($parcel_id)
    {
        return response()->json([
            "status" => false,
            "message" => "API is not ready for prod yet!",
            "data" => []
        ], 400);
        //Log::channel('trendyol')->info("ParcelEtgb: ", [$parcel_id]);
        $scgoods = ScGood::where('tracking_no', $parcel_id)->first();
        if (!$scgoods || !$scgoods->reg_number) {
            $responseJson = [
                "code" => 400,
                "msg" => "Parcel is not declared yet."
            ];
            return Response::json($responseJson, 400);
        }

        $responseJson = [
            "code" => 200,
            "data" => [
                "etgb_no" => $scgoods->reg_number,
                "etgb_date" => strtotime($scgoods->insert_date),
                "parcel_id" => $parcel_id
            ]
        ];

        return Response::json($responseJson, 400);
    }

    public function delete($partnerCompany, $trackingCode)
    {
        $track = Track::query()->where('tracking_code', $trackingCode)->first();
        if (!$track) {
            return response()->json([
                "code" => 404,
                "message" => "Package not found",
                "data" => []
            ]);
        }

        $track = Track::query()->where('tracking_code', $trackingCode)->first();
        $track->status = 19;
        $track->save();

//        Track::query()->where('tracking_code', $trackingCode)->delete();

        return response()->json([
            "code" => 200,
            "message" => "Package deleted from system",
            "data" => []
        ]);
    }
}
