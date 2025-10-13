<?php

namespace App\Http\Controllers\Front;

use App\Http\Requests;
use App\Models\Package;
use App\Models\Transaction;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Response;

/**
 * Class ExtraController
 *
 * @package App\Http\Controllers\Front
 */
class AzerPoctController extends MainController
{
    /**
     * @return JsonResponse
     */

    public function callback(Request $request)
    {
        $sendMessage = true;
        $bodyContent = $request->getContent();
        $method = $request->method();
        $ldate = date('Y-m-d H:i:s');
        $ip = $request->ip();
        file_put_contents('/var/log/ase_azerpoct.log', $method . ' ' . $ip . ' ' . $bodyContent . "\n", FILE_APPEND);
        $str = $ldate . " " . $ip . " ";
        $data = json_decode($bodyContent);
        $event = 'order_update';
        if (isset($data->event))
            $event = $data->event;
        $status = '';
        if (isset($data->db_results) && isset($data->db_results->status))
            $status = $data->db_results->status;
        $status_id = '';
        if (isset($data->status_id))
            $status_id = $data->status_id;
        $details = '';
        if (isset($data->details))
            $details = $data->details;
        $packages = [];
        if (isset($data->packages) && is_array($data->packages))
            $packages = $data->packages;
        if (isset($data->package_id))
            $packages[] = $data->package_id;
        $str .= $event . ' status:' . $status_id . ' packages:' . implode($packages, ',') . "\n";
        file_put_contents('/var/log/ase_azerpoct.log', $str, FILE_APPEND);
        //$packages = Package::whereNull('deleted_at')->where('azerpoct_status',1)->where('azerpoct_send',1)->whereIn('custom_id',$packages)->get();
        $packages = Package::whereNull('deleted_at')->whereIn('custom_id', $packages)->get();
        if ($event == 'order_update') {
            foreach ($packages as $package) {
                DB::update('update package_azerpoct set status=?,updated_at=? where package_id=?', [$status_id, $ldate, $package->id]);
                if (($package->status != 3) && ($status_id == 5)) {
                    $package->status = 3;
                    $package->save();
                    file_put_contents('/var/log/ase_azerpoct.log', " done\n", FILE_APPEND);
                }
            }
        }
        if ($event == 'vendor_payment_update' && $status_id == 1) {
            foreach ($packages as $package) {
                file_put_contents('/var/log/ase_azerpoct.log', '  ' . $package->custom_id, FILE_APPEND);
                if ($package->paid) {
                    file_put_contents('/var/log/ase_azerpoct.log', " already paid\n", FILE_APPEND);
                    continue;
                }
                $pkg_amount = $package->delivery_manat_price_discount;
                Transaction::create([
                    'user_id' => $package->user_id,
                    'custom_id' => $package->id,
                    'paid_by' => 'AZERPOCT',
                    'amount' => $pkg_amount,
                    'type' => 'OUT',
                    'paid_for' => 'PACKAGE',
                    'extra_data' => $bodyContent,
                ]);
                $package->paid = true;
                $package->azerpoct_paid = true;
                $package->save();
                $message = null;
                $message .= "💳 <b>" . $package->user->full_name . "</b> (" . $package->user->customer_id . ") ";
                $message .= "AzerPoct ilə <a href='https://admin." . env('DOMAIN_NAME') . "/packages/" . $package->id . "/edit'>" . $package->tracking_code . "</a> tracking kodu olan bağlaması üçün <b>" . $pkg_amount . " AZN</b> ödəniş etdi.";
                if ($sendMessage) {
                    sendTGMessage($message);
                } else {
                    //file_put_contents('/var/log/ase_azerpoct.log', $message,FILE_APPEND);
                }
                file_put_contents('/var/log/ase_azerpoct.log', " paid\n", FILE_APPEND);
            }
        }

        /*	return \Response::json([
                'status' => '200',
                'data' => [
                'order_id' => '22222222',
                'charge' => '2.2',
                'status' => 'true',
                ]
            ]);*/

        return Response::json([
            'status' => 200,
            'result' => 'success',
            'message' => 'Ok'
        ]);
    }
}
