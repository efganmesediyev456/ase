<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests;
use App\Models\Package;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Request;
use Response;
use Validator;

/**
 * Class ExtraController
 *
 * @package App\Http\Controllers\Api
 */
class ExtraController extends Controller
{
    /**
     * @return JsonResponse
     */
    public function user()
    {
        //SDf3459s@34sfd
        $validator = Validator::make(Request::all(), [
            'code' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $user = User::where('customer_id', Request::get('code'))->first();

        if ($user) {
            return Response::json([
                'user' => [
                    'id' => $user->id,
                    'full_name' => $user->full_name,
                    'code' => $user->customer_id,
                    'address' => $user->address,
                    'phone' => $user->phone,
                    'passport' => $user->passport,
                ],
            ]);
        } else {
            return Response::json(['errors' => 'Member not found!'], 400);
        }
    }

    /**
     * @return JsonResponse
     */
    public function send()
    {
        $validator = Validator::make(Request::all(), [
            'tracking_code' => 'required_without:ase_package_id|string|min:3|exists:packages,tracking_code',
            'ase_package_id' => 'required_without:tracking_code|string|exists:packages,custom_id',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        if (Request::get('tracking_code') != null) {
            $key = 'tracking_code';
            $value = Request::get('tracking_code');
        } else {
            $key = 'custom_id';
            $value = Request::get('ase_package_id');
        }
        $package = Package::where($key, $value)->first();

        if ($package->warehouse_id != Request::get('warehouse')) {
            return response()->json(['errors' => "The package doesn't belong to your warehouse"], 400);
        }

        if (!$package->status) {
            $package->status = 1;
            $package->save();

            return response()->json(['message' => "The package was sent"]);
        }

        return response()->json(['errors' => "The package has already sent"], 400);
    }

    /**
     * @return JsonResponse
     */
    public function add()
    {
        $validator = Validator::make(Request::all(), [
            'tracking_code' => 'nullable|string|min:3',
            'user_id' => 'required_without:code|integer|min:1|exists:users,id',
            'code' => 'required_without:user_id|string|exists:users,customer_id',
            'weight' => 'required|numeric|min:0.1',
            'measurement_unit' => 'nullable|string|in:' . implode(',', config('ase.attributes.weight')), //default kg
            'number_item' => 'nullable|integer|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'length' => 'nullable|numeric|min:0',
            'length_unit' => 'nullable|string|in:' . implode(',', config('ase.attributes.length')),
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        if (!Request::get('user_id') != null) {
            $user_id = (User::where('customer_id', Request::get('code'))->first())->id;
        } else {
            $user_id = Request::get('user_id');
        }

        $package = new Package();

        $package->warehouse_id = Request::get('warehouse');
        $package->user_id = $user_id;
        $package->tracking_code = Request::get('tracking_code');
        $package->weight = Request::get('weight');
        $package->width = Request::get('with');
        $package->height = Request::get('height');
        $package->length = Request::get('length');
        $package->weight_type = array_search(Request::get('measurement_unit') ?: 'kg', config('ase.attributes.weight'));
        $package->length_type = array_search(Request::get('length_unit') ?: 'cm', config('ase.attributes.length'));

        $package->save();

        return response()->json([
            'ase_package_id' => $package->custom_id,
            'tracking_code' => $package->tracking_code,
        ]);
    }

    public function track($code)
    {
        $package = Package::whereCustomId($code)->first();

        if (!$package) {
            return response()->json([
                'ase_code' => $code,
                'error' => "Could not find a package!",
            ], 404);
        } else {
            $data = [
                'id' => $package->id,
                'ase_code' => $code,
                'tracking_code' => $package->tracking_code,
                'status' => $package->status_label,
                'paid' => boolval($package->paid),

            ];

            if ($package->warehouse) {
                $data['warehouse'] = [
                    'name' => $package->warehouse->company_name,
                    'country' => $package->warehouse->country->name,
                ];
            }

            return response()->json($data, 200);
        }
    }
}
