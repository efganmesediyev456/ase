<?php

namespace App\Http\Controllers\Front;

use App\Http\Requests;
use DB;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use SimpleXMLElement;
use function Response;

/**
 * Class ExtraController
 *
 * @package App\Http\Controllers\Front
 */
class KapitalPaymentController extends MainController
{
    /**
     * @return JsonResponse
     */
    public function __construct()
    {
        //$this->callback(\Request());
    }

    public function index()
    {
    }

    public function resp($code, $msg, $res_code = 200)
    {
        $ldate = date('Y-m-d H:i:s');
        file_put_contents('/var/log/ase_kapital_payment.log', $ldate . " " . $code . " " . $msg . "\n", FILE_APPEND);
        if (!$res_code)
            $res_code = 200;
        return Response('<Response code="' . $code . '" eng="' . $msg . '" />', $res_code)->header('Content-Type', 'text/xml');
    }


    public function callback(Request $request)
    {

        //$bodyContent = urldecode($request->getContent());
        $bodyContent = urldecode($request->post('xmlmsg'));
        if (empty($bodyContent))
            $bodyContent = urldecode($request->getContent());
        $method = $request->method();
        $ldate = date('Y-m-d H:i:s');
        $ip = $request->ip();
        //file_put_contents('/var/log/ase_kapital_payment.log', $method.' '.$ip.' '.$bodyContent."\n",FILE_APPEND);
        file_put_contents('/var/log/ase_kapital_payment.log', $ldate . ' ' . $method . ' ' . $ip . "\n", FILE_APPEND);
        file_put_contents('/var/log/ase_kapital_payment.log', '  data:' . $bodyContent . "\n", FILE_APPEND);
        //$str= $ldate." ".$ip." ";
        if (empty($bodyContent))
            return $this->resp('020', 'Empty request');
        $query_str = "insert into kapital_payment(created_at";
        $query_str .= ",MessageDate,ResponseCode";
        $query_str .= ",SessionId,PAN,TranId,TotalAmount,PurchaseAmount,OrderStatus,CardUID,OrderId,TransactionType,OrderDescription,PurchaseAmountScr,TotalAmountScr,FeeScr,AcqFeeScr,TranDateTime,ResponseDescription,OrderStatusScr,MerchantTranId";
        $query_str .= ",xml_content)";
        $query_str .= " values(?,str_to_date(?,'%d/%m/%Y %H:%i:%s')";
        $query_str .= ",?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,str_to_date(?,'%d/%m/%Y %H:%i:%s'),?,?,?";
        $query_str .= ",?)";
        $xml = null;
        try {
            $xml = new SimpleXMLElement($bodyContent);
        } catch (Exception $e) {
            $xml = false;
            file_put_contents('/var/log/ase_kapital_payment.log', $ldate . ' wrong data (' . $e->getMessage() . '): ' . $bodyContent . "\n", FILE_APPEND);
            return $this->resp('020', 'Wrong xml');
        }
        //file_put_contents('/var/log/ase_kapital_payment.log', ' ['.$xml->Message->asXml()."]\n",FILE_APPEND);
        if (!$xml || !isset($xml->Message)) {
            file_put_contents('/var/log/ase_kapital_payment.log', $ldate . ' wrong data (no message): ' . $bodyContent . "\n", FILE_APPEND);
            return $this->resp('020', 'No Message');
        }
        $msg = $xml->Message;
        DB::insert($query_str, [$ldate, $msg['date']
            , $msg->ResponseCode
            , $msg->SessionId
            , $msg->PAN
            , $msg->TranId ? $msg->TranId : NULL
            , $msg->TotalAmount ? $msg->TotalAmount : NULL
            , $msg->PurchaseAmount ? $msg->PurchaseAmount : NULL
            , $msg->OrderStatus
            , $msg->CardRegistrationResponse->CardUID
            , $msg->OrderID ? $msg->OrderID : NULL
            , $msg->TransactionType
            , $msg->OrderDescription
            , $msg->PurchaseAmountScr
            , $msg->TotalAmountScr
            , $msg->FeeScr
            , $msg->AcqFeeScr
            , $msg->TranDateTime
            , $msg->ResponseDescription
            , $msg->OrderStatusScr
            , $msg->MerchantTranId
            , $bodyContent]);
        $ch = curl_init();

        //curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, env('KAPITAL_WEBHOOK_URL'));
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: */*',
            'Authorization: bearer ' . env('KAPITAL_WEBHOOK_KEY'),
            "Content-Type: application/xml"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyContent);
        $output = curl_exec($ch);
        $res_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        //$res=json_decode($output);
        file_put_contents('/var/log/ase_kapital_payment.log', ' Res: ' . $res_code . ' ' . $output . "\n", FILE_APPEND);
        if ($res_code == 200 || !$res_code) {
            return $this->resp('000', 'Ok');
        }
        return $this->resp($res_code, "Error", $res_code);
    }
}
