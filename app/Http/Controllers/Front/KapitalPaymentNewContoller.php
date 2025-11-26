<?php

namespace App\Http\Controllers\Front;

use Alert;
use App\Jobs\UpdateCarrierPackagePaymentStatusJob;
use App\Models\CD;
use App\Models\Package;
use App\Models\PayPhone;
use App\Models\Track;
use App\Models\Payments\PortManat;
use App\Models\Promo;
use App\Models\PromoLog;
use App\Models\Transaction;
use App\Models\Ulduzum;
use App\Models\User;
use App\Services\KapitalBank\KapitalBankTxpgService;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;

class KapitalPaymentNewContoller extends MainController
{


    function log($message)
    {
        file_put_contents("/var/log/test_kapital_order_create.log", $message . "\n", FILE_APPEND);
    }

    public function postKapitalNewPay(Request $request, $type)
    {

        $requestAll = json_encode($request->all());
        $this->log(now()->format('Y-m-d H:i:s') . " requestAll - {$requestAll} type - $type");


        if ($type == 'courier') {
            $courierDelivery = CD::where('id', $request->item_id)->withTrashed()->first();

            $price = $request->amount;
            $body = [
                'order' => [
                    'typeRid' => 'Order_SMS',
                    'amount' => number_format(($price), 2, ".", ""),
                    'currency' => 'AZN',
                    'language' => 'en',
                    'description' => 'AseShop',
                    'hppRedirectUrl' => 'https://aseshop.az/kapital-bank/callback',
                    'hppCofCapturePurposes' => ['Cit']
                ]
            ];
            $kapitalBankTxpgService = new KapitalBankTxpgService();

            $kapitalResponse = $kapitalBankTxpgService->createOrder($body);
            $OrderID = $kapitalResponse['order_id'];
            $password = $kapitalResponse['password'];
            $redirectUrl = $kapitalResponse['redirectUrl'];

            $this->log(now()->format('Y-m-d H:i:s') . " response from kapital - " . json_encode($kapitalResponse));


            $tran = Transaction::create([
                'user_id' => $courierDelivery->user_id,
                'custom_id' => $courierDelivery->id,
                'paid_by' => 'KAPITAL',
                'amount' => $price,
                'source_id' => $OrderID,
                'type' => 'PENDING',
                'paid_for' => 'COURIER_DELIVERY',
            ]);

            $this->log(now()->format('Y-m-d H:i:s') . " created transactions - " . json_encode($tran));


            return redirect($redirectUrl);

        } elseif ($type == 'market') {
            $pay_phone = PayPhone::where('id', $request->item_id)->first();

            $price = $pay_phone->amount;

            $body = [
                'order' => [
                    'typeRid' => 'Order_SMS',
                    'amount' => number_format(($price), 2, ".", ""),
                    'currency' => 'AZN',
                    'language' => 'en',
                    'description' => 'AseShop',
                    'hppRedirectUrl' => 'https://aseshop.az/kapital-bank/callback',
                    'hppCofCapturePurposes' => ['Cit']
                ]
            ];

            $kapitalBankTxpgService = new KapitalBankTxpgService();

            $kapitalResponse = $kapitalBankTxpgService->createOrder($body);
            $OrderID = $kapitalResponse['order_id'];
            $redirectUrl = $kapitalResponse['redirectUrl'];

            $this->log(now()->format('Y-m-d H:i:s') . " response from kapital - " . json_encode($kapitalResponse));


            $pay_phone->order_id = $kapitalResponse['order_id'];

            $pay_phone->save();

            $tran = Transaction::create([
//                'user_id' => $courierDelivery->user_id,
                'custom_id' => $pay_phone->id,
                'phone' => $pay_phone->phone,
                'paid_by' => 'KAPITAL',
                'amount' => $price,
                'source_id' => $OrderID,
                'type' => 'PENDING',
                'paid_for' => 'MARKET',
            ]);

            $this->log(now()->format('Y-m-d H:i:s') . " created transactions - " . json_encode($tran));


            return redirect($redirectUrl);
        } elseif ($type == 'track_broker') {
            $track = Track::find($request->item_id);

            $price = 15;

            $body = [
                'order' => [
                    'typeRid' => 'Order_SMS',
                    'amount' => number_format(($price), 2, ".", ""),
                    'currency' => 'AZN',
                    'language' => 'en',
                    'description' => 'AseShop',
                    'hppRedirectUrl' => 'https://aseshop.az/kapital-bank/callback',
                    'hppCofCapturePurposes' => ['Cit']
                ]
            ];
            $kapitalBankTxpgService = new KapitalBankTxpgService();

            $kapitalResponse = $kapitalBankTxpgService->createOrder($body);
            $OrderID = $kapitalResponse['order_id'];
            $password = $kapitalResponse['password'];
            $redirectUrl = $kapitalResponse['redirectUrl'];

            $this->log(now()->format('Y-m-d H:i:s') . " response from kapital - " . json_encode($kapitalResponse));


            $tran = Transaction::create([
                'user_id' => null,
                'custom_id' => $track->id,
                'paid_by' => 'KAPITAL',
                'amount' => $price,
                'source_id' => $OrderID,
                'type' => 'PENDING',
                'paid_for' => 'TRACK_BROKER',
            ]);

            $this->log(now()->format('Y-m-d H:i:s') . " created transactions - " . json_encode($tran));


            return redirect($redirectUrl);
        } elseif ($type == 'package_broker') {
            $package = Package::find($request->item_id);

            $price = (empty($item->user->voen)) ? 15 : 50;
//            $price = 0.1;

            $body = [
                'order' => [
                    'typeRid' => 'Order_SMS',
                    'amount' => number_format(($price), 2, ".", ""),
                    'currency' => 'AZN',
                    'language' => 'en',
                    'description' => 'AseShop',
                    'hppRedirectUrl' => 'https://aseshop.az/kapital-bank/callback',
                    'hppCofCapturePurposes' => ['Cit']
                ]
            ];
            $kapitalBankTxpgService = new KapitalBankTxpgService();

            $kapitalResponse = $kapitalBankTxpgService->createOrder($body);
            $OrderID = $kapitalResponse['order_id'];
            $password = $kapitalResponse['password'];
            $redirectUrl = $kapitalResponse['redirectUrl'];

            $this->log(now()->format('Y-m-d H:i:s') . " response from kapital - " . json_encode($kapitalResponse));

            $tran = Transaction::create([
                'user_id' => null,
                'custom_id' => $package->id,
                'paid_by' => 'KAPITAL',
                'amount' => $price,
                'source_id' => $OrderID,
                'type' => 'PENDING',
                'paid_for' => 'PACKAGE_BROKER',
            ]);
            $this->log(now()->format('Y-m-d H:i:s') . " created transactions - " . json_encode($tran));


            return redirect($redirectUrl);
        } elseif ($type == 'package') {
//            $totalPrice = $request->get('amount');
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

            $arr = explode('-', $id);
            $packages = Package::whereIn('id', $arr)->where('paid', 0)->get();

            $totalPrice = Package::whereIn('id', $arr)->where('paid', 0)->sum('delivery_price_azn');

            $body = [
                'order' => [
                    'typeRid' => 'Order_SMS',
                    'amount' => number_format(($totalPrice), 2, ".", ""),
                    'currency' => 'AZN',
                    'language' => 'en',
                    'description' => 'AseShop',
                    'hppRedirectUrl' => 'https://aseshop.az/kapital-bank/callback',
                    'hppCofCapturePurposes' => ['Cit']
                ]
            ];
            $kapitalBankTxpgService = new KapitalBankTxpgService();

            $kapitalResponse = $kapitalBankTxpgService->createOrder($body);


            $this->log(now()->format('Y-m-d H:i:s') . " response from kapital - " . json_encode($kapitalResponse));


            $transactions = [];
//            if ($kapitalResponse) {
            $OrderID = $kapitalResponse['order_id'];
            $password = $kapitalResponse['password'];
            $redirectUrl = $kapitalResponse['redirectUrl'];
            foreach ($packages as $package) {
                $price = $package->delivery_price_azn;
                $tran = Transaction::create([
                    'user_id' => $package->user_id,
                    'custom_id' => $package->id,
                    'paid_by' => 'KAPITAL',
                    'amount' => $price,
                    'source_id' => $OrderID,
                    'type' => 'PENDING',
                    'paid_for' => 'PACKAGE',
                ]);

                $transactions[] = $tran;
                $package->transaction_id = $OrderID;
                $package->save();

            }
//            }

            $this->log(now()->format('Y-m-d H:i:s') . " created transactions - " . json_encode($transactions));


            return redirect($redirectUrl);

        } elseif ($type == 'track') {


            $track = Track::find($request->item_id);

            $price = $request->amount;
            $body = [
                'order' => [
                    'typeRid' => 'Order_SMS',
                    'amount' => number_format(($price), 2, ".", ""),
                    'currency' => 'AZN',
                    'language' => 'en',
                    'description' => 'AseShop',
                    'hppRedirectUrl' => 'https://aseshop.az/kapital-bank/callback',
                    'hppCofCapturePurposes' => ['Cit']
                ]
            ];
            $kapitalBankTxpgService = new KapitalBankTxpgService();

            $kapitalResponse = $kapitalBankTxpgService->createOrder($body);
            $OrderID = $kapitalResponse['order_id'];
            $password = $kapitalResponse['password'];
            $redirectUrl = $kapitalResponse['redirectUrl'];

            $this->log(now()->format('Y-m-d H:i:s') . " response from kapital - " . json_encode($kapitalResponse));


            $tran = Transaction::create([
                'user_id' => null,
                'custom_id' => $track->id,
                'paid_by' => 'KAPITAL',
                'amount' => $price,
                'source_id' => $OrderID,
                'type' => 'PENDING',
                'paid_for' => 'TRACK_DELIVERY',
            ]);

            $this->log(now()->format('Y-m-d H:i:s') . " created transactions - " . json_encode($tran));


            return redirect($redirectUrl);
        } elseif ($type == 'package_debt') {

            $package = Package::find($request->item_id);

            if ($package->paid != 1) {
                return redirect()->to(route('my-packages', ['id' => 2]) . '?error=' . 'Bağlamanın daşınma haqqı ödənilməyib');
            }

            $price = $package->debt_price;
            $body = [
                'order' => [
                    'typeRid' => 'Order_SMS',
                    'amount' => number_format(($price), 2, ".", ""),
                    'currency' => 'AZN',
                    'language' => 'en',
                    'description' => 'AseShop',
                    'hppRedirectUrl' => 'https://aseshop.az/kapital-bank/callback',
                    'hppCofCapturePurposes' => ['Cit']
                ]
            ];
            $kapitalBankTxpgService = new KapitalBankTxpgService();

            $kapitalResponse = $kapitalBankTxpgService->createOrder($body);
            $OrderID = $kapitalResponse['order_id'];
            $password = $kapitalResponse['password'];
            $redirectUrl = $kapitalResponse['redirectUrl'];

            $this->log(now()->format('Y-m-d H:i:s') . " response from kapital - " . json_encode($kapitalResponse));

            $tran = Transaction::create([
                'user_id' => $package->user_id,
                'custom_id' => $package->id,
                'paid_by' => 'KAPITAL',
                'amount' => $price,
                'source_id' => $OrderID,
                'type' => 'PENDING',
                'paid_for' => 'PACKAGE_DEBT',
                'debt' => 1,
            ]);

            $this->log(now()->format('Y-m-d H:i:s') . " created transactions - " . json_encode($tran));


            return redirect($redirectUrl);

        } elseif ($type == 'track_debt') {
            $track = Track::find($request->item_id);

            $price = $track->debt_price;
            $body = [
                'order' => [
                    'typeRid' => 'Order_SMS',
                    'amount' => number_format(($price), 2, ".", ""),
                    'currency' => 'AZN',
                    'language' => 'en',
                    'description' => 'AseShop',
                    'hppRedirectUrl' => 'https://aseshop.az/kapital-bank/callback',
                    'hppCofCapturePurposes' => ['Cit']
                ]
            ];
            $kapitalBankTxpgService = new KapitalBankTxpgService();

            $kapitalResponse = $kapitalBankTxpgService->createOrder($body);
            $OrderID = $kapitalResponse['order_id'];
            $password = $kapitalResponse['password'];
            $redirectUrl = $kapitalResponse['redirectUrl'];

            $this->log(now()->format('Y-m-d H:i:s') . " response from kapital - " . json_encode($kapitalResponse));

            $tran = Transaction::create([
                'user_id' => null,
                'custom_id' => $track->id,
                'paid_by' => 'KAPITAL',
                'amount' => $price,
                'source_id' => $OrderID,
                'type' => 'PENDING',
                'paid_for' => 'TRACK_DEBT',
                'debt' => 1,
            ]);

            $this->log(now()->format('Y-m-d H:i:s') . " created transactions - " . json_encode($tran));

            return redirect($redirectUrl);

        }

    }


    public function callback(Request $request)
    {
        try {

            $orderId = $request->get('ID');
            $orderStatus = $request->get('STATUS');

            $findTransaction = Transaction::where('source_id', $orderId)->where('paid_by', 'KAPITAL')->first();

            if ($findTransaction && $findTransaction->paid_for == 'COURIER_DELIVERY') {
                $findCourierDelivery = CD::where('id', $findTransaction->custom_id)->withTrashed()->first();

                if ($orderStatus == "FullyPaid") {
                    $kapitalBankTxpgService = new KapitalBankTxpgService();

                    $orderStatus = $kapitalBankTxpgService->getOrderStatus($orderId);

                    if (isset($orderStatus['order']['status']) and $orderStatus['order']['status'] == 'FullyPaid') {


                        $findTransaction->type = 'OUT';
                        $findTransaction->extra_data = json_encode($orderStatus);
                        $findTransaction->request_all = json_encode($request->all());
                        $findTransaction->save();

                        $findCourierDelivery->paid = 1;
                        $findCourierDelivery->deleted_at = NULL;
                        $findCourierDelivery->recieved = true;
                        $findCourierDelivery->save();

                        /* Send notification */
                        $message = null;
                        $message .= "💳 <b>" . $findCourierDelivery->user->full_name . "</b> (" . $findCourierDelivery->user->customer_id . ") ";
                        $message .= "Kapital ilə <a href='https://admin." . env('DOMAIN_NAME') . "/courier_deliveries/" . $findCourierDelivery->id . "/info'>" . $findCourierDelivery->id . "</a> id olan kuryer çatdırılması üçün <b>" . $findCourierDelivery->delivery_price . " AZN</b> ödəniş etdi.";

                        sendTGMessage($message);

                        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $findCourierDelivery->user_id . " courier_callback success \n", FILE_APPEND);

                        return redirect()->to(route('cds.pay', ['id' => $findCourierDelivery->id]) . '?success=true');

                    } else {

                        $findTransaction->type = 'ERROR';
                        $findTransaction->extra_data = $orderStatus;
                        $findTransaction->request_all = json_encode($request->all());
                        $findTransaction->save();

                        $error = $orderStatus ?? 'Payment Error';
                        return redirect()->to(route('cds.pay', ['id' => $findCourierDelivery->id]) . '?error=' . $error);
                    }
                } elseif ($orderStatus == 'Declined' || $orderStatus == 'Cancelled') {

                    $findTransaction->extra_data = $orderStatus;
                    $findTransaction->type = 'ERROR';
                    $findTransaction->request_all = json_encode($request->all());
                    $findTransaction->save();

                    $error = $orderStatus ?? 'Payment Error';
                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $findCourierDelivery->user_id . " cd_callback error " . $error . " \n", FILE_APPEND);

                    return redirect()->to(route('cds.pay', ['id' => $findCourierDelivery->id]) . '?error=' . $error);
                }
            } elseif ($findTransaction && $findTransaction->paid_for == 'MARKET') {
                $findCourierDelivery = PayPhone::where('id', $findTransaction->custom_id)->first();

                if ($orderStatus == "FullyPaid") {
                    $kapitalBankTxpgService = new KapitalBankTxpgService();

                    $orderStatus = $kapitalBankTxpgService->getOrderStatus($orderId);

                    if (isset($orderStatus['order']['status']) and $orderStatus['order']['status'] == 'FullyPaid') {

                        $findTransaction->type = 'OUT';
                        $findTransaction->extra_data = json_encode($orderStatus);
                        $findTransaction->request_all = json_encode($request->all());
                        $findTransaction->save();

                        $findCourierDelivery->status = 'success';
                        $findCourierDelivery->save();

                        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $findCourierDelivery->id . " courier_callback success \n", FILE_APPEND);

                        return redirect()->to(route('payment.pay', ['id' => $findCourierDelivery->id]) . '?success=true');

                    } else {

                        $findTransaction->type = 'ERROR';
                        $findTransaction->extra_data = $orderStatus;

                        $findTransaction->request_all = json_encode($request->all());
                        $findTransaction->save();
                        $findCourierDelivery->status = 'failed';
                        $findCourierDelivery->save();
                        $error = $orderStatus ?? 'Payment Error';
                        return redirect()->to(route('payment.pay', ['id' => $findCourierDelivery->id]) . '?error=' . $error);
                    }
                } elseif ($orderStatus == 'Declined' || $orderStatus == 'Cancelled') {

                    $findTransaction->extra_data = $orderStatus;
                    $findTransaction->type = 'ERROR';
                    $findTransaction->request_all = json_encode($request->all());
                    $findTransaction->save();
                    $findCourierDelivery->status = 'failed';
                    $findCourierDelivery->save();

                    $error = $orderStatus ?? 'Payment Error';
                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $findCourierDelivery->user_id . " cd_callback error " . $error . " \n", FILE_APPEND);

                    return redirect()->to(route('payment.pay', ['id' => $findCourierDelivery->id]) . '?error=' . $error);
                }
            } elseif ($findTransaction && $findTransaction->paid_for == 'TRACK_BROKER') {
                $findTrackBroker = Track::where('id', $findTransaction->custom_id)->first();

                if ($orderStatus == "FullyPaid") {
                    $kapitalBankTxpgService = new KapitalBankTxpgService();

                    $orderStatus = $kapitalBankTxpgService->getOrderStatus($orderId);

                    if (isset($orderStatus['order']['status']) and $orderStatus['order']['status'] == 'FullyPaid') {

                        $findTransaction->type = 'OUT';
                        $findTransaction->extra_data = json_encode($orderStatus);
                        $findTransaction->request_all = json_encode($request->all());
                        $findTransaction->save();

                        $findTrackBroker->paid_broker = 1;
                        $findTrackBroker->broker_price = 15;
                        $findTrackBroker->save();

                        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $findTrackBroker->id . " courier_callback success \n", FILE_APPEND);

                        return redirect()->route('payment.broker.pay', ['success' => true]);


                    } else {

                        $findTransaction->type = 'ERROR';
                        $findTransaction->extra_data = $orderStatus;

                        $findTransaction->request_all = json_encode($request->all());
                        $findTransaction->save();

                        $error = $orderStatus ?? 'Payment Error';
                        return redirect()->route('payment.broker.pay', ['error' => $error]);
                    }
                } elseif ($orderStatus == 'Declined' || $orderStatus == 'Cancelled') {

                    $findTransaction->extra_data = $orderStatus;
                    $findTransaction->type = 'ERROR';
                    $findTransaction->request_all = json_encode($request->all());
                    $findTransaction->save();

                    $error = $orderStatus ?? 'Payment Error';
                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $findTrackBroker->user_id . " cd_callback error " . $error . " \n", FILE_APPEND);

                    return redirect()->route('payment.broker.pay', ['error' => $error]);
                }
            } elseif ($findTransaction && $findTransaction->paid_for == 'PACKAGE_BROKER') {
                $findPackageBroker = Package::where('id', $findTransaction->custom_id)->first();

                if ($orderStatus == "FullyPaid") {
                    $kapitalBankTxpgService = new KapitalBankTxpgService();

                    $orderStatus = $kapitalBankTxpgService->getOrderStatus($orderId);

                    if (isset($orderStatus['order']['status']) and $orderStatus['order']['status'] == 'FullyPaid') {

                        $findTransaction->type = 'OUT';
                        $findTransaction->extra_data = json_encode($orderStatus);
                        $findTransaction->request_all = json_encode($request->all());
                        $findTransaction->save();

                        $findPackageBroker->paid_broker = 1;
                        $findPackageBroker->broker_price = (empty($findPackageBroker->user->voen)) ? 15 : 50;
                        $findPackageBroker->save();

                        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $findPackageBroker->id . " courier_callback success \n", FILE_APPEND);

                        return redirect()->route('payment.broker.pay', ['success' => true]);

                    } else {

                        $findTransaction->type = 'ERROR';
                        $findTransaction->extra_data = $orderStatus;

                        $findTransaction->request_all = json_encode($request->all());
                        $findTransaction->save();
                        $error = $orderStatus ?? 'Payment Error';
                        return redirect()->route('payment.broker.pay', ['error' => $error]);
                    }
                } elseif ($orderStatus == 'Declined' || $orderStatus == 'Cancelled') {

                    $findTransaction->extra_data = $orderStatus;
                    $findTransaction->type = 'ERROR';
                    $findTransaction->request_all = json_encode($request->all());
                    $findTransaction->save();

                    $error = $orderStatus ?? 'Payment Error';
                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $findPackageBroker->user_id . " cd_callback error " . $error . " \n", FILE_APPEND);

                    return redirect()->route('payment.broker.pay', ['error' => $error]);
                }
            } elseif ($findTransaction && $findTransaction->paid_for == 'PACKAGE') {

                $findPackages = Package::where('transaction_id', $orderId)->get();
                $findPackageTransaction = Transaction::where('source_id', $orderId)->get();


                if ($orderStatus == "FullyPaid") {
                    $kapitalBankTxpgService = new KapitalBankTxpgService();

                    $orderStatus = $kapitalBankTxpgService->getOrderStatus($orderId);

                    if (isset($orderStatus['order']['status']) and $orderStatus['order']['status'] == 'FullyPaid') {

                        foreach ($findPackageTransaction as $transaction) {

                            $transaction->type = 'OUT';
                            $transaction->extra_data = json_encode($orderStatus);
                            $transaction->request_all = json_encode($request->all());
                            $transaction->save();
                            $package = Package::find($transaction->custom_id);
                            $package->paid = 1;
                            $package->save();
                            dispatch(new UpdateCarrierPackagePaymentStatusJob($package->custom_id))->onQueue('default');


                            /* Send notification */
                            $message = null;
                            $message .= "💳 <b>" . $package->user->full_name . "</b> (" . $package->user->customer_id . ") ";
                            $message .= "Portmanat ilə <a href='https://admin." . env('DOMAIN_NAME') . "/packages/" . $package->id . "/edit'>" . $package->tracking_code . "</a> tracking kodu olan bağlaması üçün <b>" . $package->delivery_manat_price_discount . " AZN</b> ödəniş etdi.";

                            sendTGMessage($message);

                            dispatch(new UpdateCarrierPackagePaymentStatusJob($package->custom_id))->onQueue('default');


                        }


//                        foreach ($findPackageTransaction as $transaction) {
//
//                            $transaction->type = 'OUT';
//                            $transaction->extra_data = json_encode($orderStatus);
//                            $transaction->request_all = json_encode($request->all());
//                            $transaction->save();
//
//                        }
//
//                        foreach ($findPackages as $package) {
//                            $package->paid = 1;
//                            $package->save();
//
//                            dispatch(new UpdateCarrierPackagePaymentStatusJob($package->custom_id))->onQueue('default');
//
//
//                            /* Send notification */
//                            $message = null;
//                            $message .= "💳 <b>" . $package->user->full_name . "</b> (" . $package->user->customer_id . ") ";
//                            $message .= "Portmanat ilə <a href='https://admin." . env('DOMAIN_NAME') . "/packages/" . $package->id . "/edit'>" . $package->tracking_code . "</a> tracking kodu olan bağlaması üçün <b>" . $package->delivery_manat_price_discount . " AZN</b> ödəniş etdi.";
//
//                            sendTGMessage($message);
//
//                            dispatch(new UpdateCarrierPackagePaymentStatusJob($package->custom_id))->onQueue('default');
//
//                        }

                        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $findPackageTransaction->first()->user_id . " callback success \n", FILE_APPEND);

                        return redirect()->to(route('my-packages', ['id' => 2]) . '?success=true');

                    } else {

                        foreach ($findPackageTransaction as $transaction) {

                            $transaction->type = 'ERROR';
                            $transaction->extra_data = $orderStatus;
                            $transaction->request_all = json_encode($request->all());
                            $transaction->save();

                        }

                        $error = $orderStatus ?? 'Payment Error';
                        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $findTransaction->first()->user_id . " callback error " . $error . " \n", FILE_APPEND);

                        return redirect()->to(route('my-packages', ['id' => 2]) . '?error=' . $error);

                    }
                } elseif ($orderStatus == 'Declined' || $orderStatus == 'Cancelled') {

                    foreach ($findPackageTransaction as $transaction) {

                        $transaction->type = 'ERROR';
                        $transaction->extra_data = $orderStatus;
                        $transaction->request_all = json_encode($request->all());
                        $transaction->save();

                    }

                    $error = $orderStatus ?? 'Payment Error';
                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $findTransaction->first()->user_id . " callback error " . $error . " \n", FILE_APPEND);

                    return redirect()->to(route('my-packages', ['id' => 2]) . '?error=' . $error);
                } else {
                    $error = $orderStatus ?? 'Payment Error';
                    return redirect()->to(route('my-packages', ['id' => 2]) . '?error=' . $error);
                }

            } elseif ($findTransaction && $findTransaction->paid_for == 'TRACK_DELIVERY') {
                $Track = Track::find($findTransaction->custom_id);

                if ($orderStatus == "FullyPaid") {
                    $kapitalBankTxpgService = new KapitalBankTxpgService();

                    $orderStatus = $kapitalBankTxpgService->getOrderStatus($orderId);

                    if (isset($orderStatus['order']['status']) and $orderStatus['order']['status'] == 'FullyPaid') {

                        $findTransaction->type = 'OUT';
                        $findTransaction->extra_data = json_encode($orderStatus);
                        $findTransaction->request_all = json_encode($request->all());
                        $findTransaction->save();

                        $Track->paid = 1;
                        $Track->bot_comment = 'Web link pay';
                        $Track->save();

                        $message = null;
                        $message .= "💳 <b>" . $Track->fullname . "</b> ";
                        $message .= "Portmanat ilə <a href='https://admin." . env('DOMAIN_NAME') . "/tracks/q=" . $Track->tracking_code . "'>" . $Track->tracking_code . "</a> olan track üçün <b>" . $Track->delivery_price_azn1 . " AZN</b> ödəniş etdi.";

                        sendTGMessage($message);

                        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $Track->fullname . " tr_callback success \n", FILE_APPEND);

                        return redirect()->to(route('track-pay', ['code' => $Track->custom_id]) . '?success=true');

                    } else {

                        $findTransaction->type = 'ERROR';
                        $findTransaction->extra_data = $orderStatus;
                        $findTransaction->request_all = json_encode($request->all());
                        $findTransaction->save();

                        $error = $orderStatus ?? 'Payment Error';
                        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $Track->fullname . " tr_callback error " . $error . " \n", FILE_APPEND);

                        return redirect()->to(route('track-pay', ['code' => $Track->custom_id]) . '?error=' . $error);


                    }
                } elseif ($orderStatus == 'Declined' || $orderStatus == 'Cancelled') {

                    $findTransaction->extra_data = $orderStatus;
                    $findTransaction->type = 'ERROR';
                    $findTransaction->request_all = json_encode($request->all());
                    $findTransaction->save();

                    $error = $orderStatus ?? 'Payment Error';
                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $Track->fullname . " tr_callback error " . $error . " \n", FILE_APPEND);

                    return redirect()->to(route('track-pay', ['code' => $Track->custom_id]) . '?error=' . $error);
                }
            } elseif ($findTransaction && $findTransaction->paid_for == 'PACKAGE_DEBT') {
                $package = Package::find($findTransaction->custom_id);

                if ($orderStatus == "FullyPaid") {
                    $kapitalBankTxpgService = new KapitalBankTxpgService();

                    $orderStatus = $kapitalBankTxpgService->getOrderStatus($orderId);

                    if (isset($orderStatus['order']['status']) and $orderStatus['order']['status'] == 'FullyPaid') {

                        $findTransaction->type = 'OUT';
                        $findTransaction->extra_data = json_encode($orderStatus);
                        $findTransaction->request_all = json_encode($request->all());
                        $findTransaction->debt = 1;
                        $findTransaction->save();

                        $package->paid_debt = 1;
                        $package->bot_comment = 'Web link pay';
                        $package->save();

                        $message = null;
                        $message .= "💳 <b>" . $package->user->full_name . "</b> (" . $package->user->customer_id . ") ";
                        $message .= "Kapital ilə <a href='https://admin." . env('DOMAIN_NAME') . "/tracks/q=" . $package->tracking_code . "'>" . $package->tracking_code . "</a> olan package debt üçün <b>" . $package->debt_price . " AZN</b> ödəniş etdi.";

                        sendTGMessage($message);

                        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $package->user->full_name . " tr_callback success \n", FILE_APPEND);

                        return redirect()->to(route('my-packages', ['id' => 2]) . '?success=true');

                    } else {

                        $findTransaction->type = 'ERROR';
                        $findTransaction->extra_data = $orderStatus;
                        $findTransaction->request_all = json_encode($request->all());
                        $findTransaction->save();

                        $error = $orderStatus ?? 'Payment Error';
                        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $package->user->full_name . " tr_callback error " . $error . " \n", FILE_APPEND);

                        return redirect()->to(route('my-packages', ['id' => 2]) . '?error=' . $error);

                    }
                } elseif ($orderStatus == 'Declined' || $orderStatus == 'Cancelled') {

                    $findTransaction->extra_data = $orderStatus;
                    $findTransaction->type = 'ERROR';
                    $findTransaction->request_all = json_encode($request->all());
                    $findTransaction->save();

                    $error = $orderStatus ?? 'Payment Error';
                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $package->user->full_name . " tr_callback error " . $error . " \n", FILE_APPEND);

                    return redirect()->to(route('my-packages', ['id' => 2]) . '?error=' . $error);
                }
            } elseif ($findTransaction && $findTransaction->paid_for == 'TRACK_DEBT') {
                $track = Track::find($findTransaction->custom_id);

                if ($orderStatus == "FullyPaid") {
                    $kapitalBankTxpgService = new KapitalBankTxpgService();

                    $orderStatus = $kapitalBankTxpgService->getOrderStatus($orderId);

                    if (isset($orderStatus['order']['status']) and $orderStatus['order']['status'] == 'FullyPaid') {

                        $findTransaction->type = 'OUT';
                        $findTransaction->extra_data = json_encode($orderStatus);
                        $findTransaction->request_all = json_encode($request->all());
                        $findTransaction->debt = 1;
                        $findTransaction->save();

                        $track->paid_debt = 1;
                        $track->bot_comment = 'Web link pay';
                        $track->save();

                        $message = null;
                        $message .= "💳 <b>" . $track->fullname . "</b>";
                        $message .= "Kapital ilə <a href='https://admin." . env('DOMAIN_NAME') . "/tracks/q=" . $track->tracking_code . "'>" . $track->tracking_code . "</a> olan track debt üçün <b>" . $track->debt_price . " AZN</b> ödəniş etdi.";

                        sendTGMessage($message);

                        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $track->fullname . " tr_callback success \n", FILE_APPEND);

                        return redirect()->to(route('track-pay-debt', ['code' => $track->custom_id]) . '?success=true');

                    } else {

                        $findTransaction->type = 'ERROR';
                        $findTransaction->extra_data = $orderStatus;
                        $findTransaction->request_all = json_encode($request->all());
                        $findTransaction->save();

                        $error = $orderStatus ?? 'Payment Error';
                        file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $track->fullname . " tr_callback error " . $error . " \n", FILE_APPEND);

                        return redirect()->to(route('track-pay-debt', ['code' => $track->custom_id]) . '?error=' . $error);


                    }
                } elseif ($orderStatus == 'Declined' || $orderStatus == 'Cancelled') {

                    $findTransaction->extra_data = $orderStatus;
                    $findTransaction->type = 'ERROR';
                    $findTransaction->request_all = json_encode($request->all());
                    $findTransaction->save();

                    $error = $orderStatus ?? 'Payment Error';
                    file_put_contents('/var/log/ase_portmanat.log', Carbon::now() . " " . $track->fullname . " tr_callback error " . $error . " \n", FILE_APPEND);

                    return redirect()->to(route('track-pay-debt', ['code' => $track->custom_id]) . '?error=' . $error);
                }
            }


        } catch (Exception $exception) {

            $botToken = "7784139238:AAGfstOZANbUgTV3hYKV8Xua8xQ_eJs5_wU";
            $website = "https://api.telegram.org/bot" . $botToken;
            $chatId = "-1002397303546";
            file_get_contents($website . "/sendMessage?chat_id=" . $chatId . "&text= ‼️ AseShop Error: " . $request . ' - Az');
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
}
