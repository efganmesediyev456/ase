<?php

namespace App\Http\Controllers\Front;

use App\Http\Requests;
use App\Jobs\UpdateCarrierPackagePaymentStatusJob;
use App\Mail\OrderRequest;
use App\Models\Country;
use App\Models\Extra\Notification;
use App\Models\Order;
use App\Models\Package;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mail;
use Response;

/**
 * Class ExtraController
 *
 * @package App\Http\Controllers\Front
 */
class AseMobileController extends MainController
{
    /**
     * @return JsonResponse
     */
    public $testMode = false;

    public function order_on_create(Request $request)
    {
        $bodyContent = $request->getContent();
        $method = $request->method();
        $ldate = date('Y-m-d H:i:s');
        $ip = $request->ip();
        $str = $ldate . " " . $ip . " ";
        $order_id = $request->get('order_id');
        file_put_contents('/var/log/ase_asemobile.log', 'order_on_create ' . $method . ' ' . $ip . ' ' . $bodyContent . " order_id:" . $order_id . "\n", FILE_APPEND);
        if (!$order_id) {
            file_put_contents('/var/log/ase_asemobile.log', "  Error No order_id\n", FILE_APPEND);
            return Response::json([
                'status' => 400,
                'result' => 'error',
                'message' => 'No order_id'
            ], 400);
        }
        $order = Order::with(['country', 'links', 'user'])->find($order_id);
        if (!$order) {
            file_put_contents('/var/log/ase_asemobile.log', "  Error Order not found" . $order_id . "\n", FILE_APPEND);
            return Response::json([
                'status' => 400,
                'result' => 'error',
                'message' => 'Order not found ' . $order_id
            ], 400);
        }

        $links = $order->links;
        $user = $order->user;
        Notification::sendOrder($order->id);
        $country = Country::find($order->country->id);

        if ($country->emails) {
            $toAdmins = array_map('trim', explode(",", $country->emails));
            Mail::to($toAdmins)->send(new OrderRequest($order));
        }

        $message = "🔗 <b>" . $user->full_name . "</b> (" . $user->customer_id . ") ";
        $message .= $order->country->name . " ölkəsi üzrə ";
        $message .= "<a href='https://admin." . env('DOMAIN_NAME') . "/orders/" . $order->id . "/links'>" . count($links) . " ədəd link</a> sifariş etdi.";

        if ($this->testMode) {
            file_put_contents('/var/log/ase_asemobile.log', "  Ok\n", FILE_APPEND);
            return Response::json([
                'status' => 200,
                'result' => 'success',
                'message' => $message,
            ], 200);
        }
        sendTGMessage($message);
        file_put_contents('/var/log/ase_asemobile.log', "  Ok\n", FILE_APPEND);
        return Response::json([
            'status' => 200,
            'result' => 'success',
            'message' => 'Ok'
        ], 200);
    }


    public function package_on_create(Request $request)
    {
        $bodyContent = $request->getContent();
        $method = $request->method();
        $ldate = date('Y-m-d H:i:s');
        $ip = $request->ip();
        //file_put_contents('/var/log/ase_asemobile.log', $method.' '.$ip.' '.$bodyContent."\n",FILE_APPEND);
        $str = $ldate . " " . $ip . " ";
        $package_id = $request->get('package_id');
        file_put_contents('/var/log/ase_asemobile.log', 'package_on_create ' . $method . ' ' . $ip . ' ' . $bodyContent . " package_id:" . $package_id . "\n", FILE_APPEND);
        if (!$package_id) {
            file_put_contents('/var/log/ase_asemobile.log', "  Error not package_id\n", FILE_APPEND);
            return Response::json([
                'status' => 400,
                'result' => 'error',
                'message' => 'No package_id'
            ], 400);
        }
        $package = Package::whereId($package_id)->first();
        if (!$package) {
            file_put_contents('/var/log/ase_asemobile.log', "  Error package not found\n", FILE_APPEND);
            return Response::json([
                'status' => 400,
                'result' => 'error',
                'message' => 'Package not found ' . $package_id
            ], 400);
        }
        $message = "🛑 <b>" . $package->user->full_name . "</b> (" . $package->user->customer_id . ") ";
        $message .= "<a href='https://admin." . env('DOMAIN_NAME') . "/packages/" . $package->id . "/edit'>" . $package->tracking_code . "</a> tracking code ilə yeni bəyannamə yaratdı.";
        if ($this->testMode) {
            file_put_contents('/var/log/ase_asemobile.log', "  Ok\n", FILE_APPEND);
            return Response::json([
                'status' => 200,
                'result' => 'success',
                'message' => $message,
            ], 200);
        }
        sendTGMessage($message);
        file_put_contents('/var/log/ase_asemobile.log', "  Ok\n", FILE_APPEND);
        return Response::json([
            'status' => 200,
            'result' => 'success',
            'message' => 'Ok'
        ], 200);
    }

    public function package_on_delete(Request $request)
    {
        $bodyContent = $request->getContent();
        $method = $request->method();
        $ldate = date('Y-m-d H:i:s');
        $ip = $request->ip();
        $str = $ldate . " " . $ip . " ";
        $package_id = $request->get('package_id');
        file_put_contents('/var/log/ase_asemobile.log', 'package_on_delete ' . $method . ' ' . $ip . ' ' . $bodyContent . " package_id:" . $package_id . "\n", FILE_APPEND);
        if (!$package_id) {
            file_put_contents('/var/log/ase_asemobile.log', "  Error no package_id\n", FILE_APPEND);
            return Response::json([
                'status' => 400,
                'result' => 'error',
                'message' => 'No package_id'
            ], 400);
        }
        $package = Package::withTrashed()->whereId($package_id)->first();
        if (!$package) {
            file_put_contents('/var/log/ase_asemobile.log', "  Error package not found\n", FILE_APPEND);
            return Response::json([
                'status' => 400,
                'result' => 'error',
                'message' => 'Package not found ' . $package_id
            ], 400);
        }
        $message = "🛑 <b>" . $package->user->full_name . "</b> (" . $package->user->customer_id . ") ";
        $message .= "<a href='https://admin." . env('DOMAIN_NAME') . "/packages/" . $package->id . "/edit'>" . $package->tracking_code . "</a> tracking code ilə olan bəyannaməsini sildi!";
        if ($this->testMode) {
            file_put_contents('/var/log/ase_asemobile.log', "  Ok\n", FILE_APPEND);
            return Response::json([
                'status' => 200,
                'result' => 'success',
                'message' => $message,
            ], 200);
        }
        sendTGMessage($message);
        file_put_contents('/var/log/ase_asemobile.log', "  Ok\n", FILE_APPEND);
        return Response::json([
            'status' => 200,
            'result' => 'success',
            'message' => 'Ok'
        ], 200);
    }

    public function package_on_paid(Request $request)
    {
        $bodyContent = $request->getContent();
        $method = $request->method();
        $ldate = date('Y-m-d H:i:s');
        $ip = $request->ip();
        $str = $ldate . " " . $ip . " ";
        $package_id = $request->get('package_id');
        $amount = $request->get('amount');
        file_put_contents('/var/log/ase_asemobile.log', 'package_on_paid ' . $method . ' ' . $ip . ' ' . $bodyContent . " package_id:" . $package_id . " amount:" . $amount . "\n", FILE_APPEND);
        if (!$package_id) {
            file_put_contents('/var/log/ase_asemobile.log', "  Error no package_id\n", FILE_APPEND);
            return Response::json([
                'status' => 400,
                'result' => 'error',
                'message' => 'No package_id'
            ], 400);
        }
        /*if (!$amount) {
            file_put_contents('/var/log/ase_asemobile.log', "  Error no amount\n", FILE_APPEND);
            return Response::json([
                'status' => 400,
                'result' => 'error',
                'message' => 'No amount'
            ], 400);
	}*/
        $package = Package::whereId($package_id)->first();
        if (!$package) {
            file_put_contents('/var/log/ase_asemobile.log', "  Error Package not found\n", FILE_APPEND);
            return Response::json([
                'status' => 400,
                'result' => 'error',
                'message' => 'Package not found ' . $package_id
            ], 400);
        }
        $message = "💳 <b>" . $package->user->full_name . "</b> (" . $package->user->customer_id . ") ";
        $message .= "Asemobile ilə <a href='https://admin." . env('DOMAIN_NAME') . "/packages/" . $package->id . "/edit'>" . $package->tracking_code . "</a> tracking kodu olan bağlaması üçün <b>";
        if ($amount)
            $message .= $amount . " AZN</b>";
        $message .= " ödəniş etdi.";
        if ($this->testMode) {
            file_put_contents('/var/log/ase_asemobile.log', "  Ok\n", FILE_APPEND);
            return Response::json([
                'status' => 200,
                'result' => 'success',
                'message' => $message,
            ], 200);
        }
        sendTGMessage($message);
        dispatch(new UpdateCarrierPackagePaymentStatusJob($package->custom_id))->onQueue('default');
        file_put_contents('/var/log/ase_asemobile.log', "  Ok\n", FILE_APPEND);

        dispatch(new UpdateCarrierPackagePaymentStatusJob($package->custom_id))->onQueue('default');

        return Response::json([
            'status' => 200,
            'result' => 'success',
            'message' => 'Ok'
        ], 200);
    }

    public function user_on_create(Request $request)
    {
        $bodyContent = $request->getContent();
        $method = $request->method();
        $ldate = date('Y-m-d H:i:s');
        $ip = $request->ip();
        $str = $ldate . " " . $ip . " ";
        $user_id = $request->get('user_id');
        file_put_contents('/var/log/ase_asemobile.log', 'user_on_create ' . $method . ' ' . $ip . ' ' . $bodyContent . " user_id:" . $user_id . "\n", FILE_APPEND);
        if (!$user_id) {
            file_put_contents('/var/log/ase_asemobile.log', "  Error no user_id\n", FILE_APPEND);
            return Response::json([
                'status' => 400,
                'result' => 'error',
                'message' => 'No user_id'
            ], 400);
        }
        $user = User::whereId($user_id)->first();
        if (!$user) {
            file_put_contents('/var/log/ase_asemobile.log', "  Error user not found\n", FILE_APPEND);
            return Response::json([
                'status' => 400,
                'result' => 'error',
                'message' => 'User not found ' . $user_id
            ], 400);
        }
        $message = "✅ <b>" . $user->full_name . "</b> (" . $user->customer_id . ") ";
        $message .= ($user->city_name ? ($user->city_name . " şəhərindən ") : null) . "qeydiyyatdan keçdi.";
        if ($user->promo) {
            $message .= " promo code:" . $user->promo->code;
        }
        if ($this->testMode) {
            file_put_contents('/var/log/ase_asemobile.log', "  Ok\n", FILE_APPEND);
            return Response::json([
                'status' => 200,
                'result' => 'success',
                'message' => $message,
            ], 200);
        }
        sendTGMessage($message);
        file_put_contents('/var/log/ase_asemobile.log', "  Ok\n", FILE_APPEND);
        return Response::json([
            'status' => 200,
            'result' => 'success',
            'message' => 'Ok'
        ], 200);
    }

    public function package(Request $request)
    {
        $bodyContent = $request->getContent();
        $method = $request->method();
        $ldate = date('Y-m-d H:i:s');
        $ip = $request->ip();
        //file_put_contents('/var/log/ase_asemobile.log', $method.' '.$ip.' '.$bodyContent."\n",FILE_APPEND);
        $str = $ldate . " " . $ip . " ";
        $packageArr = null;
        if ($request->has('package_id')) {
            $packageArr = [];
            $package = Package::where('id', $request->get('package_id'))->first();
            if ($package) {
                $packageArr['id'] = $package->id;
                $packageArr['shipping_amount_usd'] = $package->getShippingAmountUSD();

                $shipping_amount = $package->shipping_amount_goods;
                if (empty($shipping_amount))
                    $shipping_amount = $package->shipping_amount;
                $packageArr['shipping_amount'] = $shipping_amount;

                $weigh = $package->weight_goods;
                if (empty($weight))
                    $weight = $package->weight;
                $packageArr['weight'] = $weight;

                $packageArr['delivery_price_usd_discount'] = $package->delivery_usd_price_discount;
                $packageArr['delivery_price_azn_discount'] = $package->getDeliveryPriceAZNWithDiscount();
                $packageArr['total_price_usd'] = $package->getTotalPriceAttribute();
            }
        }
        if ($packageArr && count($packageArr) > 0) {
            return Response::json([
                'status' => 200,
                'package' => $packageArr,
                'result' => 'success',
                'message' => 'Ok'
            ], 200);
        } else {
            return Response::json([
                'status' => 400,
                'result' => 'error',
                'message' => 'No package_id or package not found'
            ], 400);
        }
    }

    public function user(Request $request)
    {
        $bodyContent = $request->getContent();
        $method = $request->method();
        $ldate = date('Y-m-d H:i:s');
        $ip = $request->ip();
        //file_put_contents('/var/log/ase_asemobile.log', $method.' '.$ip.' '.$bodyContent."\n",FILE_APPEND);
        $str = $ldate . " " . $ip . " ";
        $userArr = null;
        if ($request->has('user_id')) {
            $userArr = [];
            $user = User::where('id', $request->get('user_id'))->first();
            if ($user) {
                $data = NULL;
                $total_price_usd = 0;
                $userArr['id'] = $user->id;
                $startForExists = Carbon::now()->firstOfMonth()->format('Y-m-d H:i:s');
                if ($user->check_customs) {
                    $data = Package::join('package_carriers', 'packages.id', '=', 'package_carriers.package_id')->where('user_id', $user->id)->whereIn('package_carriers.code', [200, 400])->whereRaw("(((package_carriers.inserT_DATE is not null) and (package_carriers.inserT_DATE >='" . $startForExists . "')) or ((package_carriers.inserT_DATE is null) and (package_carriers.created_at >='" . $startForExists . "')))")->get();

                } else {
                    $data = Package::where('user_id', $user->id)->whereNotNull('sent_at')->where('sent_at', '>=', $startForExists)->get();
                }

                if ($data) {
                    foreach ($data as $package) {
                        $total_price_usd += $package->total_price;
                    }
                }
                $userArr['total_price_usd_last_month'] = $total_price_usd;
            }
        }

        if ($userArr && count($userArr) > 0) {
            return Response::json([
                'status' => 200,
                'user' => $userArr,
                'result' => 'success',
                'message' => 'Ok'
            ], 200);
        } else {
            return Response::json([
                'status' => 400,
                'result' => 'error',
                'message' => 'No user_id or user not found'
            ], 400);
        }
    }

    public function invoice(Request $request)
    {
        $bodyContent = $request->getContent();
        $method = $request->method();
        $ldate = date('Y-m-d H:i:s');
        $ip = $request->ip();
        file_put_contents('/var/log/ase_asemobile.log', $method . ' ' . $ip . ' ' . $bodyContent . "\n", FILE_APPEND);
        $str = $ldate . " " . $ip . " ";
        if ($request->hasFile('invoice')) {
            //$fileName=$request->file('invoice');
            $fileName = uniqid() . '.' . $request->file('invoice')->getClientOriginalExtension();
            $request->file('invoice')->move(public_path('uploads/packages/'), $fileName);
            $str .= " Ok invoice:" . $fileName . "\n";
            file_put_contents('/var/log/ase_asemobile.log', $str, FILE_APPEND);
            return Response::json([
                'status' => 200,
                'invoice' => $fileName,
                'result' => 'success',
                'message' => 'Ok'
            ], 200);
        } else {
            $str .= " Error no invoice in request\n";
            file_put_contents('/var/log/ase_asemobile.log', $str, FILE_APPEND);
            return Response::json([
                'status' => 400,
                'result' => 'error',
                'message' => 'No invoice in request'
            ], 400);
        }
    }
}
