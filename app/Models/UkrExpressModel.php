<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;

class UkrExpressModel extends Model
{
    //public $UE_BASE_URL="https://api.testsrvr.ukraine-express.com/partners/v2";
    //public $UE_KEY="3d0d6cad7b47a2e6bb36e674e575ce62";
    public $UE_BASE_URL = "http://partnersapi.ukraine-express.com/partners/v2";
    public $UE_KEY = "60b29738a2fd15426f0b62cb445986ad";
    //public $UE_KEY="94835679856-3274782374-94785-3245";

    public $curlDebug = false;

    public $div = 'us';
    public $doLog = true;
    public $doErr = false;
    public $message;
    public $code;
    public $id;
    public $trackingNumber;

    function log($message)
    {
        if ($this->doLog)
            echo $message . "\n";
        //file_put_contents("/var/log/ase_uexpress2.log",$message."\n",FILE_APPEND);
    }

    function err($message)
    {
        if ($this->doErr) {
            $ldate = date('Y-m-d H:i:s');
            file_put_contents("/var/log/ase_uexpress2_error.log", $ldate . " model " . $message . "\n", FILE_APPEND);
        }
    }

    function airbox_list()
    {
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/airboxes/');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        $output = curl_exec($ch);
        echo 'URL:' . $this->UE_BASE_URL . '/' . $this->div . '/airboxes/' . "\n";
        echo 'DATA:' . $output . "\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        if (isset($res->success) && !$res->success) {
            $this->log('Error: airbox list message:' . $res->message);
            $this->err('Error: airbox list message:' . $res->message);
            return null;
        }
        if (!isset($res->data) || !is_array($res->data)) {
            $this->log('Error: airbox list no data:');
            $this->err('Error: airbox list no data:');
            return null;
        }
        print_r($res->data);
        return $res->data;
    }

    /*    function parcel_list() {
    	$ch = curl_init();
	if($this->curlDebug)
           curl_setopt($ch, CURLOPT_VERBOSE, true);

	curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL.'/'.$this->div.'/parcels/');
	curl_setopt($ch, CURLOPT_USERAGENT,'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
           'accept: text/plain',
           'access_token:'.$this->UE_KEY,
	   "Content-Type: application/json"
        ));
	$output = curl_exec($ch);
	//echo $output."\n";
	curl_close($ch);
	$res=json_decode($output);
	if(isset($res->success) && !$res->success) {
	   $this->log('Error: parcel list message:'.$res->message);
	   $this->err('Error: parcel list message:'.$res->message);
	   return null;
	}
	if(!isset($res->data) || !is_array($res->data)) {
	   $this->log('Error: parcel list no data:');
	   $this->err('Error: parcel list no data:');
	   return null;
	}
	print_r($res->data);
	return $res->data;
    }
 */

    function airbox_get($id)
    {
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/airbox/' . $id);
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        if (!$res || !isset($res->id)) {
            return null;
        }
        return $res;
    }

    function parcel_get($customer_id, $id)
    {
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $customer_id . '/parcel/' . $id);
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        if (!$res || !isset($res->id)) {
            return null;
        }
        return $res;
    }

    function track_delete($tracking_id, $customer_id)
    {
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $customer_id . '/tracking-numbers/' . $tracking_id);
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        $output = curl_exec($ch);
        echo "URL: " . $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $customer_id . '/tracking-numbers/' . $tracking_id . "\n";
        echo $output . "\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        if (isset($res->success) && !$res->success) {
            $this->log('Error: track message: ' . $res->message);
            //$this->err('Error: track message: '.$res->message);
            return null;
        }
        if (!isset($res) || !is_array($res) || count($res) <= 0) {
            $this->log('Error: track no data');
            //$this->err('Error: track no data');
            return null;
        }
        return $res;
    }

    function parse_output($output) {
	if(!empty($output) && (strpos($output,'k') === 0)) {
	   return ltrim($output, 'k');
	}
	return $output;
    }

    function track_get_photos($tracking_id, $customer_id)
    {

        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $customer_id . '/tracking-numbers/' . $tracking_id . '/photos');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        $output = curl_exec($ch);
        //echo "URL: ".$this->UE_BASE_URL.'/'.$this->div.'/customer/'.$customer_id.'/tracking-numbers/'.$tracking_id.'/photos'."\n";
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        if (isset($res->success) && !$res->success) {
            $this->log('Error: message: ' . $res->message);
            $this->message = '-';
            //$this->err('Error: track message: '.$res->message);
            return null;
        }
        if (!isset($res) || !is_array($res) || count($res) <= 0) {
            $this->log('-');
            $this->message = '-';
            //$this->err('Error: track no data');
            return null;
        }
        return $res;
    }

    function track_get_declaration($tracking_id, $customer_id)
    {
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $customer_id . '/tracking-numbers/' . $tracking_id . '/declaration');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        $output = curl_exec($ch);
        echo "URL: " . $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $customer_id . '/tracking-numbers/' . $tracking_id . '/declaration' . "\n";
        echo $output . "\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        if (isset($res->success) && !$res->success) {
            $this->log('Error: track message: ' . $res->message);
            $this->err('Error: track message: ' . $res->message);
            return null;
        }
        if (!isset($res) || !is_array($res) || count($res) <= 0) {
            $this->log('Error: track no data');
            $this->err('Error: track no data');
            return null;
        }
        return $res;
    }

    function customer_get($id)
    {
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $id);
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        //echo "URL: ".$this->UE_BASE_URL.'/'.$this->div.'/tracking-numbers/?filter_ids='.$id."\n";
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        if (isset($res->success) && !$res->success) {
            $this->log('Error: track message:' . $res->message);
            $this->err('Error: track message:' . $res->message);
            return null;
        }
        print_r($res);
        return;
    }


    function track_get_by_number($number, $customer_id = NULL)
    {
        if (empty($number))
            return NULL;

        if ($customer_id) {
            $ch = curl_init();
            if ($this->curlDebug)
                curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $customer_id . '/tracking-numbers/by-number/' . $number);
            curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'accept: text/plain',
                'access_token:' . $this->UE_KEY,
                "Content-Type: application/json"
            ));
            //echo "URL: ".$this->UE_BASE_URL.'/'.$this->div.'/customer/'.$customer_id.'/tracking-numbers/'.$id."\n";
            $output = curl_exec($ch);
            //echo $output."\n";
            curl_close($ch);
	    $output=$this->parse_output($output);
            $res = json_decode($output);
            if (isset($res->success) && !$res->success) {
                //$this->log('Error: track_get message:'.$res->message);
                //$this->err('Error: track_get message:'.$res->message);
            } else if (!isset($res->id)) {
                // $this->log('Error: track_get no data');
                // $this->err('Error: track_get no data');
            } else {
                //print_r($res);
                return $res;
            }
        }
        return NULL;
    }

    function track_get($id, $customer_id = NULL)
    {
        if (!$id)
            return NULL;

        if ($customer_id) {
            $ch = curl_init();
            if ($this->curlDebug)
                curl_setopt($ch, CURLOPT_VERBOSE, true);
            curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $customer_id . '/tracking-numbers/' . $id);
            curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'accept: text/plain',
                'access_token:' . $this->UE_KEY,
                "Content-Type: application/json"
            ));
            //echo "URL: ".$this->UE_BASE_URL.'/'.$this->div.'/customer/'.$customer_id.'/tracking-numbers/'.$id."\n";
            $output = curl_exec($ch);
            //echo $output."\n";
            curl_close($ch);
	    $output=$this->parse_output($output);
            $res = json_decode($output);
            if (isset($res->success) && !$res->success) {
                $this->log('Error: track_get message:' . $res->message);
                $this->err('Error: track_get message:' . $res->message);
            } else if (!isset($res->id)) {
                $this->log('Error: track_get no data');
                $this->err('Error: track_get no data');
            } else {
                //print_r($res);
                return $res;
            }
        }

        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/tracking-numbers/?filter_ids=' . $id);
        //curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL.'/'.$this->div.'/tracking-numbers/'.$id);
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        //echo "URL: ".$this->UE_BASE_URL.'/'.$this->div.'/tracking-numbers/?filter_ids='.$id."\n";
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        if (isset($res->success) && !$res->success) {
            $this->log('Error: track_get message:' . $res->message);
            $this->err('Error: track_get message:' . $res->message);
            return null;
        }
        if (!isset($res->data) || !is_array($res->data) || count($res->data) <= 0) {
            $this->log('Error: track_get no data');
            $this->err('Error: track_get no data');
            return null;
        }
        //print_r($res->data);
        return $res->data[0];
    }

    function track_list_parcel($parcel_id)
    {
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/tracking-numbers/?parcel_id=' . $parcel_id);
        echo "URL: " . $this->UE_BASE_URL . '/' . $this->div . '/tracking-numbers/?parcel_id=' . $parcel_id . "\n";
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        if (isset($res->success) && !$res->success) {
            $this->log('Error: track list message:' . $res->message);
            $this->err('Error: track list message:' . $res->message);
            return null;
        }
        if (!isset($res->data) || !is_array($res->data)) {
            $this->log('Error: track list no data:');
            $this->err('Error: track list no data:');
            return null;
        }
        //print_r($res->data);
        return $res->data;
    }

    function parcel_list($offset = 0, $limit = 0, $container_id = NULL, $airbox_id = NULL, $in_warehouse = NULL)
    {
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        $params = '';
        if ($offset) {
            if (!empty($params)) $params .= '&';
            $params .= 'offset=' . $offset;
        }
        if ($limit) {
            if (!empty($params)) $params .= '&';
            $params .= 'limit=' . $limit;
        }
        if ($container_id) {
            if (!empty($params)) $params .= '&';
            $params .= 'container_id=' . $container_id;
        }
        if ($airbox_id) {
            if (!empty($params)) $params .= '&';
            $params .= 'airbox_id=' . $airbox_id;
        }
        if ($in_warehouse) {
            if (!empty($params)) $params .= '&';
            $params .= 'in_warehouse=1';
        }
        $url = $this->UE_BASE_URL . '/' . $this->div . '/parcels/';
        if (!empty($params)) $url .= '?' . $params;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        if (isset($res->success) && !$res->success) {
            $this->log('Error: parcel list message:' . $res->message);
            $this->err('Error: parcel list message:' . $res->message);
            return null;
        }
        if (!isset($res->data) || !is_array($res->data)) {
            $this->log('Error: parcel list no data:');
            $this->err('Error: parcel list no data:');
            return null;
        }
        //print_r($res->data);
        return $res->data;
    }

    function track_list($offset = 0, $limit = 0, $filter = '', $customer_id = NULL)
    {
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        $params = '';
        if ($offset) {
            if (!empty($params)) $params .= '&';
            $params .= 'offset=' . $offset;
        }
        if ($limit) {
            if (!empty($params)) $params .= '&';
            $params .= 'limit=' . $limit;
        }
        if (!empty($filter)) {
            if (!empty($params)) $params .= '&';
            $params .= $filter;
        }
        $url = $this->UE_BASE_URL . '/' . $this->div . '/tracking-numbers/';
        if ($customer_id)
            $url = $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $customer_id . '/tracking-numbers/';
        if (!empty($params)) $url .= '?' . $params;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        if (isset($res->success) && !$res->success) {
            $this->log('Error: track list message:' . $res->message);
            $this->err('Error: track list message:' . $res->message);
            return null;
        }
        if (!isset($res->data) || !is_array($res->data)) {
            $this->log('Error: track list no data:');
            $this->err('Error: track list no data:');
            return null;
        }
        //print_r($res->data);
        return $res->data;
    }

    function test_receive_and_assign_tracking($tracking_number, $customer_id)
    {
        $str = "{\n";
        $str .= '  "tracking_number": "' . $tracking_number . '"' . "\n";
        $str .= '  ,"customer_id": ' . $customer_id . "\n";
        $str .= '  ,"division": "' . $this->div . '"' . "\n";
        $str .= "}\n";
        return $this->test($str, 'receive-and-assign');
    }

    function test_receive_tracking($tracking_id)
    {
        $str = "{\n";
        $str .= '  "tracking_id": ' . $tracking_id . "\n";
        $str .= '  ,"division": "' . $this->div . '"' . "\n";
        $str .= "}\n";
        return $this->test($str, 'receive-tracking');
    }

    function test_pack_tracking($tracking_id)
    {
        $str = "{\n";
        $str .= '  "tracking_id": ' . $tracking_id . "\n";
        $str .= '  ,"division": "' . $this->div . '"' . "\n";
        $str .= "}\n";
        return $this->test($str, 'pack-tracking');
    }

    function test_parcel_to_airbox($parcel_id, $new_airbox = 'true')
    {
        $str = "{\n";
        $str .= '  "parcel_id": ' . $parcel_id . "\n";
        $str .= '  ,"division": "' . $this->div . '"' . "\n";
        $str .= '  ,"new_airbox": ' . $new_airbox . "\n";
        $str .= "}\n";
        return $this->test($str, 'parcel-to-airbox');
    }

    function test_airbox_to_container($airbox_id, $new_container = 'true')
    {
        $str = "{\n";
        $str .= '  "airbox_id": ' . $airbox_id . "\n";
        $str .= '  ,"division": "' . $this->div . '"' . "\n";
        $str .= '  ,"new_container": ' . $new_container . "\n";
        $str .= "}\n";
        return $this->test($str, 'airbox-to-container');
    }

    function test_load_parcel($parcel_id, $new_container)
    {
        $str = "{\n";
        $str .= '  "parcel_id": ' . $parcel_id . "\n";
        $str .= '  ,"division": "' . $this->div . '"' . "\n";
        $str .= '  ,"new_container": ' . $new_container . "\n";
        $str .= "}\n";
        return $this->test($str, 'load-parcel');
    }

    function test($str, $event)
    {
        //echo $str;
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/test/' . $event);
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        $output = curl_exec($ch);
        echo 'URL: ' . $this->UE_BASE_URL . '/test/' . $event . "\n";
        echo 'Data: ' . $str . "\n";
        echo 'Output: ' . $output . "\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        if (isset($res->success) && !$res->success) {
            $this->log('Error: test ' . $event . ' message:' . $res->message);
            $this->err('Error: test ' . $event . ' message:' . $res->message);
            return false;
        }
        return true;
    }

    function zpl($package)
    {
        $item = Package::find($package->id);
        if (!$item) {
            return '';
        }
        $shipper = $item->warehouse_id ? $item->warehouse : ($item->country ? $item->country->warehouse : null);

        if ($shipper && !$shipper->country) {
            die("Warehouse doesn't have country.");
        }

        $item->updateCarrier();


        return view('admin.zpl', compact('item', 'shipper'))->render();
    }


    function packing_data($package, $test = false)
    {
        $this->code = '';
        $this->message = '';
        if (!$package) {
            $this->message = 'no package';
            $this->log('Error: no package for packing data');
            $this->err('Error: no package for packing data');
            return false;
        }
        if (!$package->ukr_express_id) {
            $this->message = 'no package ukr';
            $this->err('Error: no package ukr data for packing data');
            return false;
        }
        $user = $package->user;
        if (!$user) {
            $this->message = 'no user';
            $this->log('Error: no user for packing data');
            $this->err('Error: no user for packing data');
            return false;
        }
        if (!$user->ukr_express_id) {
            $this->message = 'no user ukr';
            $this->log('Error: no user ukr data for packing data');
            $this->err('Error: no user ukr data for packing data');
            return false;
        }
        /*$str="{\n";
	    $str.='  "print_url": "'.route('invoice',['id' => $package->id]).'"'."\n";
	    //$str.='  ,"zpl": "'.route('zpl',['id' => $package->id]).'"'."\n";
	    //$str.='  ,"zpl": "'.$this->zpl($package).'"'."\n";
	$str.="}\n";*/
        //echo $str;
        /*if (!$package->invoice) {
            $this->message = 'no invoice';
            $this->err('Error: no invoice for packing data');
            return false;
	}*/
        //$str= json_encode(array('print_url'=>route('invoice',['id' => $package->id]),'zpl'=>$this->zpl($package)));
        //$str= json_encode(array('print_url'=>$package->invoice,'zpl'=>$this->zpl($package)));
        $invoice = $package->generateHtmlInvoice();
        if (empty($invoice)) {
            $this->message = 'Cannot generate html invoice';
            $this->log('Error: cannot generate html invoice for packing data');
            $this->err('Error: cannot generate html invoice for packing data');
            return false;
        }
        $str = json_encode(array('print_url' => $invoice, 'zpl' => $this->zpl($package)));
        if ($test) {
            echo $str . "\n";
            return true;
        }
        //return true;

        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $user->ukr_express_id . '/tracking-numbers/' . $package->ukr_express_id . '/packing-data');
        //echo  'URL: '.$this->UE_BASE_URL.'/'.$this->div.'/customer/'.$user->ukr_express_id.'/tracking-numbers/'.$package->ukr_express_id.'/packing-data'."\n";
        //echo 'Invoice: '.$invoice."\n";
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        //curl_setopt($ch, CURLOPT_PUT, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        //print_r($res);
        if (empty($res)) {
            $this->message = 'Empty reponse';
            $this->log('Error: ' . $package->custom_id . ' packing data Empty Response');
            $this->err('Error: ' . $package->custom_id . ' packing data Empty Response');
            return false;
        }
        $this->res = $res;
        if ($res && is_array($res) && in_array('code', $res))
            $this->res = $res['code'];
        if ($res && is_array($res) && in_array('message', $res))
            $this->message = $res['message'];
        if (isset($res->success) && !$res->success) {
            $this->message = 'message:' . $res->message;
            $this->log('Error: ' . $package->custom_id . ' packing data ' . $this->message);
            $this->err('Error: ' . $package->custom_id . ' packing data ' . $this->message);
            return false;
        }
        $track = $this->track_get($package->ukr_express_id, $user->ukr_express_id);
        if ($track && isset($track->on_packing) && isset($track->on_packing->has_zpl) && isset($track->on_packing->has_print_url) && $track->on_packing->has_zpl && $track->on_packing->has_print_url) {
            return true;
        } else {
            $this->message = 'message: has_zpl or has_print_url is not set';
            $this->log('Error: ' . $package->custom_id . ' packing data ' . $this->message);
            $this->err('Error: ' . $package->custom_id . ' packing data ' . $this->message);
            return false;
        }
    }

    function checkup_close($checkup)
    {
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        $package = $checkup->package;
        if (!$package) {
            $this->message = 'Checkup has no package';
            return false;
        }
        if (!$package->user) {
            $this->message = 'Checkup package has no user';
            return false;
        }
        if (!$package->ukr_express_id) {
            $this->message = 'Checkup package is not in Ukr Express';
            return false;
        }
        if (!$package->user->ukr_express_id) {
            $this->message = 'Checkup user is not in Ukr Express';
            return false;
        }

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $package->user->ukr_express_id . '/tracking-numbers/' . $package->ukr_express_id . '/additional-services/checkup');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        $str = "";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        //echo $str."\n";
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        //print_r($res);
        $this->res = $res;
        if ($res && is_array($res) && in_array('code', $res))
            $this->res = $res['code'];
        if (isset($res->success) && !$res->success) {
            $this->message = 'message:' . $res->message;
            return false;
        }
        $checkup->processed = $res->additional_services->checkup->processed;
        return true;
    }

    function checkup_add($checkup)
    {
        $arr = array('description' => $checkup->description);

        $str = json_encode($arr);
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        $package = $checkup->package;
        if (!$package) {
            $this->message = 'Checkup has no package';
            return false;
        }
        if (!$package->user) {
            $this->message = 'Checkup package has no user';
            return false;
        }
        if (!$package->ukr_express_id) {
            $this->message = 'Checkup package is not in Ukr Express';
            return false;
        }
        if (!$package->user->ukr_express_id) {
            $this->message = 'Checkup user is not in Ukr Express';
            return false;
        }
        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $package->user->ukr_express_id . '/tracking-numbers/' . $package->ukr_express_id . '/additional-services/checkup');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        //echo $str."\n";
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);

       //print_r($res);
        $this->res = $res;
        if (isset($res->success) && !$res->success) {
            $this->message = 'message:' . $res->message;
            return false;
        }
        //if($res && is_array($res) && in_array('id',$res) && $res['id'])
        if ($res && isset($res->id) && $res->id)
            return true;
        $this->message = $output;
        return false;
    }

    function ticket_close($ticket)
    {
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/support/tickets/' . $ticket->ukr_express_id . '/close');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        $str = "";
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        //echo $str."\n";
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        //print_r($res);
        $this->res = $res;
        if ($res && is_array($res) && in_array('code', $res))
            $this->res = $res['code'];
        if (isset($res->success) && !$res->success) {
            $this->message = 'message:' . $res->message;
            return false;
        }
        //$this->id=$res->id;
        //$ticked->id=$res->id;
        //$ticket->number=$res->number;
        //$ticket->answer_required=$res->answer_required;
        $ticket->is_closed = $res->is_closed;
        //$ticket->created_timestamp = date('Y-m-d H:i:s',$res->created_timestamp);
        return true;
    }

    function ticket_conversation_list($ticket)
    {
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/support/tickets/' . $ticket->ukr_express_id . '/conversation');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        //$str='';
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        //echo $str."\n";
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        //print_r($res);
        $this->res = $res;
        if ($res && is_array($res) && in_array('code', $res))
            $this->res = $res['code'];
        if (isset($res->success) && !$res->success) {
            $this->message = 'message:' . $res->message;
            return null;
        }
        return $res;
    }

    function ticket_conversation_add($ticketConversation)
    {
        $arr = array('text' => $ticketConversation->text);

        $str = json_encode($arr);
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/support/tickets/' . $ticketConversation->ticket->ukr_express_id . '/conversation');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        //echo $str."\n";
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        //print_r($res);
        $this->res = $res;
        if ($res && is_array($res) && in_array('code', $res))
            $this->res = $res['code'];
        if (isset($res->success) && !$res->success) {
            $this->message = 'message:' . $res->message;
            return false;
        }
        $this->id = $res->id;
        //$ticketConversation->name=$res->name;
        $ticketConversation->type = $res->type;
        $ticketConversation->is_read = $res->is_read;
        $ticketConversation->created_timestamp = date('Y-m-d H:i:s', $res->created_timestamp);
        return true;
    }


    function ticket_add($ticket)
    {
        $arr = array('subject' => $ticket->subject, 'description' => $ticket->description);//,'linked_parcel_id'=>$ticket->linked_parcel_id,'linked_tracking_id'=>$ticket->linked_tracking_id);
        if ($ticket->linked_tracking_id)
            $arr['linked_tracking_id'] = $ticket->linked_tracking_id;
        if ($ticket->linked_parcel_id)
            $arr['linked_parcel_id'] = $ticket->linked_parcel_id;

        $str = json_encode($arr);
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/support/tickets');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        //echo $str."\n";
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        //print_r($res);
        $this->res = $res;
        if ($res && is_array($res) && in_array('code', $res))
            $this->res = $res['code'];
        if (isset($res->success) && !$res->success) {
            $this->message = 'message:' . $res->message;
            return false;
        }
        $this->id = $res->id;
        //$ticked->id=$res->id;
        $ticket->number = $res->number;
        $ticket->answer_required = $res->answer_required;
        $ticket->is_closed = $res->is_closed;
        $ticket->created_timestamp = date('Y-m-d H:i:s', $res->created_timestamp);
        return true;
    }

    function return($package, $cancel, $address = NULL, $note = NULL, $file = NULL)
    {
        if ($file) {

            $destinationPath = public_path('uploads/ue-parcel');
            $uniqueName = time() . '_' . $file->getClientOriginalName();
            $uniqueName = str_replace(' ', '', $uniqueName);

            $file->move($destinationPath, $uniqueName);

            $imageFullName = 'https://'.request()->getHost().'/uploads/ue-parcel/'.$uniqueName;


            $client = new Client();
            $imageUrl = $imageFullName;

            $response = $client->request('POST', 'http://partnersapi.ukraine-express.com/partners/v2/file-upload/generic-document-multipart', [
                'headers' => [
                    'Authorization' => 'Bearer 60b29738a2fd15426f0b62cb445986ad'
                ],
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($imageUrl, 'r'),
                        'filename' => basename($imageUrl)
                    ]
                ]
            ]);
            $responseBody = json_decode($response->getBody()->getContents(), true);
            $ukrImageId = $responseBody['id'];


            $package->ukr_express_image_id = $ukrImageId;
            $package->image_path = $imageFullName;
            $package->save();
        }

        $this->code = '';
        if (!$package) {
            $this->message = 'no package';
            $this->log('Error: no package for return');
            $this->err('Error: no package for retur');
            return false;
        }
        if (!$package->ukr_express_id) {
            $this->message = 'no package ukr';
            $this->log('Error: no package ukr data for return');
            $this->err('Error: no package ukr data for return');
            return false;
        }
        $user = $package->user;
        if (!$user) {
            $this->message = 'no user';
            $this->log('Error: no user for return');
            $this->err('Error: no user for return');
            return false;
        }
        if (!$user->ukr_express_id) {
            $this->message = 'no user ukr';
            $this->log('Error: no user ukr data for return');
            $this->err('Error: no user ukr data for return');
            return false;
        }
        $str = '';
        if (!$cancel)
            $str = json_encode(['address' => $address, 'note' => $note, 'return_label_type' => 'no', 'return_label_uploaded_file_id' => 0]);
        //echo $str."\n";

        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $user->ukr_express_id . '/tracking-numbers/' . $package->ukr_express_id . '/return');
        //echo $this->UE_BASE_URL.'/'.$this->div.'/customer/'.$user->ukr_express_id.'/tracking-numbers/'.$package->ukr_express_id.'/return'."\n";
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        if ($cancel)
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        //print_r($res);
        $this->res = $res;
        if ($res && is_array($res) && in_array('code', $res))
            $this->res = $res['code'];
        if (isset($res->success) && !$res->success) {
            $this->message = 'message:' . $res->message;
            $this->log('Error: ' . $package->custom_id . ' return ' . $this->message);
            $this->err('Error: ' . $package->custom_id . ' return ' . $this->message);
            return false;
        }
        return true;
    }

    function change_sending_permission($package, $is_allowed)
    {
        $this->code = '';
        $this->doLog = true;
        if (!$package) {
            $this->message = 'no package';
            $this->log('Error: no package for change sending permittion');
            $this->err('Error: no package for change sending permittion');
            return false;
        }
        if (!$package->ukr_express_id) {
            $this->message = 'no package ukr';
            $this->log('Error: no package ukr data for change sending permittion');
            $this->err('Error: no package ukr data for change sending permittion');
            return false;
        }
        $user = $package->user;
        if (!$user) {
            $this->message = 'no user';
            $this->log('Error: no user for change sending permittion');
            $this->err('Error: no user for change sending permittion');
            return false;
        }
        if (!$user->ukr_express_id) {
            $this->message = 'no user ukr';
            $this->log('Error: no user ukr data for change sending permittion');
            $this->err('Error: no user ukr data for change sending permittion');
            return false;
        }
        $str = '{"allowed":true}';
        if (!$is_allowed)
            $str = '{"allowed":false}';

        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $user->ukr_express_id . '/tracking-numbers/' . $package->ukr_express_id . '/sending-permission');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        //print_r($res);
        $this->res = $res;
        if ($res && is_array($res) && in_array('code', $res))
            $this->res = $res['code'];
        if (isset($res->success) && !$res->success) {
            $this->message = 'message:' . $res->message;
            $this->log('Error: ' . $package->custom_id . ' change sending permission ' . $this->message);
            $this->err('Error: ' . $package->custom_id . ' change sending permission ' . $this->message);
            return false;
        }
        return true;
    }

    function change_customer($package, $old_customer_id = NULL)
    {

        $this->code = '';
        if (!$package) {
            $this->message = 'no package';
            $this->log('Error: no package for change customer');
            $this->err('Error: no package for change customer');
            return false;
        }
        if (!$package->ukr_express_id) {
            $this->message = 'no package ukr';
            $this->log('Error: no package ukr data for change customer');
            $this->err('Error: no package ukr data for change customer');
            return false;
        }
        $user = $package->user;
        if (!$user) {
            $this->message = 'no user';
            $this->log('Error: no user for change customer');
            $this->err('Error: no user for change customer');
            return false;
        }
        if (!$user->ukr_express_id) {
            $this->message = 'no user ukr';
            $this->log('Error: no user ukr data for change customer');
            $this->err('Error: no user ukr data for change customer');
            return false;
        }
        if (!$old_customer_id) {
            $track = $this->track_get($package->ukr_express_id);
            if ($track)
                $old_customer_id = $track->customer_id;
            else
                return true;
        }
        if (!$old_customer_id) {
            $this->message = 'no old customer id';
            $this->log('Error: no old customer id for change customer');
            $this->err('Error: no old customer id for change customer');
            return false;
        }
        if ($old_customer_id && ($old_customer_id == $user->ukr_express_id)) {
            //$this->log("Already at customer:".$old_customer_id);
            return true;
        }
        $this->log('Changing customer for track ' . $package->tracking_code . ' from ' . $old_customer_id . ' to ' . $user->ukr_express_id);
        $str = '{"new_customer_id":' . $user->ukr_express_id . '}';

        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $old_customer_id . '/tracking-numbers/' . $package->ukr_express_id . '/change-customer');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        $output = curl_exec($ch);
	//echo "URL: ". $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $user->ukr_express_id . '/tracking-numbers/' . $package->ukr_express_id . '/change-customer'."\n";
	//echo "Data: ".$str."\n";
        //echo "Response: ".$output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        //print_r($res);
        $this->res = $res;
        if ($res && is_array($res) && in_array('code', $res))
            $this->res = $res['code'];
        if (isset($res->success) && !$res->success) {
            $this->message = 'message:' . $res->message;
            $this->log('Error: ' . $package->custom_id . ' change customer ' . ' from ' . $old_customer_id . ' to ' . $user->ukr_express_id . " " . $this->message);
            $this->err('Error: ' . $package->custom_id . ' change customer ' . ' from ' . $old_customer_id . ' to ' . $user->ukr_express_id . " " . $this->message);
            return false;
        }
        return true;
    }

    function declaration($package, $test = false)
    {
        $this->code = '';
        $this->message = '';
        if (!$package) {
            $this->message = 'no package';
            $this->log('Error: no package for declaration data');
            $this->err('Error: no package for declaration data');
            return false;
        }
        if (!$package->ukr_express_id) {
            $this->message = 'no package ukr';
            $this->log('Error: no package ukr data for declaration data');
            $this->err('Error: no package ukr data for declaration data');
            return false;
        }
        $user = $package->user;
        if (!$user) {
            $this->message = 'no user';
            $this->log('Error: no user for declaration data');
            $this->err('Error: no user for declaration data');
            return false;
        }
        if (!$user->ukr_express_id) {
            $this->message = 'no user ukr';
            $this->log('Error: no user ukr data for declaration data');
            $this->err('Error: no user ukr data for declaration data');
            return false;
        }
        $str = '';
        if ($package->goods && count($package->goods) > 1) {
            $arr = [];
            foreach ($package->goods as $good) {
                $number_items = $good->number_items;
                if (!$number_items) {
                    if (count($package->goods) == 1)
                        $number_items = $package->getNumberItems();
                    else
                        $number_items = 1;
                }
                if (!$number_items) {
                    $number_items = 1;
                }
                $arr[] = array('title_en' => $good->name_parent, 'quantity' => $number_items);
            }
            $str = json_encode($arr);
        } else {
	    $dtn= $package->detailed_type_number;
	    if(!$dtn) $dtn=1;
            $str = json_encode(array(array('title_en' => $package->detailed_type_parent_name, 'quantity' => $dtn)));
        }
        if ($test) {
            echo 'URL: ' . $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $user->ukr_express_id . '/tracking-numbers/' . $package->ukr_express_id . '/declaration' . "\n";
            echo $str . "\n";
            return true;
        }
        //echo "Data: ".$str."\n";
        //return true;

        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

//	   echo  'URL: '.$this->UE_BASE_URL.'/'.$this->div.'/customer/'.$user->ukr_express_id.'/tracking-numbers/'.$package->ukr_express_id.'/declaration'."\n";
//	   echo $str."\n";

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $user->ukr_express_id . '/tracking-numbers/' . $package->ukr_express_id . '/declaration');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        //curl_setopt($ch, CURLOPT_PUT, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        //print_r($res);
        if (empty($res)) {
            $this->message = 'Empty reponse';
            $this->log('Error: ' . $package->custom_id . ' declaration data Empty Response');
            $this->err('Error: ' . $package->custom_id . ' declaration data Empty Response');
            return false;
        }
        $this->res = $res;
        if ($res && is_array($res) && in_array('code', $res))
            $this->res = $res['code'];
        if ($res && is_array($res) && in_array('message', $res))
            $this->message = $res['message'];
        if (isset($res->success) && !$res->success) {
            $this->message = 'message:' . $res->message;
            $this->log('Error: ' . $package->custom_id . ' declaration data ' . $this->message);
            $this->err('Error: ' . $package->custom_id . ' declaration data ' . $this->message);
            return false;
        }
        return true;
    }

    function package_additional_info($package, $debug = false)
    {
        if (!$package->ukr_express_id) {
            return false;
        }
        $user = $package->user;
        if (!$user) {
            return false;
        }
        if (!$user->ukr_express_id) {
            return false;
        }
        $price = $package->getShippingAmountUSD();
        if (!$price) {
            $price = 0;
	}
        $price = intval($price * 100);
        $ch = curl_init();
        $str = "{\n";
        $str .= '    "declared_price_cents": ' . $price . "\n";
	if($package->otp_code)
            $str .= '    ,"otp_for_receiving": "' . $package->otp_code . '"'."\n";
        $str .= "}\n";
        if ($debug) {
            echo "URL: " . $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $user->ukr_express_id . '/tracking-numbers/'.$package->ukr_express_id.'/additional-info' . "\n";
            echo "DATA: " . $str;
        }

        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $user->ukr_express_id . '/tracking-numbers/'.$package->ukr_express_id.'/additional-info');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        $output = curl_exec($ch);
        if ($debug) {
            echo "RESPONSE: " . $output . "\n";
        }
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        //print_r($res);
        if ($res && is_array($res) && in_array('code', $res))
            $this->res = $res['code'];
	if (isset($res->success) && !$res->success) {
	    return false;
	}
	return true;
    }

    function package_add($package, $debug = false)
    {
        $user = $package->user;
        $this->id = null;
        $this->trackingNumber = null;

        if (!$user) {
            $this->message = 'user empty';
            $this->log('Error: ' . $package->custom_id . ' adding track ' . $this->message);
            $this->err('Error: ' . $package->custom_id . ' adding track ' . $this->message);
            return false;
        }
        if (!$user->ukr_express_id) {
            if (!$this->user_register($user)) {
                $this->message = 'cannot register user';
                $this->log('Error: ' . $package->custom_id . ' adding track ' . $this->message);
                $this->err('Error: ' . $package->custom_id . ' adding track ' . $this->message);
                return false;
            }
        }
        if (!$user->ukr_express_id) {
            $this->message = 'no user ukr customer_id';
            $this->log('Error: ' . $package->custom_id . ' adding track ' . $this->message);
            $this->err('Error: ' . $package->custom_id . ' adding track ' . $this->message);
            return false;
        }
        $track = $this->track_get_by_number($package->tracking_code, $user->ukr_express_id);
        if ($track && $track->id) {
            $this->id = $track->id;
            $this->message = 'Ok';
            $this->log('Ok: ' . $package->tracking_code . ' Already added ');
            return true;
        }

        $price = $package->getShippingAmountUSD();
        if (!$price) {
            /*if($debug) {
		echo "Declared price:".$price."\n";
	   } else {
	        $this->message='no declared price';
	        $this->log('Error: '.$package->custom_id.' adding track '.$this->message);
	        $this->err('Error: '.$package->custom_id.' adding track '.$this->message);
	   }*/
            $price = 0;
            //return false;
        }
        $price = intval($price * 100);

        $ch = curl_init();
        $str = "{\n";
        $str .= '  "number": "' . $package->tracking_code . '"' . "\n";
        $str .= '  ,"is_sending_allowed": "true"' . "\n";
        $str .= '  ,"delivery_type_tag": "airregular"' . "\n";
        $str .= '  ,"additional_info": {' . "\n";
        $str .= '    "seller_order_number": ""' . "\n";
        $str .= '    ,"seller_name": ""' . "\n";
        $str .= '    ,"customer_description": ""' . "\n";
        $str .= '    ,"declared_price_cents": ' . $price . "\n";
	if($package->otp_code)
            $str .= '    ,"otp_for_receiving": "' . $package->otp_code . '"'."\n";
        $str .= "  }\n";
        $str .= '  ,"on_packing": {' . "\n";
        $str .= '    "has_zpl": false' . "\n";
        $str .= '    ,"has_print_url": false' . "\n";
        $str .= "  }\n";
        $str .= "}\n";
        if ($debug) {
            echo "URL: " . $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $user->ukr_express_id . '/tracking-numbers/' . "\n";
            echo "DATA: " . $str;
        }

        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $user->ukr_express_id . '/tracking-numbers/');
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        $output = curl_exec($ch);
        if ($debug) {
            echo "RESPONSE: " . $output . "\n";
        }
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        //print_r($res);
        if ($res && is_array($res) && in_array('code', $res))
            $this->res = $res['code'];
        if (isset($res->success) && !$res->success && (strpos($res->message, 'Tracking number already registered') == 0)) {
            $this->log(' Changing customer ' . $package->custom_id . ' adding track ' . $this->message);
            $track = $this->track_find($package->tracking_code, $user->ukr_express_id);
            if ($track) {
                $this->trackingNumber = $track->number;
                $this->log(' Changing customer found track ' . $track->id);
                if ($track->customer_id == $user->ukr_express_id) {
                    $this->log(' Ok same customer_id');
                    $res->success = true;
                    $this->id = $track->id;
                    $res = $track;
                } else if (($track->number == $package->tracking_code) && $this->change_customer($package)) {
                    $this->log(' Ok customer_id changed');
                    $res->success = true;
                    $this->id = $track->id;
                    $res = $track;
                }
            }
        }
        if (isset($res->success) && !$res->success) {
            $this->message = 'message:' . $res->message;
            echo "URL: " . $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $user->ukr_express_id . '/tracking-numbers/' . "\n";
            echo "DATA: " . $str . "\n";
            echo "RESPONSE: " . $output . "\n";
            $this->log('Error: ' . $package->custom_id . ' adding track ' . $this->message);
            $this->err('Error: ' . $package->custom_id . ' adding track ' . $this->message);
            return false;
        }
        if (!isset($res->id) || !$res->id) {
            $this->message = 'message: ID Not Set';
            echo "URL: " . $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $user->ukr_express_id . '/tracking-numbers/' . "\n";
            echo "DATA: " . $str . "\n";
            echo "RESPONSE: " . $output . "\n";
            $this->log('Error: ' . $package->custom_id . ' adding track ' . $this->message);
            $this->err('Error: ' . $package->custom_id . ' adding track ' . $this->message);
            return false;
        }
        $this->id = $res->id;
        $this->message = 'Ok';
        $this->log('Ok: ' . $package->tracking_code . ' adding track ');
        return true;
    }

    function track_find($tracking_number, $customer_id)
    {
        if ($tracking_number && !empty($tracking_number)) {
            $tr_num = $tracking_number;
            do {
                $tracks = $this->track_list(0, 100, 'last_symbols=' . $tr_num);
                if ($tracks && is_array($tracks))
                    foreach ($tracks as $track) {
                        if (str_contains($track->number, $tr_num)) {
                            return $track;
                        }
                    }
                $tr_num = substr($tr_num, 1);
            } while (strlen($tr_num) >= 20);
        }
        if ($customer_id) {
            $tracks = $this->track_list(0, 100, '', $customer_id);
            foreach ($tracks as $track) {
                if (str_contains($track->number, $tracking_number)) {
                    return $track;
                }
            }
        }
        return NULL;
    }

    function user_register($user)
    {
        $ch = curl_init();
        $addr_list = preg_split('/\s+/', $user->address);
        $street = '';
        $home = '';
        $apt = '';
        $zip = $user->zip_code;

        $street_cnt = 0;
        if (count($addr_list) >= 1) {
            $from_i = 0;
            $to_i = count($addr_list) - 3;
            if ($to_i < 0) $to_i = 0;
            for ($i = $from_i; $i <= $to_i; $i++) {
                if (!empty($street)) $street .= ' ';
                $street .= $addr_list[$i];
                $street_cnt++;
            }
            $apt = $addr_list[count($addr_list) - 1];
        }
        if (count($addr_list) >= 3) {
            $apt = $addr_list[count($addr_list) - 1];
        }
        if (count($addr_list) >= 2) {
            $from_i = $street_cnt;
            $to_i = count($addr_list) - 2;
            for ($i = $from_i; $i <= $to_i; $i++) {
                if (!empty($home)) $street .= ' ';
                $street .= $addr_list[$i];
                $street_cnt++;
            }
        }
        $aseNum = substr($user->customer_id, 3, 5);
        if (empty($zip)) $zip = 'AZE0000';
        //if(empty($street)) $street='NONE';
        $street = a2l('Üzeyir Hacıbəyov, 61b');
        if (empty($home)) $home = '01';
        if (empty($apt)) $apt = '01';
        $usr = [];
        /*$str="{\n";
	    $str.='"first_name": "'.$user->name.'"'."\n";
	    $str.=',"last_name": "'.$user->surname.'"'."\n";
	    $str.=',"translit_first_name": "'.$user->name.'"'."\n";
	    $str.=',"translit_last_name": "'.$user->surname.'"'."\n";
	    //$str.=',"email": "'.$user->email.'"'."\n";
	    $str.=',"email": "'.$user->customer_id.'@aseshop.az'.'"'."\n";
	    //$str.=',"phone": "'.$user->phone.'"'."\n";
	    $str.=',"phone": "'.'9940000'.$aseNum.'"'."\n";
	    $str.=',"street": "'.$street.'"'."\n";
	    $str.=',"home": "'.$home.'"'."\n";
	    $str.=',"apt": "'.$apt.'"'."\n";
	    $str.=',"city": "'.$user->city_name.'"'."\n";
	    $str.=',"zip": "'.$zip.'"'."\n";
	    $str.=',"state": "'.$user->city_name.'"'."\n";
	    $str.=',"country": "Azerbaijan"'."\n";
	    $str.="}\n";*/
        $usr["first_name"] = $user->name;
        $usr["last_name"] = $user->surname;
        $usr["translit_first_name"] = $user->name;
        $usr["translit_last_name"] = $user->surname;
        if (!$user->ukr_express_id) {
            $usr["email"] = $user->customer_id . '@aseshop.az';
            $usr["phone"] = '9940000' . $aseNum;
        }
        $usr["street"] = $street;
        $usr["home"] = $home;
        $usr["apt"] = $apt;
        $usr["city"] = $user->city_name;
        $usr["zip"] = $zip;
        $usr["state"] = $user->city_name;
        $usr["country"] = "Azerbaijan";
        $str = json_encode($usr);
        /*$usr['first_name']=$user->name;
	$usr['last_name']=$user->surname;
	$usr['transit_first_name']=$user->name;
	$usr['transit_last_name']=$user->surname;
	$usr['email']=$user->email;
	$usr['email']=$user->email;
	$str= json_encode($usr);*/
        //echo $str;
        //return false;

        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);

        $user_code = str_replace('ASE', '', $user->customer_id);
        //$user_code=str_pad($user_code, 5, '0', STR_PAD_LEFT);

        $url = $this->UE_BASE_URL . '/register/' . $user_code;
        if ($user->ukr_express_id)
            $url = $this->UE_BASE_URL . '/' . $this->div . '/customer/' . $user->ukr_express_id;
        curl_setopt($ch, CURLOPT_URL, $url);
        echo "URL:" . $url . "\n";
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        if ($user->ukr_express_id)
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
        $output = curl_exec($ch);
        curl_close($ch);
	$output=$this->parse_output($output);
        $res = json_decode($output);
        if (isset($res->id)) {
            $ldate = date('Y-m-d H:i:s');
            //echo "res id:".$res->id."  ukr express id:".$user->ukr_express_id."\n";
            if (!$user->ukr_express_id)
                $user->ukr_express_id = $res->id;
            $user->ukr_express_update_at = $ldate;
            $user->save();
            return true;
        }
        $this->log($str);
        $this->log($output);
        $this->err("Error adding user " . $output);
        return false;
    }


    public function customers_all()
    {
        $ch = curl_init();
        if ($this->curlDebug)
            curl_setopt($ch, CURLOPT_VERBOSE, true);



        curl_setopt($ch, CURLOPT_URL, $this->UE_BASE_URL . '/' . $this->div . '/customers?limit=10000&offset=30000');
        //echo  'URL: '.$this->UE_BASE_URL.'/'.$this->div.'/customer/'.$user->ukr_express_id.'/tracking-numbers/'.$package->ukr_express_id.'/packing-data'."\n";
        //echo 'Invoice: '.$invoice."\n";
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        //curl_setopt($ch, CURLOPT_PUT, 1);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'access_token:' . $this->UE_KEY,
            "Content-Type: application/json"
        ));
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
        $output=$this->parse_output($output);
        $res = json_decode($output,true);




        return ($res);
    }

}

