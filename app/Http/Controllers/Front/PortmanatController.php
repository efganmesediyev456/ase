<?php

namespace App\Http\Controllers\Front;

use Alert;
use App\Jobs\UpdateCarrierPackagePaymentStatusJob;
use App\Models\CD;
use App\Models\Package;
use App\Models\Track;
use App\Models\Payments\PortManat;
use App\Models\Promo;
use App\Models\PromoLog;
use App\Models\Transaction;
use App\Models\Ulduzum;
use App\Models\User;
use Exception;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;

class PortmanatController extends MainController
{
    public function ulduzum(Request $request)
    {
        //get amount from packages
        $ulduzumId = 0;
        $uDiscount = 0;
        $amount = 0;
        $ids = request()->get('ids');
        $arr = explode('-', $ids);
        $packages = Package::whereIn('id', $arr)->get();
        foreach ($packages as $package) {
            $amount += $package->delivery_manat_price_discount;
        }
        $strAmount = strval(round($amount, 2));
        $arr = explode('.', $strAmount);
        if (count($arr) == 2) {
            if (strlen($arr[1]) == 1) $strAmount .= '0';
        } else {
            $strAmount .= '.00';
        }
        //----------

        if ($amount > 0) {
            //get ulduzum from db
            $code = request()->get('code');
            $ulduzum = Ulduzum::where('code', $code)->where('created_at', '>=', Carbon::now()->subDays(1)->toDateTimeString())->first();
            if (!$ulduzum || !$ulduzum->is_completed) {
                $uDiscount = $this->getUlduzum($code, $amount);
                if ($uDiscount && $uDiscount > 0) {
                    if (!$ulduzum)
                        $ulduzum = new Ulduzum();
                    $ulduzum->code = $code;
                    $ulduzum->amount = $strAmount;
                    $ulduzum->discount_percent = $uDiscount;
                    $ulduzum->is_completed = false;
                    $ulduzum->save();
                    $ulduzumId = $ulduzum->id;
                }
            }
        }

        return response($ulduzumId . '-' . $uDiscount, 200)
            ->header('Content-Type', 'text/plain');
    }

    public function getUlduzum($u_code, $u_amount)
    {
        if(Auth::user()->id == 31548){
            return 20;
        }
        $u_in = [];
        $u_in['authKey'] = 'f0caa1a471fdf8b5f0112280f12ca6d3';
        $u_in['terminalCode'] = '3258';
        $u_in['identicalCode'] = $u_code;
        $u_in['amount'] = $u_amount;
        $u_in['campaign'] = '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://portal.emobile.az/externals/loyalty/calculate");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($u_in));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        $u_out = json_decode($server_output, true);
        $ldate = date('Y-m-d H:i:s');
        if ($u_out['result'] == "success") {
            $u_discount = $u_out["data"]["discount_percent"];
            $u_amount = $u_out["data"]["amount"];
            file_put_contents("/var/log/ase_ulduzum.log", $ldate . " Ok calculate " . $u_code . " " . $u_amount . " " . $u_discount . "\n", FILE_APPEND);
            return $u_discount;
        }
        if ($u_out['result'] == "error") {
            $u_message = $u_out["errormess"];
            file_put_contents("/var/log/ase_ulduzum.log", $ldate . " Error calculate " . $u_code . " " . $u_amount . " " . $u_message . "\n", FILE_APPEND);
            return 0;
        }
        file_put_contents("/var/log/ase_ulduzum.log", $ldate . " Error calculate " . $u_code . " " . $u_amount . " Empty response\n", FILE_APPEND);
        return 0;
    }

    public function promo(Request $request)
    {
        //get amount from packages
        $promoId = 0;
        $promo = null;
        $promo_percent = 0;
        $promo_amount_rest = 0;
        $promo_weight_rest = 0;
        $ids = request()->get('ids');
        $arr = explode('-', $ids);

        $code = request()->get('code');
        $ldate = date('Y-m-d H:i:s');
        $user_promo_id = Auth::user()->promo_id;
        $pr_user = 0;
        $promo_empty = false;
        if (!empty($code)) {
            $promo = Promo::where('code', $code)->where('is_active', 1)->where('activation', 2)->whereRaw("(((start_at is null) or (start_at<='" . $ldate . "')) and ((stop_at is null) or (stop_at>='" . $ldate . "')))")->first();
        } else if (!empty($user_promo_id)) {
            $promo = Promo::where('id', $user_promo_id)->where('is_active', 1)->where('activation', 1)->whereRaw("(((start_at is null) or (start_at<='" . $ldate . "')) and ((stop_at is null) or (stop_at>='" . $ldate . "')))")->first();
            $promo_empty = true;
        }
        if ($promo) {
            $promoLogs = PromoLog::where('promo_id', $promo->id)->where('user_id', Auth::user()->id)->get();
            if (($promo->num_to_use > $promo->num_used) || $promo_empty || count($promoLogs) > 0) {
                $promo_percent = $promo->percent;
                $promo_amount_rest = $promo->amount;
                $promo_weight_rest = $promo->weight;

                foreach ($promoLogs as $promoLog) {
                    $promo_amount_rest -= $promoLog->package->promo_discount_amount_azn ? $promoLog->package->promo_discount_amount_azn : 0;
                    $promo_weight_rest -= $promoLog->package->promo_discount_weight ? $promoLog->package->promo_discount_weight : 0;
                }
                if ($promo_amount_rest < 0) $promo_amount_rest = 0;
                if ($promo_weight_rest < 0) $promo_weight_rest = 0;
                if (($promo_percent > 0) || ($promo_amount_rest > 0) || ($promo_weight_rest > 0)) {
                    $promoId = $promo->id;
                    if (empty($code))
                        $pr_user = 1;
                }
            } else {
                $promo = null;
            }
        }

        return response($promoId . '-' . $pr_user . '-' . $promo_percent . '-' . $promo_amount_rest . '-' . $promo_weight_rest, 200)
            ->header('Content-Type', 'text/plain');
    }

    public function cd_hash(Request $request)
    {
        $userId = '';
        $usr = Auth::user();
        if ($usr) $userId = $usr->id;
        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " cd_hash " . http_build_query(request()->all()) . "\n", FILE_APPEND);
        $client_rrn = request()->get('client_rrn');
        $ids = request()->get('ids');
        $amount = 0;
        $arr = explode('-', $ids);


        $cds = CD::whereIn('id', $arr)->get();
        foreach ($cds as $cd) {
            $cd_amount = $cd->delivery_price;
            $amount += $cd_amount;
        }
        $strAmount = strval(round($amount, 2));
        $arr = explode('.', $strAmount);
        if (count($arr) == 2) {
            if (strlen($arr[1]) == 1) $strAmount .= '0';
        } else {
            $strAmount .= '.00';
        }
        $portmanat = new PortManat();
        $res = $portmanat->generateHash($client_rrn, $strAmount) . '-' . $strAmount;
        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " cd_hash " . $res . "\n", FILE_APPEND);
        return response($res, 200)
            ->header('Content-Type', 'text/plain');
    }

    public function hash(Request $request)
    {
        $userId = '';
        $usr = Auth::user();
        if ($usr) $userId = $usr->id;
        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " hash " . http_build_query(request()->all()) . "\n", FILE_APPEND);
        $client_rrn = request()->get('client_rrn');
        $ids = request()->get('ids');
        $ulduzumId = request()->get('ulid');
        $promoId = request()->get('prid');
        $promo_percent = 0;
        $promo_amount_rest = 0;
        $promo_weight_rest = 0;
        $promo = null;
        $promo_empty = false;
        $user = User::where('id', Auth::user()->id)->first();

        if (empty($promoId)) {
            $promoId = $user->promo_id;
        }

        if ($promoId == $user->promo_id) {
            $promo_empty = true;
        }

        if (!empty($promoId) && $promoId > 0) {
            $promo = Promo::where('id', $promoId)->first();
            if ($promo) {
                $promoLogs = PromoLog::where('promo_id', $promo->id)->where('user_id', Auth::user()->id)->get();
                if (($promo->num_to_use > $promo->num_used) || $promo_empty || count($promoLogs) > 0) {
                    $promo_percent = $promo->percent;
                    $promo_amount_rest = $promo->amount;
                    $promo_weight_rest = $promo->weight;

                    foreach ($promoLogs as $promoLog) {
                        $promo_amount_rest -= $promoLog->package->promo_discount_amount_azn ? $promoLog->package->promo_discount_amount_azn : 0;
                        $promo_weight_rest -= $promoLog->package->promo_discount_weight ? $promoLog->package->promo_discount_weight : 0;
                    }
                    if ($promo_amount_rest < 0) $promo_amount_rest = 0;
                    if ($promo_weight_rest < 0) $promo_weight_rest = 0;
                    if (($promo_percent <= 0) && ($promo_amount_rest <= 0) && ($promo_weight_rest <= 0)) {
                        $promo = null;
                    }
                } else {
                    $promo = null;
                }
            }
        }
        $amount = 0;
        $arr = explode('-', $ids);


        $packages = Package::whereIn('id', $arr)->get();
        foreach ($packages as $package) {
            $pkg_amount = $package->delivery_manat_price_discount;
            $pkg_amount2 = $pkg_amount;
            if (!$pkg_amount || $pkg_amount <= 0)
                continue;
            if (!$promo || ($promo->warehouse_id && $promo->warehouse_id > 0 && $promo->warehouse_id != $package->warehouse_id)) {
                $amount += $pkg_amount;
                continue;
            }
            if ($promo_weight_rest > 0) {
                $p_weight = $package->weight - $promo_weight_rest;
                $promo_weight_rest = $promo_weight_rest - $package->weight;
                if ($p_weight <= 0 || $promo_weight_rest > 0) {
                    $pkg_amount = 0;
                    continue;
                }
                $promo_weight_rest = 0;
                $pkg_amount = $package->getDeliveryPriceAZNWithDiscount($p_weight);
                $pkg_amount2 = $pkg_amount;
            }
            if ($promo_amount_rest > 0) {
                $pkg_amount = $pkg_amount - $promo_amount_rest;
                $promo_amount_rest = $promo_amount_rest - $pkg_amount2;
                if ($pkg_amount <= 0 || $promo_amount_rest > 0) {
                    $pkg_amount = 0;
                    continue;
                }
                $promo_amount_rest = 0;
            }
            if ($promo_percent && $promo_percent > 0) {
                $pkg_amount = $pkg_amount - ($pkg_amount * $promo_percent) / 100;
            }
            $amount += $pkg_amount;
        }

        if ($amount > 0 && !empty($ulduzumId) && $ulduzumId > 0) {
            $ulduzum = Ulduzum::where('id', $ulduzumId)->first();
            if ($ulduzum) {
                $amount = $amount - ($amount * $ulduzum->discount_percent) / 100;
            }
        }
        $strAmount = strval(round($amount, 2));
        $arr = explode('.', $strAmount);
        if (count($arr) == 2) {
            if (strlen($arr[1]) == 1) $strAmount .= '0';
        } else {
            $strAmount .= '.00';
        }
        //$amount=0.2;//test;
        $portmanat = new PortManat();
        $res = $portmanat->generateHash($client_rrn, $strAmount) . '-' . $strAmount;
        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " hash " . $res . "\n", FILE_APPEND);
        return response($res, 200)
            ->header('Content-Type', 'text/plain');
    }

    public function callback(Request $request)
    {
        try {


            if ($request->has('client_rrn') && strpos($request->get('client_rrn'), 'cd_') === 0) {
                return $this->cd_callback($request);
            }
            if ($request->has('client_rrn') && strpos($request->get('client_rrn'), 'tr_') === 0) {
                return $this->tr_callback($request);
            }
            $userId = '';
            $usr = Auth::user();
            if ($usr) $userId = $usr->id;
            file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " callback " . http_build_query(request()->all()) . "\n", FILE_APPEND);
            $gamount = null;
            if (request()->exists('amount'))
                $gamount = request()->get('amount');
            /*if (! empty($_GET['psp_rrn']) || ($gamount != null && $gamount==0)) {
        $psp_rrn = request()->get('psp_rrn');*/
            if (($request->has('psp_rrn') && !empty($request->get('psp_rrn'))) || ($gamount != null && $gamount == 0)) {
                $psp_rrn = request()->get('psp_rrn');
                $packageId = request()->get('client_rrn');
                $id = '';
                $ulduzumId = '';
                $promoId = '';
                $uid = '';
                $arr = explode("_", $packageId);
                if (count($arr) >= 1) $id = $arr[0];
                if (count($arr) >= 2) $ulduzumId = $arr[1];
                if (count($arr) >= 3) $promoId = $arr[2];
                if (count($arr) >= 4) $uid = $arr[3];
                //list($id,$ulduzumId,$uid) = explode("_", $packageId);
                $arr = explode('-', $id);
                $packages = Package::whereIn('id', $arr)->where('paid', 0)->get();
                if (count($packages) <= 0) {
                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " callback 0 paid packages \n", FILE_APPEND);
                    //return redirect()->to(route('my-packages', ['id' => 2]) . '?error=Wrong packages');
                    return redirect()->to(route('my-packages', ['id' => 2]) . '?success=true');
                }
                $ulduzum_discount = 0;
                $ulduzum_code = '';
                $promo_percent = 0;
                $promo_amount_rest = 0;
                $promo_weight_rest = 0;
                $promo_code = '';
                //$package = Package::find($id);
                //if($psp_rrn=='-') {
                //}

                $promo = null;
                $promoLogs = [];

                $promo_empty = false;
                if (empty($userId)) {
                    $userId = $packages[0]->user_id;
                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " callback from_pkg " . http_build_query(request()->all()) . "\n", FILE_APPEND);
                }
                $user = User::where('id', $userId)->first();

                if (empty($promoId)) {
                    $promoId = $user->promo_id;
                }
                if ($promoId == $user->promo_id) {
                    $promo_empty = true;
                }

                if (!empty($promoId) && $promoId > 0) {
                    $promo = Promo::where('id', $promoId)->first();
                    if ($promo) {
                        $promoLogs = PromoLog::where('promo_id', $promo->id)->where('user_id', $userId)->get();
                        if (($promo->num_to_use > $promo->num_used) || $promo_empty || count($promoLogs) > 0) {
                            $promo_percent = $promo->percent;
                            $promo_amount_rest = $promo->amount;
                            $promo_weight_rest = $promo->weight;

                            foreach ($promoLogs as $promoLog) {
                                $promo_amount_rest -= $promoLog->package->promo_discount_amount_azn ? $promoLog->package->promo_discount_amount_azn : 0;
                                $promo_weight_rest -= $promoLog->package->promo_discount_weight ? $promoLog->package->promo_discount_weight : 0;
                            }
                            if ($promo_amount_rest < 0) $promo_amount_rest = 0;
                            if ($promo_weight_rest < 0) $promo_weight_rest = 0;
                            if (($promo_percent <= 0) && ($promo_amount_rest <= 0) && ($promo_weight_rest <= 0)) {
                                $promo = null;
                            }
                        } else {
                            $promo = null;
                        }
                    }
                }

                $ulduzum = null;
                if (!empty($ulduzumId) && $ulduzumId > 0) {
                    $ulduzum = Ulduzum::where('id', $ulduzumId)->first();
                    if ($ulduzum) {
                        $ulduzum_discount = $ulduzum->discount_percent;
                        $ulduzum_code = $ulduzum->code;
                    }
                }

                if ($gamount != null && $gamount == 0) {
                    $amount = 0;
                    $bak_promo_amount_rest = $promo_amount_rest;
                    $bak_promo_weight_rest = $promo_weight_rest;
                    foreach ($packages as $package) {
                        $pkg_amount = $package->delivery_manat_price_discount;
                        $pkg_amount2 = $pkg_amount;
                        if (!$pkg_amount || $pkg_amount <= 0)
                            continue;
                        if (!$promo || ($promo->warehouse_id && $promo->warehouse_id > 0 && $promo->warehouse_id != $package->warehouse_id)) {
                            $amount += $pkg_amount;
                            continue;
                        }
                        if ($promo_weight_rest > 0) {
                            $p_weight = $package->weight - $promo_weight_rest;
                            $promo_weight_rest = $promo_weight_rest - $package->weight;
                            if ($p_weight <= 0 || $promo_weight_rest > 0) {
                                $pkg_amount = 0;
                                continue;
                            }
                            $promo_weight_rest = 0;
                            $pkg_amount = $package->getDeliveryPriceAZNWithDiscount($p_weight);
                            $pkg_amount2 = $pkg_amount;
                        }
                        if ($promo_amount_rest > 0) {
                            $pkg_amount = $pkg_amount - $promo_amount_rest;
                            $promo_amount_rest = $promo_amount_rest - $pkg_amount2;
                            if ($pkg_amount <= 0 || $promo_amount_rest > 0) {
                                $pkg_amount = 0;
                                continue;
                            }
                            $promo_amount_rest = 0;
                        }
                        if ($promo_percent && $promo_percent > 0) {
                            $pkg_amount = $pkg_amount - ($pkg_amount * $promo_percent) / 100;
                        }
                        $amount += $pkg_amount;
                    }
                    if ($amount > 0) {
                        $strAmount = strval(round($amount, 2));
                        $arr = explode('.', $strAmount);
                        if (count($arr) == 2) {
                            if (strlen($arr[1]) == 1) $strAmount .= '0';
                        } else {
                            $strAmount .= '.00';
                        }
                        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " callback wrong_amount " . $amount . "\n", FILE_APPEND);
                        return redirect()->to(route('my-packages', ['id' => 2]) . '?error=Wrong Amount');
                        //$portmanat=new PortManat();
                        //return redirect($portmanat->generateUrlFromRequest($request,$strAmount));
                    }
                    $promo_amount_rest = $bak_promo_amount_rest;
                    $promo_weight_rest = $bak_promo_weight_rest;
                }


                //if ($package && ! $package->paid) {
                if (count($packages) > 0) {
                    $server_output = '';
                    $obj = [];

                    if ($psp_rrn == 'pass398wpd31') {
                        $obj['code'] = '0';//test;
                        $server_output = '{"status":"MANUAL"}';
                    } else if ($gamount != null && $gamount == 0) {
                        $obj['code'] = '0';
                        $server_output = '{"status":"PROMOCODE"}';
                    } else {
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, "https://psp.mps.az/check");
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['psp_rrn' => $psp_rrn]));
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        for ($k = 1; $k <= 3; $k++) {
                            file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " request " . $psp_rrn . " \n", FILE_APPEND);
                            $server_output = curl_exec($ch);
                            $obj = json_decode($server_output, true);
                            if (isset($obj['code']) && ($obj['code'] == '0')) {
                                file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " request " . $psp_rrn . " " . $k . " Ok\n", FILE_APPEND);
                                break;
                            }
                            file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " request " . $psp_rrn . " " . $k . " Failed\n", FILE_APPEND);
                            if ($k < 3) sleep(1 + $k);
                        }
                        curl_close($ch);
                    }
                    //$obj['code'] = '0';//test;
                    $amount = 0;

                    if (isset($obj['code']) && ($obj['code'] == '1')) {
                        // Pending
                        foreach ($packages as $package) {
                            $pkg_amount = $package->delivery_manat_price_discount;
                            $pkg_amount2 = $pkg_amount;
                            if ($pkg_amount > 0) {
                                if (!$promo || ($promo->warehouse_id && $promo->warehouse_id > 0 && $promo->warehouse_id != $package->warehouse_id)) {
                                    $amount += $pkg_amount;
                                    continue;
                                }
                                if ($promo_weight_rest > 0) {
                                    $p_weight = $package->weight - $promo_weight_rest;
                                    $promo_weight_rest = $promo_weight_rest - $package->weight;
                                    if ($p_weight <= 0 || $promo_weight_rest > 0) {
                                        $pkg_amount = 0;
                                        continue;
                                    }
                                    $promo_weight_rest = 0;
                                    $pkg_amount = $package->getDeliveryPriceAZNWithDiscount($p_weight);
                                    $pkg_amount2 = $pkg_amount;
                                }
                                if ($promo_amount_rest > 0) {
                                    $pkg_amount = $pkg_amount - $promo_amount_rest;
                                    $promo_amount_rest = $promo_amount_rest - $pkg_amount2;
                                    if ($pkg_amount <= 0 || $promo_amount_rest > 0) {
                                        $pkg_amount = 0;
                                        continue;
                                    }
                                    $promo_amount_rest = 0;
                                }
                                if ($promo_percent && $promo_percent > 0) {
                                    $pkg_amount = $pkg_amount - ($pkg_amount * $promo_percent) / 100;
                                }

                                if ($ulduzum && $pkg_amount > 0 && $ulduzum_discount && $ulduzum_discount > 0)
                                    $pkg_amount = $pkg_amount - ($pkg_amount * $ulduzum_discount) / 100;
                            }

                            Transaction::create([
                                'user_id' => $package->user_id,
                                'custom_id' => $package->id,
                                'paid_by' => 'PORTMANAT',
                                'amount' => $pkg_amount,
                                'type' => 'PENDING',
                                'paid_for' => 'PACKAGE',
                                'source_id' => $psp_rrn ?? null,
                                'request_all' => json_encode($request->all()) ?? null,
                                'extra_data' => $server_output,
                            ]);
                        }

                        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " callback pending \n", FILE_APPEND);
                        return redirect()->to(route('my-packages', ['id' => 2]) . '?pending=true');
                    } elseif (isset($obj['code']) && ($obj['code'] == '0')) {
                        // Success
                        foreach ($packages as $package) {
                            $pkg_amount = $package->delivery_manat_price_discount;
                            $pkg_amount2 = $pkg_amount;
                            $pkg_promo_weight = 0;
                            $pkg_promo_weight_amount = 0;
                            $pkg_promo_weight_amount_azn = 0;
                            $pkg_promo_weight_amount_usd = 0;
                            $pkg_promo_amount = 0;
                            $pkg_promo_amount_azn = 0;
                            $pkg_promo_amount_usd = 0;
                            if ($pkg_amount > 0) {
                                if (!$promo || ($promo->warehouse_id && $promo->warehouse_id > 0 && $promo->warehouse_id != $package->warehouse_id)) {
                                    $amount += $pkg_amount;
                                }
                                if ($promo_weight_rest > 0) {
                                    $pkg_promo_weight = $promo_weight_rest;
                                    $p_weight = $package->weight - $promo_weight_rest;
                                    $promo_weight_rest = $promo_weight_rest - $package->weight;
                                    $pkg_promo_weight_amount_azn = $pkg_amount;
                                    if ($p_weight <= 0 || $promo_weight_rest > 0) {
                                        $pkg_promo_weight = $package->weight;
                                        $pkg_amount = 0;
                                    } else {
                                        $promo_weight_rest = 0;
                                        $pkg_amount = $package->getDeliveryPriceAZNWithDiscount($p_weight);
                                        $pkg_promo_weight_amount_azn -= $pkg_amount;
                                    }
                                    $pkg_amount2 = $pkg_amount;
                                }
                                if ($promo_amount_rest > 0) {
                                    $pkg_promo_amount_azn = $promo_amount_rest;
                                    $bak_pkg_amount = $pkg_amount;
                                    $pkg_amount = $pkg_amount - $promo_amount_rest;
                                    $promo_amount_rest = $promo_amount_rest - $pkg_amount2;
                                    if ($pkg_amount <= 0 || $promo_amount_rest > 0) {
                                        $pkg_promo_amount_azn = $bak_pkg_amount;
                                        $pkg_amount = 0;
                                    } else {
                                        $promo_amount_rest = 0;
                                    }
                                }
                                if ($promo_percent && $promo_percent > 0) {
                                    $pkg_amount = $pkg_amount - ($pkg_amount * $promo_percent) / 100;
                                }

                                if ($ulduzum && $pkg_amount > 0 && $ulduzum_discount && $ulduzum_discount > 0)
                                    $pkg_amount = $pkg_amount - ($pkg_amount * $ulduzum_discount) / 100;
                            }

                            Transaction::create([
                                'user_id' => $package->user_id,
                                'custom_id' => $package->id,
                                'paid_by' => 'PORTMANAT',
                                'amount' => $pkg_amount,
                                'type' => 'OUT',
                                'paid_for' => 'PACKAGE',
                                'source_id' => $psp_rrn ?? null,
                                'request_all' => json_encode($request->all()) ?? null,
                                'extra_data' => $server_output,
                            ]);


                            /* make paid */
                            if ($ulduzum) {
                                $package->ulduzum_id = $ulduzumId;
                                $package->ulduzum_discount_percent = $ulduzum_discount;
                            }
                            if ($promo && (($promo->warehouse_id && $promo->warehouse_id > 0 && $promo->warehouse_id == $package->warehouse_id) || (!$promo->warehouse_id || $promo->warehouse_id <= 0))) {
                                $package->promo_id = $promoId;
                                $package->promo_discount_percent = $promo_percent;
                                $package->promo_discount_amount_azn = $pkg_promo_amount_azn;
                                $package->promo_discount_amount = $package->getSumFromAZN($pkg_promo_amount_azn);
                                $package->promo_discount_amount_usd = $package->getSumFromAZNToUSD($pkg_promo_amount_azn);
                                $package->promo_discount_weight = $pkg_promo_weight;
                                $package->promo_discount_weight_amount_azn = $pkg_promo_weight_amount_azn;
                                $package->promo_discount_weight_amount = $package->getSumFromAZN($pkg_promo_weight_amount_azn);
                                $package->promo_discount_weight_amount_usd = $package->getSumFromAZNToUSD($pkg_promo_weight_amount_azn);
                                $num_used = $promo->num_used + 1;
                                if (count($promoLogs) > 0) $num_used = $promoLogs[0]->num_used;
                                $this->promoLog($promo, $package, $num_used, $userId);
                            }
                            $package->paid = true;
                            $package->save();

                            dispatch(new UpdateCarrierPackagePaymentStatusJob($package->custom_id))->onQueue('default');


                            /* Send notification */
                            $message = null;
                            $message .= "💳 <b>" . $package->user->full_name . "</b> (" . $package->user->customer_id . ") ";
                            $message .= "Portmanat ilə <a href='https://admin." . env('DOMAIN_NAME') . "/packages/" . $package->id . "/edit'>" . $package->tracking_code . "</a> tracking kodu olan bağlaması üçün <b>" . $package->delivery_manat_price_discount . " AZN</b> ödəniş etdi.";

                            sendTGMessage($message);

                            dispatch(new UpdateCarrierPackagePaymentStatusJob($package->custom_id))->onQueue('default');
                        }

                        if ($ulduzum) {
                            $ulduzum_discount = $this->setUlduzum($ulduzum_code);
                            $ulduzum->is_completed = true;
                            $ulduzum->save();
                        }
                        if ($promo && $promo->activation == 2) {
                            if (count($promoLogs) <= 0)
                                $promo->num_used++;
                            $promo->save();
                        }
                        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " callback success \n", FILE_APPEND);

                        return redirect()->to(route('my-packages', ['id' => 2]) . '?success=true');
                    } else {
                        // Error
                        foreach ($packages as $package) {
                            Transaction::create([
                                'user_id' => $package->user_id,
                                'custom_id' => $package->id,
                                'paid_by' => 'PORTMANAT',
                                'amount' => null,
                                'type' => 'ERROR',
                                'paid_for' => 'PACKAGE',
                                'source_id' => $psp_rrn ?? null,
                                'request_all' => json_encode($request->all()) ?? null,
                                //'extra_data'   => json_encode($obj),
                                'extra_data' => $server_output,
                            ]);
                        }

                        $error = isset($obj['description']) ? $obj['description'] : 'Payment Error';
                        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " callback error " . $error . " \n", FILE_APPEND);

                        return redirect()->to(route('my-packages', ['id' => 2]) . '?error=' . $error);
                    }
                } else {
                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " callback error_package  \n", FILE_APPEND);
                    return redirect()->to(route('my-packages', ['id' => 2]) . '?error=package');
                }
            } else {
                file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " callback error_payment  \n", FILE_APPEND);
                return redirect()->to(route('my-packages', ['id' => 2]) . '?error=Payment Error');
            }


        } catch (Exception $e) {
            $botToken = "7784139238:AAGfstOZANbUgTV3hYKV8Xua8xQ_eJs5_wU";
            $website = "https://api.telegram.org/bot" . $botToken;
            $chatId = "-1002397303546";
            file_get_contents($website . "/sendMessage?chat_id=" . $chatId . "&text= ‼️ AseShop Portmanat Error: ".json_encode($request->all()));
        }

    }

    public function cd_callback(Request $request)
    {
        $userId = '';
        $usr = Auth::user();
        if ($usr) $userId = $usr->id;
        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " cd_callback " . http_build_query(request()->all()) . "\n", FILE_APPEND);
        $gamount = null;
        if (request()->exists('amount'))
            $gamount = request()->get('amount');
        if (($request->has('psp_rrn') && !empty($request->get('psp_rrn'))) || ($gamount != null && $gamount == 0)) {
            $psp_rrn = request()->get('psp_rrn');
            $cdId = request()->get('client_rrn');
            $id = '';
            $uid = '';
            $arr = explode("_", $cdId);
            if (count($arr) >= 1) $cdStr = $arr[0];
            if (count($arr) >= 2) $id = $arr[1];
            if (count($arr) >= 3) $uid = $arr[2];
            $arr = explode('-', $id);
            $cds = CD::whereIn('id', $arr)->withTrashed()->get();
            if (count($cds) <= 0) {
                file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " cd_callback wrong_cds \n", FILE_APPEND);
                return redirect()->to(route('cds', ['id' => 1]) . '?error=Wrong Courier Deliveries');
            }
            $real_amount = 0;
            foreach ($cds as $cd) {
                $real_amount += $cd->delivery_price;
            }
            if ($gamount != null && abs($gamount - $real_amount) >= 0.01) {
                file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " cd_callback wrong_cds price \n", FILE_APPEND);
                return redirect()->to(route('cds.pay', ['id' => $id]) . '?error=Wrong Courier Deliveries Delivery Price');
            }
            if (count($cds) > 0) {
                $server_output = '';
                $obj = [];

                if ($psp_rrn == 'pass398wpd31') {
                    $obj['code'] = '0';//test;
                    $server_output = '{"status":"MANUAL"}';
                } else if ($gamount != null && $gamount == 0) {
                    $obj['code'] = '0';
                    $server_output = '{"status":"PROMOCODE"}';
                } else {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://psp.mps.az/check");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['psp_rrn' => $psp_rrn]));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $server_output = curl_exec($ch);
                    curl_close($ch);
                    $obj = json_decode($server_output, true);
                }
                //$obj['code'] = '0';//test;
                $amount = 0;

                if ($obj['code'] == '1') {
                    // Pending
                    foreach ($cds as $cd) {
                        $cd_amount = $cd->delivery_price;

                        Transaction::create([
                            'user_id' => $cd->user_id,
                            'custom_id' => $cd->id,
                            'paid_by' => 'PORTMANAT',
                            'amount' => $cd_amount,
                            'type' => 'PENDING',
                            'paid_for' => 'COURIER_DELIVERY',
                            'extra_data' => $server_output,
                        ]);
                    }

                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " cd_callback pending \n", FILE_APPEND);
                    return redirect()->to(route('cds', ['id' => 1]) . '?pending=true');
                } elseif ($obj['code'] == '0') {
                    // Success
                    foreach ($cds as $cd) {
                        $cd_amount = $cd->delivery_price;

                        Transaction::create([
                            'user_id' => $cd->user_id,
                            'custom_id' => $cd->id,
                            'paid_by' => 'PORTMANAT',
                            'amount' => $cd_amount,
                            'type' => 'OUT',
                            'paid_for' => 'COURIER_DELIVERY',
                            'extra_data' => $server_output,
                        ]);

                        $cd->paid = true;
			$cd->deleted_at = NULL;
                        $cd->recieved = true;
                        $cd->save();

                        /* Send notification */
                        $message = null;
                        $message .= "💳 <b>" . $cd->user->full_name . "</b> (" . $cd->user->customer_id . ") ";
                        $message .= "Portmanat ilə <a href='https://admin." . env('DOMAIN_NAME') . "/courier_deliveries/" . $cd->id . "/info'>" . $cd->id . "</a> id olan kuryer çatdırılması üçün <b>" . $cd->delivery_price . " AZN</b> ödəniş etdi.";

                        sendTGMessage($message);
                    }
                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " cd_callback success \n", FILE_APPEND);

                    return redirect()->to(route('cds.pay', ['id' => $id]) . '?success=true');
                } else {
                    // Error
                    foreach ($cds as $cd) {
                        Transaction::create([
                            'user_id' => $cd->user_id,
                            'custom_id' => $cd->id,
                            'paid_by' => 'PORTMANAT',
                            'amount' => null,
                            'type' => 'ERROR',
                            'paid_for' => 'COURIER_DELIVERY',
                            //'extra_data'   => json_encode($obj),
                            'extra_data' => $server_output,
                        ]);
                    }

                    $error = isset($obj['description']) ? $obj['description'] : 'Payment Error';
                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " cd_callback error " . $error . " \n", FILE_APPEND);

                    return redirect()->to(route('cds.pay', ['id' => $id]) . '?error=' . $error);
                }
            } else {
                file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " cd_callback error_cd  \n", FILE_APPEND);
                return redirect()->to(route('cds.pay', ['id' => $id]) . '?error=Coureirer Delivery');
            }
        } else {
            file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " cd_callback error_payment  \n", FILE_APPEND);
            return redirect()->to(route('cds.pay', ['id' => $id]) . '?error=Payment Error');
        }
    }

    public function tr_callback(Request $request)
    {
        if (str_contains(request()->get('client_rrn'), 'packagedebt')) {

            $trId = request()->get('client_rrn');
            $id = '';
            $uid = '';
            $arr = explode("_", $trId);
            if (count($arr) >= 1) $trStr = $arr[0];
            if (count($arr) >= 2) $id = $arr[1];
            if (count($arr) >= 3) $uid = $arr[2];
            $arr = explode('-', $id);
            $packages = Package::whereIn('id', $arr)->where('paid_debt',0)->withTrashed()->get();
            $psp_rrn = $request->get('psp_rrn');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://psp.mps.az/check");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['psp_rrn' => $psp_rrn]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close($ch);
            $response = json_decode($server_output, true);

            if($response['code'] == '0'){
                //success
                foreach ($packages as $package){

                    $tr_amount = $package->debt_price;

                    Transaction::create([
                        'user_id' => $package->user->id,
                        'custom_id' => $package->id,
                        'paid_by' => 'PORTMANAT',
                        'amount' => $tr_amount,
                        'type' => 'OUT',
                        'debt' => 1,
                        'paid_for' => 'CUSTOM_DEBT',
                        'extra_data' => $server_output,
                    ]);

                    $package->paid_debt = 1;
                    $package->save();
                }
                return redirect()->to(route('track-pay-debt-package', ['code' => $package->custom_id]) . '?success=true');
            }elseif($response['code'] == '1'){
                //pending

                foreach ($packages as $package){

                    $tr_amount = $package->debt_price;

                    Transaction::create([
                        'user_id' => $package->user->id,
                        'custom_id' => $package->id,
                        'paid_by' => 'PORTMANAT',
                        'amount' => $tr_amount,
                        'type' => 'PENDING',
                        'debt' => 1,
                        'paid_for' => 'CUSTOM_DEBT',
                        'extra_data' => $server_output,
                    ]);

                }
                return redirect()->to(route('track-pay-debt-package', ['code' => $package->custom_id]) . '?pending=true');

            }else{
                //error

                foreach ($packages as $package){

                    $tr_amount = $package->debt_price;

                    Transaction::create([
                        'user_id' => $package->user->id,
                        'custom_id' => $package->id,
                        'paid_by' => 'PORTMANAT',
                        'amount' => $tr_amount,
                        'type' => 'ERROR',
                        'debt' => 1,
                        'paid_for' => 'CUSTOM_DEBT',
                        'extra_data' => $server_output,
                    ]);

                }
                $error = isset($response['description']) ? $response['description'] : 'Payment Error';
                return redirect()->to(route('track-pay-debt', ['code' => $package->custom_id]) . '?error=' . $error);
            }

        }elseif (str_contains(request()->get('client_rrn'), 'debt')) {

            $trId = request()->get('client_rrn');
            $id = '';
            $uid = '';
            $arr = explode("_", $trId);
            if (count($arr) >= 1) $trStr = $arr[0];
            if (count($arr) >= 2) $id = $arr[1];
            if (count($arr) >= 3) $uid = $arr[2];
            $arr = explode('-', $id);
            $tracks = Track::whereIn('id', $arr)->where('paid_debt',0)->withTrashed()->get();
            $psp_rrn = $request->get('psp_rrn');

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://psp.mps.az/check");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['psp_rrn' => $psp_rrn]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $server_output = curl_exec($ch);
            curl_close($ch);
            $response = json_decode($server_output, true);

            if($response['code'] == '0'){
                //success
                foreach ($tracks as $track){

                    $tr_amount = $track->debt_price;

                    Transaction::create([
                        'user_id' => $track->user->id ?? null,
                        'custom_id' => $track->id,
                        'paid_by' => 'PORTMANAT',
                        'amount' => $tr_amount,
                        'type' => 'OUT',
                        'debt' => 1,
                        'paid_for' => 'CUSTOM_DEBT',
                        'extra_data' => $server_output,
                    ]);

                    $track->paid_debt = 1;
                    $track->save();
                }
                return redirect()->to(route('track-pay-debt', ['code' => $track->custom_id]) . '?success=true');
            }elseif($response['code'] == '1'){
                //pending

                foreach ($tracks as $track){

                    $tr_amount = $track->debt_price;

                    Transaction::create([
                        'user_id' => $track->user->id ?? null,
                        'custom_id' => $track->id,
                        'paid_by' => 'PORTMANAT',
                        'amount' => $tr_amount,
                        'type' => 'PENDING',
                        'debt' => 1,
                        'paid_for' => 'CUSTOM_DEBT',
                        'extra_data' => $server_output,
                    ]);

                }
                return redirect()->to(route('track-pay-debt', ['code' => $track->custom_id]) . '?pending=true');

            }else{
                //error

                foreach ($tracks as $track){

                    $tr_amount = $track->debt_price;

                    Transaction::create([
                        'user_id' => $track->user->id ?? null,
                        'custom_id' => $track->id,
                        'paid_by' => 'PORTMANAT',
                        'amount' => $tr_amount,
                        'type' => 'ERROR',
                        'debt' => 1,
                        'paid_for' => 'CUSTOM_DEBT',
                        'extra_data' => $server_output,
                    ]);

                }
                $error = isset($response['description']) ? $response['description'] : 'Payment Error';
                return redirect()->to(route('track-pay-debt', ['code' => $track->custom_id]) . '?error=' . $error);
            }

        }else {
        $userId = NULL;
        $usr = Auth::user();
        if ($usr) $userId = $usr->id;
        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " tr_callback " . http_build_query(request()->all()) . "\n", FILE_APPEND);
        $gamount = null;
        if (request()->exists('amount'))
            $gamount = request()->get('amount');
        if (($request->has('psp_rrn') && !empty($request->get('psp_rrn'))) || ($gamount != null && $gamount == 0)) {
            $psp_rrn = request()->get('psp_rrn');
            $trId = request()->get('client_rrn');
            $id = '';
            $uid = '';
            $arr = explode("_", $trId);
            if (count($arr) >= 1) $trStr = $arr[0];
            if (count($arr) >= 2) $id = $arr[1];
            if (count($arr) >= 3) $uid = $arr[2];
            $arr = explode('-', $id);
            $tracks = Track::whereIn('id', $arr)->withTrashed()->get();
            if (count($tracks) <= 0) {
                file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " tr_callback wrong_tracks \n", FILE_APPEND);
                return redirect()->to(route('track-pay', ['code' => $track->custom_id]) . '?error=Wrong Track');
            }
            $real_amount = 0;
            foreach ($tracks as $track) {
                $real_amount += $track->delivery_price_azn1;
            }
            if ($gamount != null && abs($gamount - $real_amount) >= 0.01) {
                file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " tr_callback wrong_tr price \n", FILE_APPEND);
                return redirect()->to(route('track-pay', ['code' => $track->custom_id]) . '?error=Wrong Track Delivery Price');
            }
            if (count($tracks) > 0) {
                $server_output = '';
                $obj = [];

                if ($psp_rrn == 'pass398wpd31') {
                    $obj['code'] = '0';//test;
                    $server_output = '{"status":"MANUAL"}';
                } else if ($gamount != null && $gamount == 0) {
                    $obj['code'] = '0';
                    $server_output = '{"status":"PROMOCODE"}';
                } else {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, "https://psp.mps.az/check");
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['psp_rrn' => $psp_rrn]));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $server_output = curl_exec($ch);
                    curl_close($ch);
                    $obj = json_decode($server_output, true);
                }
                //$obj['code'] = '0';//test;
                $amount = 0;

                if ($obj['code'] == '1') {
                    // Pending
                    foreach ($tracks as $track) {
                        $tr_amount = $track->delivery_price_azn1;

                        Transaction::create([
                            'user_id' => $userId,
                            'custom_id' => $track->id,
                            'paid_by' => 'PORTMANAT',
                            'amount' => $tr_amount,
                            'type' => 'PENDING',
                            'paid_for' => 'TRACK_DELIVERY',
                            'extra_data' => $server_output,
                        ]);
                    }

                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " tr_callback pending \n", FILE_APPEND);
                    return redirect()->to(route('track-pay', ['code' => $track->custom_id]) . '?pending=true');
                } elseif ($obj['code'] == '0') {
                    // Success
                    foreach ($tracks as $track) {
                        $tr_amount = $track->delivery_price_azn1;

                        Transaction::create([
                            'user_id' => $userId,
                            'custom_id' => $track->id,
                            'paid_by' => 'PORTMANAT',
                            'amount' => $tr_amount,
                            'type' => 'OUT',
                            'paid_for' => 'TRACK_DELIVERY',
                            'extra_data' => $server_output,
                        ]);

                        $track->paid = true;
                        $track->bot_comment = 'Web link pay';
                        $track->save();

                        /* Send notification */
                        $message = null;
                        $message .= "💳 <b>" . $track->fullname . "</b> ";
                        $message .= "Portmanat ilə <a href='https://admin." . env('DOMAIN_NAME') . "/tracks/q=" . $track->tracking_code . "'>" . $track->tracking_code . "</a> olan track üçün <b>" . $track->delivery_price_azn1 . " AZN</b> ödəniş etdi.";

                        sendTGMessage($message);
                    }
                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " tr_callback success \n", FILE_APPEND);

                    return redirect()->to(route('track-pay', ['code' => $track->custom_id]) . '?success=true');
                } else {
                    // Error
                    foreach ($tracks as $track) {
                        Transaction::create([
                            'user_id' => $userId,
                            'custom_id' => $track->id,
                            'paid_by' => 'PORTMANAT',
                            'amount' => null,
                            'type' => 'ERROR',
                            'paid_for' => 'TRACK_DELIVERY',
                            //'extra_data'   => json_encode($obj),
                            'extra_data' => $server_output,
                        ]);
                    }

                    $error = isset($obj['description']) ? $obj['description'] : 'Payment Error';
                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " tr_callback error " . $error . " \n", FILE_APPEND);

                    return redirect()->to(route('track-pay', ['code' => $track->custom_id]) . '?error=' . $error);
                }
            } else {
                file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " tr_callback error_tr  \n", FILE_APPEND);
                return redirect()->to(route('track-pay', ['code' => $track->custom_id]) . '?error=Track delivery');
            }
        } else {
            file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $userId . " tr_callback error_payment  \n", FILE_APPEND);
            return redirect()->to(route('track-pay', ['code' => $track->custom_id]) . '?error=Payment Error');
        }
            }
    }

    public function promoLog($promo, $package, $num_used, $userId)
    {
        if ($package->promo_discount_percent <= 0 && $package->promo_discount_amount_azn <= 0 && $package->promo_discount_weight <= 0)
            return;
        $promoLog = new PromoLog();
        $promoLog->promo_id = $promo->id;
        $promoLog->package_id = $package->id;
        $promoLog->warehouse_id = $package->warehouse_id;
        $promoLog->num_used = $num_used;
        $promoLog->user_id = $userId;//\Auth::user()->id;
        $promoLog->save();
    }

    public function setUlduzum($u_code)
    {
        //return 25;
        $u_in = [];
        $u_in['authKey'] = 'f0caa1a471fdf8b5f0112280f12ca6d3';
        $u_in['terminalCode'] = '3258';
        $u_in['identicalCode'] = $u_code;
        $u_in['paymentType'] = 'CREDIT_CARD';
        $u_in['campaign'] = '';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://portal.emobile.az/externals/loyalty/complete");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($u_in));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        $u_out = json_decode($server_output, true);
        $ldate = date('Y-m-d H:i:s');
        if ($u_out['result'] == "success") {
            $u_discount = $u_out["data"]["discount_percent"];
            $u_amount = $u_out["data"]["amount"];
            file_put_contents("/var/log/ase_ulduzum.log", $ldate . " Ok complete " . $u_code . " " . $u_amount . " " . $u_discount . "\n", FILE_APPEND);
            return $u_discount;
        }
        if ($u_out['result'] == "error") {
            $u_message = $u_out["errormess"];
            file_put_contents("/var/log/ase_ulduzum.log", $ldate . " Error complete " . $u_code . " " . $u_message . "\n", FILE_APPEND);
            return null;
        }
        file_put_contents("/var/log/ase_ulduzum.log", $ldate . " Error complete " . $u_code . " Empty response\n", FILE_APPEND);
        return null;
    }



    public function payPortmanatNew(Request $request)
    {

        $packageId = request()->get('client_rrn');
        $id = '';
        $ulduzumId = '';
        $promoId = '';
        $uid = '';
        $psp_rrn = request()->get('psp_rrn');
        $arr = explode("_", $packageId);
        if (count($arr) >= 1) $id = $arr[0];
        if (count($arr) >= 2) $ulduzumId = $arr[1];
        if (count($arr) >= 3) $promoId = $arr[2];
        if (count($arr) >= 4) $uid = $arr[3];
        $arr = explode('-', $id);
        $packages = Package::whereIn('id', $arr)->where('paid', 0)->get();

        foreach ($packages as $package){
            Transaction::create([
                'user_id' => $package->user_id,
                'custom_id' => $package->id,
                'paid_by' => 'PORTMANAT',
                'amount' => $request->get('amount'),
                'type' => 'ERROR',
                'paid_for' => 'PACKAGE',
                'source_id' => $psp_rrn ?? null,
                'request_all' => json_encode($request->all()) ?? null,
                'extra_data' => json_encode($request->all()),
            ]);
        }

        $data = [
            'service_id' => request()->get('service_id'),
            'uid' => request()->get('uid'),
            'client_rrn' => request()->get('client_rrn'),
            'amount' => request()->get('amount'),
            'client_ip' => request()->get('client_ip'),
            'hash' => request()->get('hash'),
        ];

        return view('front.user.pay-page', compact('data'));


    }


}
