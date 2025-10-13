<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Courier;
use App\Models\Customer;
use App\Models\Filial;
use App\Models\Package;
use App\Models\Track;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InideskController extends Controller
{

    private function checkTokenStatus($token)
    {
        $valid_tokens = [
            '8e98LCuw3uOuzLPoYeU9MC9GdkWMmA3S7IlqML9oIsiYKF5wPSTe3xXtFClwWVKM',
        ];

        return in_array($token, $valid_tokens);
    }

    public function getDatas(Request $request)
    {
        try {
            $header_token = $request->header("auth-token");

            if (!$this->checkTokenStatus($header_token)) {
                return response()->json(["message" => "Unauthorized"], 401);
            }

            $reference_ids = $request->get('reference_ids');;
            $result_type = $request->get('result_type', 'status');
            $reference_type = $request->get('reference_type', 'id');
            $searchTerm = $request->get('region_name', null);
            $result = [];

            $locations = Filial::where('is_active', 1)
                ->get();

//        $locations_data = [];
//        foreach ($locations as $location){
//            $locations_data[] =[
//                'id' => $location->id,
//                'name' => $location->name,
//                'address' => $location->address,
//                'latitude' => $location->latitude,
//                'longitude' => $location->longitude,
//                'contact_phone' => $location->contact_phone,
//                'work_time' => $location->work_time,
//            ];
//        }

            if ($reference_type == 'fin' || $reference_type == 'customer_code') {
                $user = User::where(function ($query) use ($reference_ids) {
                    $query->where('fin', $reference_ids)
                        ->orWhere('customer_id', $reference_ids);
                })
                    ->first();


                if (!$user && $reference_type == 'fin') {
                    $customer = Customer::where(function ($query) use ($reference_ids) {
                        $query->where('fin', $reference_ids);
                    })
                        ->first();
                }

                if (!$user && !$customer) {
                    return response()->json(["status" => "OK", "message" => "Success", "user" => "Müştəri məlumatı sistemimizdə tapılmadı. Zəhmət olmasa, müştəri FİN kodunuzu/əlaqə məlumatlarını yenidən yoxlayın və təkrar cəhd edin.", "packages" => "", "pin_url" => ""], 404);
                }

                if ($customer && $customer->fullname) {
                    $fullname = $customer->fullname;
                    $parts = explode(' ', trim($fullname));

                    $first_name = isset($parts[1]) ? $parts[1] : '';
                    $last_name = isset($parts[0]) ? $parts[0] : '';
                }

                $user_datas = [
                    'id' => $user ? $user->id : $customer->id,
                    'first_name' => $user ? $user->name : $first_name,
                    'last_name' => $user ? $user->surname : $last_name,
                    'email' => $user ? $user->email : $customer->email,
                ];

                $packages = [];

                if ($user) {
                    $user_packages = Package::where('user_id', $user->id)
                        ->whereNotIn('status', [3])
                        ->whereNull('deleted_at')
                        ->orderBy('id', 'desc')
                        ->limit(3)
                        ->get();

                    $user_datas = [
                        'id' => $user->id,
                        'first_name' => $user->name,
                        'last_name' => $user->surname,
                        'email' => $user->email,
                    ];

                    if (!$user_packages) {
                        $result = [
                            'status' => 'OK',
                            'message' => 'Success',
                            'user' => $user_datas,
                            'packages' => "Xahiş edirik, soruşmaq istədiyiniz bağlamanı müəyyən etmək üçün fin kodunu təqdim edin.",
                            'pin_url' => ""
                        ];

                        return response()->json($result, 200);
                    }

                    foreach ($user_packages as $package) {
//                    $filialDetails = $package->filial_details;
//                    $locations_data = [];
//                    if ($filialDetails) {
//                        if (preg_match('/^(.*)_\((.*?)\)$/', $filialDetails, $matches)) {
//                            $firstPart = trim($matches[1]);
//                            $firstPart = json_decode($firstPart, true);
//                            $lastPart = trim($matches[2]);
//                        }
//                        $locations = Filial::where('type', $lastPart)->where('fid', $firstPart['id'])->first();
//
//                        if ($locations) {
//                            $locations_data = [
//                                'id' => $locations->id,
//                                'name_az' => $locations->name,
//                                'address_az' => $locations->address,
//                                'phone' => $locations->contact_phone,
//                                'work_hours' => $locations->work_time,
//                                'lunch_hour' => $locations->lunch_time,
//                                'location_link' => $locations->location_url,
//                                'company' => "ASE Şirkəti",
//                            ];
//                        }
//                    }

                        $location = '';
                        $country = '';
                        $packages[] = [
                            "country" => $package->warehouse->country->name,
                            "type" => $package->warehouse->country->name,
                            "id" => $package->id,
                            "track_number" => $package->custom_id,
                            "purchase_code" => $package->tracking_code,
                            "declaration_number" => null,
                            "delivery_price" => $package->delivery_price_azn,
                            "weight" => $package->weight,
                            "location" => null,
                            "courier" => null,
                            "waybill_url" => null,
                            "current_status" => $package->status_label,
                            "note" => null,
                            "pin_url" => null,
                            "customs_on_hold_message" => null,
                            "adress" => null,
                        ];
                    }
                } elseif ($customer) {
                    $user_packages = Track::where('customer_id', $customer->id)
                        ->whereNotIn('status', [17])
                        ->whereNull('deleted_at')
                        ->orderBy('id', 'desc')
                        ->limit(3)
                        ->get();

                    $fullname = $customer->fullname;
                    $parts = explode(' ', trim($fullname));

                    $first_name = isset($parts[1]) ? $parts[1] : '';
                    $last_name = isset($parts[0]) ? $parts[0] : '';

                    $user_datas = [
                        'id' => $customer->id,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $customer->email,
                    ];

                    if (!$user_packages) {
                        $result = [
                            'status' => 'OK',
                            'message' => 'Success',
                            'user' => $user_datas,
                            'packages' => "Xahiş edirik, soruşmaq istədiyiniz bağlamanı müəyyən etmək üçün fin kodunu təqdim edin.",
                            'pin_url' => ""
                        ];

                        return response()->json($result, 200);
                    }

                    foreach ($user_packages as $package) {
//                    $filialDetails = $package->filial_details;
//                    $locations_data = [];
//                    if ($filialDetails) {
//                        if (preg_match('/^(.*)_\((.*?)\)$/', $filialDetails, $matches)) {
//                            $firstPart = trim($matches[1]);
//                            $firstPart = json_decode($firstPart, true);
//                            $lastPart = trim($matches[2]);
//                        }
//                        $locations = Filial::where('type', $lastPart)->where('fid', $firstPart['id'])->first();
//
//                        if ($locations) {
//                            $locations_data = [
//                                'id' => $locations->id,
//                                'name' => $locations->name,
//                                'address' => $locations->address,
//                                'latitude' => $locations->latitude,
//                                'longitude' => $locations->longitude,
//                                'contact_phone' => $locations->contact_phone,
//                                'work_time' => $locations->work_time,
//                            ];
//                        }
//                    }

                        $location = '';
                        $country = '';
                        $packages[] = [
                            "country" => $package->partner_with_label,
                            "type" => $package->partner_with_label,
                            "id" => $package->id,
                            "track_number" => $package->tracking_code,
                            "purchase_code" => $package->second_tracking_code ? $package->second_tracking_code : $package->tracking_code,
                            "declaration_number" => null,
                            "delivery_price" => in_array($package->partner_id, [1, 3]) ? "$package->partner_with_label packages are delivered to Azerbaijan for free" : $package->delivery_price,
                            "weight" => in_array($package->partner_id, [1, 3]) ? "$package->partner_with_label packages are delivered to Azerbaijan for free and there is no need for weight" : $package->weight,
                            "location" => null,
                            "courier" => null,
                            "waybill_url" => null,
                            "current_status" => $package->status_with_label,
                            "note" => null,
                            "pin_url" => null,
                            "customs_on_hold_message" => null,
                            "adress" => null,
                        ];
                    }
                }
                $result = [
                    'status' => 'OK',
                    'message' => 'Success',
                    'user' => $user_datas,
                    'packages' => $packages,
                    'pin_url' => ""
                ];

                return response()->json($result, 200);
            } elseif ($reference_type == 'tracking_number') {
                $user = [];
                $packages = [];
                $location = '';

                $package = Package::where(function ($query) use ($reference_ids) {
                    $query->where('custom_id', $reference_ids)
                        ->orWhere('tracking_code', $reference_ids);
                })
                    ->first();

                $packageType = $package instanceof Package ? 'package' : 'track';

                if ($packageType == 'package') {

                    if (!$package) {
                        return response()->json(["status" => "OK", "message" => "Success", "user" => null, "packages" => "Göstərilən izləmə nömrəsi ilə bağlama sistemimizdə mövcud deyil. Zəhmət olmasa, izləmə kodunuzun düzgünlüyünü yoxlayın.", "pin_url" => ""], 404);
                    }

                    $user = User::where('id', $package->user_id)
                        ->first();

                    $user_datas = [
                        'id' => $user->id,
                        'first_name' => $user->name,
                        'last_name' => $user->surname,
                        'email' => $user->email,
                    ];

                    $locations_data = [];

                    $filialDetails = $package->filial_details;

                    if ($filialDetails) {
                        if (preg_match('/^(.*)_\((.*?)\)$/', $filialDetails, $matches)) {
                            $firstPart = trim($matches[1]);
                            $firstPart = json_decode($firstPart, true);
                            $lastPart = trim($matches[2]);
                        }
                        $locations = Filial::where('type', $lastPart)->where('fid', $firstPart['id'])->where('is_active', 1)->first();
                        if ($locations) {
                            $locations_data = [
                                'id' => $locations->id,
                                'name_az' => $locations->name,
                                'address_az' => $locations->address,
                                'phone' => $locations->contact_phone,
                                'work_hours' => $locations->work_time,
                                'lunch_hour' => $locations->lunch_time,
                                'location_link' => $locations->location_url,
                                'company' => "ASE Şirkəti",
                            ];
                        }
                    }

                    $packages = [
                        "country" => $package->warehouse->country->name,
                        "type" => $package->warehouse->country->name,
                        "id" => $package->id,
                        "track_number" => $package->custom_id,
                        "purchase_code" => $package->tracking_code,
                        "declaration_number" => null,
                        "delivery_price" => $package->delivery_price_azn,
                        "debt_price" => $package->debt_price > 0 && $package->paid_debt == 0 ? $package->debt_price : 0,
                        "debt_price_link" => null,
                        "weight" => $package->weight,
                        "location" => in_array($package->status, [2, 8]) ? $locations_data : null,
                        "courier" => null,
                        "waybill_url" => $package->status == 4 && $package->paid == 0 ? "https://admin.aseshop.az/packages/$package->id/label" : 0,
                        "current_status" => $package->status_label,
                        "note" => null,
                        "pin_url" => null,
                        "customs_on_hold_message" => null,
                        "adress" => null,
                    ];
                } else {
                    $package = Track::where(function ($query) use ($reference_ids) {
                        $query->where('tracking_code', $reference_ids);
                    })
                        ->first();

                    if (!$package) {
                        return response()->json(["status" => "OK", "message" => "Success", "user" => null, "packages" => "Göstərilən izləmə nömrəsi ilə bağlama sistemimizdə mövcud deyil. Zəhmət olmasa, izləmə kodunuzun düzgünlüyünü yoxlayın.", "pin_url" => ""], 404);
                    }

                    $user = Customer::where('id', $package->customer_id)
                        ->first();

                    $fullname = $user->fullname;
                    $parts = explode(' ', trim($fullname));

                    $first_name = isset($parts[1]) ? $parts[1] : '';
                    $last_name = isset($parts[0]) ? $parts[0] : '';

                    $user_datas = [
                        'id' => $user->id,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $user->email,
                    ];
                    $locations_data = [];

                    $filialDetails = $package->filial_details;
                    if ($filialDetails) {
                        if (preg_match('/^(.*)_\((.*?)\)$/', $filialDetails, $matches)) {
                            $firstPart = trim($matches[1]);
                            $firstPart = json_decode($firstPart, true);
                            $lastPart = trim($matches[2]);
                        }
                        $locations = Filial::where('type', $lastPart)->where('fid', $firstPart['id'])->where('is_active', 1)->first();
                        if ($locations) {
                            if(!in_array($locations->id, [1, 2]) && !in_array($package->partner_id, [1, 3]) && $package->paid == 0){
                                $locations_data = [];
                            }else{
                                $locations_data = [
                                    'id' => $locations->id,
                                    'name_az' => $locations->name,
                                    'address_az' => $locations->address,
                                    'phone' => $locations->contact_phone,
                                    'work_hours' => $locations->work_time,
                                    'lunch_hour' => $locations->lunch_time,
                                    'location_link' => $locations->location_url,
                                    'company' => "ASE Şirkəti",
                                ];
                            }
                        }
                    }
                    $courier_datas = null;
                    if ($package->courier_delivery_id && $package->courier_delivery) {
                        $courier = Courier::where('id', $package->courier_delivery->courier_id)->first();
                        $courier_datas = [
                            "id" => $courier->id,
                            "name" => $courier->name,
                            "surname" => null,
                            "phone" => $courier->phone,
                        ];
                    }

                    $packages = [
                        "country" => $package->partner_with_label,
                        "type" => $package->partner_with_label,
                        "id" => $package->id,
                        "track_number" => $package->tracking_code,
                        "purchase_code" => $package->second_tracking_code ? $package->second_tracking_code : $package->tracking_code,
                        "declaration_number" => null,
                        "delivery_price" => in_array($package->partner_id, [1, 3]) ? "$package->partner_with_label packages are delivered to Azerbaijan for free" : $package->delivery_price,
                        "debt_price" => $package->debt_price > 0 && $package->paid_debt == 0 ? $package->debt_price : 0,
                        "debt_price_link" => $package->debt_price > 0 && $package->paid_debt == 0 ? "https://aseshop.az/track/pay/debt/$package->custom_id" : null,
                        "weight" => in_array($package->partner_id, [1, 3]) ? "$package->partner_with_label packages are delivered to Azerbaijan for free and there is no need for weight" : $package->weight,
                        "location" => in_array($package->status, [16, 20]) ? $locations_data : null,
                        "courier" => $courier_datas,
                        "waybill_url" => $package->status == 18 ? "https://admin.aseshop.az/tracks/$package->id/label" : null,
                        "pay_url" => $package->paid == 0 && !in_array($package->partner_id, [1, 3]) ? "https://aseshop.az/track/pay/$package->custom_id" : null,
                        "current_status" => $package->status_with_label,
                        "note" => null,
                        "pin_url" => null,
                        "customs_on_hold_message" => null,
                        "adress" => "",
                    ];

                }

                $result = [
                    'status' => 'OK',
                    'message' => 'Success',
                    'user' => $user_datas,
                    'packages' => $packages,
                ];

                return response()->json($result, 200);
            } elseif ($reference_type == 'regions') {

                $locations = Filial::where('is_active', 1)
                    ->where(function ($query) use ($searchTerm) {
                            $normalizedSearch = $this->normalizeAzerbaijaniChars(strtolower($searchTerm));
                            $query->where(function($q) use ($normalizedSearch) {
                                $q->whereRaw('LOWER(normalize_azerbaijani_chars(name)) COLLATE utf8mb4_unicode_ci like ?', ['%' . $normalizedSearch . '%'])
                                    ->orWhereRaw('LOWER(normalize_azerbaijani_chars(address)) COLLATE utf8mb4_unicode_ci like ?', ['%' . $normalizedSearch . '%']);
                            });
                    })
                    ->get();

                $locations_data = [];
//                if ($locations->count() > 3 && $locations->count() <= 7) {
                    foreach ($locations as $location) {
                        $locations_data[] = [
                            'name_az' => $location->name,
                            'address_az' => $location->address,
                            'work_hours' => $location->work_time,
                            'location_link' => $location->location_url,
                            'phone' => $location->contact_phone,
                            'company' => "ASE Şirkəti",
                        ];
                    }
//                } elseif ($locations->count() > 7) {
//                    $locations = ["There are several branches in this area. Please specify which branch you're inquiring about."];
//                }


                $result = [
                    'status' => 'OK',
                    'message' => 'Success',
                    'user' => [],
                    'packages' => [],
                    'locations' => count($locations) > 0 ? $locations_data : 'Region is not found'
                ];

                return response()->json($result);
            }
        } catch (\Exception $exception) {
            dd($exception->getMessage(), $exception->getLine());
        }
    }

    protected function normalizeAzerbaijaniChars($string)
    {
        $replacements = [
            'ə' => 'e',
            'ü' => 'u',
            'ö' => 'o',
            'ğ' => 'g',
            'ç' => 'c',
            'ş' => 's',
            'ı' => 'i',
            // Add more replacements as needed
        ];

        return strtr(mb_strtolower($string), $replacements);
    }
}
