<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class AzerPoctModel extends Model
{
    //public $AZP_BASE_URL="http://dev.aseshop.az";
    //public $AZP_KEY="YXplcnBvY3RAYXNlc2hvcC5hejowNTA0MjAyMg==";
    public $AZP_BASE_URL = "https://api.azpost.co";
    public $AZP_KEY = "TghzDs7H4Rn70FBWyxzma4HRYfgnnBv89Sl0hup6";

    public $curlDebug = false;


    function log($message)
    {
        echo $message . "\n";
        //file_put_contents("/var/log/ase_ukr_express2.log",$message."\n",FILE_APPEND);
    }

    function order_paid($package)
    {

        $ch = curl_init();
        $str = "{\n";
        $str .= '  "vendor_id": "' . env('AZERPOCT_VENDOR_ID') . '"' . "\n";
        $str .= '  ,"package_id": "' . $package->custom_id . '"' . "\n";
        $str .= '  ,"vendor_payment_status": "' . $package->paid . '"' . "\n";
        $str .= "}\n";
        echo $str;

        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->AZP_BASE_URL . '/order/vp-status');
        //curl_setopt($ch, CURLOPT_URL, $this->AZP_BASE_URL.'/azerpoct');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            //'secret:'.$this->AZP_KEY,
            'x-api-key:' . $this->AZP_KEY,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
        $res = json_decode($output);
        print_r($res);
        /*if(!isset($res->status) || $res->status!=200 || !isset($res->data) || !isset($res->data->order_id) || empty($res->data->order_id)) {
           $this->log('Error: '.$package->custom_id.' adding order '.'output:'.$output);
           return false;
        }*/

        if (!isset($res->status) || ($res->status != 200 && $res->status != 201)) {
            $this->log('Error: ' . $package->custom_id . ' paid order ' . 'output:' . $output);
            return false;
        }
        if ($res->status == 201) {
            $this->log('Warning: ' . $package->custom_id . ' paid order ' . 'output:' . $output);
        }

        $ldate = date('Y-m-d H:i:s');
        $city = '';
        $package->azerpoct_paid = $package->paid;
        $package->save();

        $this->log('Ok: ' . $package->custom_id . ' paid ' . $package->paid);
        return true;
    }

    function order_update($package)
    {
        $status = 0;


        $ch = curl_init();
        $str = "{\n";
        $str .= '  "vendor_id": "' . env('AZERPOCT_VENDOR_ID') . '"' . "\n";
        $str .= '  ,"package_id": "' . $package->custom_id . '"' . "\n";
        $str .= '  ,"status": "' . $status . '"' . "\n";
        $str .= "}\n";
        echo $str;

        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->AZP_BASE_URL . '/order/update');
        //curl_setopt($ch, CURLOPT_URL, $this->AZP_BASE_URL.'/azerpoct');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: */*',
            //'secret:'.$this->AZP_KEY,
            'x-api-key:' . $this->AZP_KEY,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
        $res = json_decode($output);
        print_r($res);
        if (!isset($res->status) || $res->status != 200 || !isset($res->data) || !isset($res->data->order_id) || empty($res->data->order_id)) {
            $this->log('Error: ' . $package->custom_id . ' adding order ' . 'output:' . $output);
            return false;
        }

        $ldate = date('Y-m-d H:i:s');
        $city = '';
        if (isset($res->data->city)) $city = $res->data->city;

        $str = "insert into package_azerpoct(order_id,city,package_id,status,vendor_payment_status,delivery_charge,created_at,updated_at)";
        $str .= " values(?,?,?,?,?,?,?,?)";
        DB::insert($str, [$res->data->order_id, $city, $package->id, 0, $vendor_payment, $res->data->charge, $ldate, $ldate]);

        $package->azerpoct_status = 10;
        $package->save();
        $this->log('Ok: ' . $package->custom_id . ' adding order ' . $res->data->order_id);
        return true;
    }

    function order_view($package)
    {
        $ch = curl_init();
        $str = "{\n";
        $str .= '  "vendor_id": "' . env('AZERPOCT_VENDOR_ID') . '"' . "\n";
        $str .= '  ,"package_id": "' . $package->custom_id . '"' . "\n";
        $str .= "}\n";
        echo $str;

        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->AZP_BASE_URL . '/order/view');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: */*',
            'x-api-key:' . $this->AZP_KEY,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        $output = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($output);
        print_r($res);
        if (!isset($res->status) || $res->status != 200 || !isset($res->data)) {
            return 'Network error:' . $output;
        }

        return 'Ok';
    }

    function order_add($package)
    {
        $user = $package->user;

        if (!$user) {
            return 'User empty';
        }
        $zip_code = strtoupper($user->zip_code);
        if (empty($zip_code)) {
            return 'Zip code empty user:' . $user->customer_id;
        }

        $weight = $package->weight_goods;
        if (empty($weight))
            $weight = $package->weight;
        if (empty($weight)) {
            return 'Weight empty';
        }
        $delivery_type = 0;
        $vendor_payment = $package->paid;


        $ch = curl_init();
        $str = "{\n";
        $str .= '  "vendor_id": "' . env('AZERPOCT_VENDOR_ID') . '"' . "\n";
        $str .= '  ,"package_id": "' . $package->custom_id . '"' . "\n";
        $str .= '  ,"delivery_post_code": "' . $zip_code . '"' . "\n";
        $str .= '  ,"package_weight": ' . $weight . "\n";
        $str .= '  ,"customer_address": "' . $user->address . '"' . "\n";
        $str .= '  ,"first_name": "' . $user->name . '"' . "\n";
        $str .= '  ,"last_name": "' . $user->surname . '"' . "\n";
        $str .= '  ,"email": "' . $user->email . '"' . "\n";
        $str .= '  ,"phone_no": "' . $user->phone . '"' . "\n";
        $str .= '  ,"user_passport": "' . $user->passport . '"' . "\n";
        $str .= '  ,"delivery_type": "' . $delivery_type . '"' . "\n";
        $str .= '  ,"vendor_payment": ' . $vendor_payment . "\n";
        $str .= "}\n";
        echo $str;

        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->AZP_BASE_URL . '/order/create');
        //curl_setopt($ch, CURLOPT_URL, $this->AZP_BASE_URL.'/azerpoct');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: */*',
            //'secret:'.$this->AZP_KEY,
            'x-api-key:' . $this->AZP_KEY,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
        $res = json_decode($output);
        print_r($res);
        if (!isset($res->status) || $res->status != 200 || !isset($res->data) || !isset($res->data->order_Id) || empty($res->data->order_Id)) {
            return 'Network error:' . $output;
        }

        $ldate = date('Y-m-d H:i:s');
        $city = '';
        if (isset($res->data->city)) $city = $res->data->city;

        $str = "insert into package_azerpoct(order_id,city,package_id,status,vendor_payment_status,delivery_charge,created_at,updated_at)";
        $str .= " values(?,?,?,?,?,?,?,?)";
        DB::insert($str, [$res->data->order_Id, $city, $package->id, 0, $vendor_payment, $res->data->charge, $ldate, $ldate]);
        //DB::update("update packages set azerpoct_send=1 where id=?",[$package->id]);
        DB::update("update packages set azerpoct_send=1,delivery_price=null,delivery_price_usd=null,delivery_price_usd_at=null,delivery_price_azn=null,delivery_price_azn_at=null where id=?", [$package->id]);

        return 'Ok';
    }

}

