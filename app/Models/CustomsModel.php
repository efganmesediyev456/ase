<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use stdClass;

class CustomsModel extends Model
{
    public $CM_BASE_URL;
    public $CM_PING_URL = "/api/v2/carriers/Ping";
    public $CM_CARRIERSPOSTS_URL = "/api/v2/carriers/carriersposts";
    public $CM_CARRIERS_URL = "/api/v2/carriers";
    public $CM_CARRIERS_DELETE_URL = "/api/v2/carriers";
    public $CM_CARRIERS_COMMERCIAL_URL = "/api/v2/carriers/commercial";
    public $CM_DECLARATIONS_URL = "/api/v2/carriers/declarations";
    public $CM_DELETED_DECLARATIONS_URL = "/api/v2/carriers/deleteddeclarations";
    public $CM_APPROVESEARCH_URL = "/api/v2/carriers/approvesearch";
    public $CM_ADDTOBOXES_URL = "/api/v2/carriers/addtoboxes";
    public $CM_CANCEL_DEPESH_URL = "/api/v2/carriers/canceldepesh";
    public $CM_CANCEL_ADDTOBOXES_URL = "/api/v2/carriers/canceladdtoboxes";
    public $CM_DEPESH_URL = "/api/v2/carriers/depesh";
    public $CM_GOODS_URL = "/api/v2/carriers/goodsgroupslist";
    public $CM_AIR_URL = "/api/v2/carriers/airwaybillpackages";
    public $CM_API_KEY_ASESHOP = "8CD0F430D478F8E1DFC8E1311B20031E3A669607";
    public $CM_API_KEY_ASE_EXPRESS = "8CD0F430D478F8E1DFC8E1311B20031E3A669607";

    public $curlDebug = false;

    public $dateFrom;
    public $dateTo;
    public $pinNumber;
    public $trackingNumber;
    public $lang = 'az';

    public $regNumber;

    public $airWaybill;
    public $depeshNumber;

    public $direction = 1;
    public $trackinG_NO;
    public $transP_COSTS;
    public $weighT_GOODS;
    public $quantitY_OF_GOODS;
    public $invoyS_PRICE;
    public $currencY_TYPE;
    public $fin;
    public $document_type;
    public $idxaL_NAME;
    public $idxaL_ADRESS;
    public $phone;
    public $ixraC_NAME;
    public $ixraC_ADRESS;
    public $goodS_TRAFFIC_FR;
    public $goodS_TRAFFIC_TO = "031";
    public $goodsList = [];
    public $errorStr = '';

    //public $goods_id;
    //public $name_of_goods;
    public $goods_idList = [];
    public $name_of_goodsList = [];

    public $errorMessage;
    public $validationError;

    public $retryCount = 0;
    public $retrySleep = 1;
    public $isCommercial = false;
    public $packagE_TYPE;
    public $isDeleted = false;

    public $c_posts = [];
    public $d_posts = [];

    public function __construct($ase_express = false)
    {
        $this->CM_BASE_URL = env('CUSTOMS_URL');
        $this->CM_API_KEY = $this->CM_API_KEY_ASESHOP;
        if ($ase_express) {
            $this->CM_API_KEY = $this->CM_API_KEY_ASE_EXPRESS;
        }
        parent::__construct();
    }

    function get_air_json_str()
    {
        $str = '{';
        $str .= '"airWaybill":' . '"' . $this->airWaybill . '"';
        if (!empty($this->depeshNumber)) {
            //$str .= ',"depeshNumber":"' . $this->depeshNumber . '"';
        }
        $str .= '}';
        return $str;
    }

    function get_declarations_json_str()
    {
        $str = '{';
        $str .= '"trackingNumber":' . '"' . $this->trackingNumber . '"';
        if (!empty($this->packagE_TYPE)) {
            $str .= ',"packagE_TYPE":"' . $this->packagE_TYPE . '"';
        } else {
            if ($this->isCommercial) {
                $str .= ',"packagE_TYPE":"2"';
            } else {
                $str .= ',"packagE_TYPE":"1"';
            }
        }
        if (!empty($this->dateFrom)) {
            $str .= ',"dateFrom":"' . $this->dateFrom . '"';
        }
        if (!empty($this->dateTo)) {
            $str .= ',"dateTo":"' . $this->dateTo . '"';
        }
        if (!empty($this->status)) {
            $str .= ',"status":"' . $this->status . '"';
        }
        $str .= '}';
        //echo $str;
        return $str;
    }

    function get_declarations2_json_str()
    {
        $str = '{';
        if (!empty($this->dateFrom)) {
            $str .= '"dateFrom":"' . $this->dateFrom . '"';
        }
        if (!empty($this->dateTo)) {
            $str .= ',"dateTo":"' . $this->dateTo . '"';
        }
        $str .= '}';
        return $str;
    }

    function get_carriersposts_url()
    {
        if ($this->isCommercial) {
            return $this->CM_BASE_URL . $this->CM_CARRIERS_COMMERCIAL_URL;
        } else {
            return $this->CM_BASE_URL . $this->CM_CARRIERS_URL;
        }
    }

    function get_carriersposts_json_str()
    {
        $str = '{';
        $str .= '"trackingNumber":' . '"' . $this->trackingNumber . '"';
        if ($this->status)
            $str .= ',"status":"' . $this->status . '"';
        if ($this->isCommercial) {
            $str .= ',"packagE_TYPE":"2"';
        } else {
            $str .= ',"packagE_TYPE":"1"';
        }
        //$str .= ',"packagE_TYPE":"1"';
        //$str .= ',"packagE_TYPE":"Posts"';
        $str .= '}';
        return $str;
    }

    function get_carriersposts2_json_str()
    {
        //curl_setopt($ch, CURLOPT_POSTFIELDS, '{"dateFrom": "2022-06-05 19:22:48.085Z","dateTo": "2022-06-20 19:22:48.085Z","packagE_TYPE":"2","status":3}');
        $str = '{';
        $str .= '"status":' . $this->status;
        if ($this->trackingNumber) {
            $str .= ',"trackingNumber":' . '"' . $this->trackingNumber . '"';
        }
        /*if ($this->isCommercial) {
            $str .= ',"packagE_TYPE":"2"';
        } else {
            $str .= ',"packagE_TYPE":"1"';
	}*/
        if ($this->isCommercial) {
            $str .= ',"packageE_TYPE":"CommercialPosts"';
        } else {
            $str .= ',"packagE_TYPE":"Posts"';
        }
        if (!empty($this->dateFrom)) {
            $str .= ',"dateFrom":"' . $this->dateFrom . '"';
        }
        if (!empty($this->dateTo)) {
            $str .= ',"dateTo":"' . $this->dateTo . '"';
        }
        $str .= '}';
        return $str;
    }

    function get_carriers_html_str()
    {
        $str = "";
        $str .= '    "direction":' . $this->direction . ',' . "\n";
        $str .= '    "trackinG_NO":"' . $this->trackinG_NO . '",' . "\n";
        $str .= '    "transP_COSTS":' . $this->transP_COSTS . ',' . "\n";
        $str .= '    "weighT_GOODS":' . $this->weighT_GOODS . ',' . "\n";
        $str .= '    "quantitY_OF_GOODS":' . $this->quantitY_OF_GOODS . ',' . "\n";
        $str .= '    "invoyS_PRICE":' . $this->invoyS_PRICE . ',' . "\n";
        $str .= '    "currencY_TYPE":"' . $this->currencY_TYPE . '",' . "\n";
        $str .= '    "fin":"' . $this->fin . '",' . "\n";
        $str .= '    "document_type":"' . $this->document_type . '",' . "\n";
        $str .= '    "idxaL_NAME":"' . $this->idxaL_NAME . '",' . "\n";
        $str .= '    "idxaL_ADRESS":"' . $this->idxaL_ADRESS . '",' . "\n";
        $str .= '    "phone":"' . $this->phone . '",' . "\n";
        $str .= '    "ixraC_NAME":"' . $this->ixraC_NAME . '",' . "\n";
        $str .= '    "ixraC_ADRESS":"' . $this->ixraC_ADRESS . '",' . "\n";
        $str .= '    "goodS_TRAFFIC_FR":"' . $this->goodS_TRAFFIC_FR . '",' . "\n";
        $str .= '    "goodS_TRAFFIC_TO":"' . $this->goodS_TRAFFIC_TO . '",' . "\n";
        $str .= '    "goods_id": ' . $this->goods_id . ',' . "\n";
        $str .= '    "name_of_goods": "' . $this->name_of_goods . '"' . "\n";
        if ($this->isCommercial) {
            $str .= '    "voen":"' . $this->voen . '",' . "\n";
            $str .= '    "airwaybill":"' . $this->airwaybill . '",' . "\n";
            $str .= '    "depesH_NUMBER":"' . $this->depesH_NUMBER . '",' . "\n";
        }
        return $str;
    }

    function get_carriers_goods_from_arr($arr)
    {
        $this->goods_idList = [];
        $this->name_of_goodsList = [];
        foreach ($arr as $key => $type) {
            $query = "select concat(coalesce(pct.name_az,pct.name_en,pct.name_ru),' / ',coalesce(ct.name_az,ct.name_en,ct.name_ru)) as type_name ";
            $query .= '       from customs_types ct';
            $query .= '       left outer join customs_types pct on pct.id=ct.parent_id';
            $query .= "	     where ct.id=" . $type;
            $types = DB::select($query);
            if (count($types) > 0) {
                $custom_good_id = $type;
                $type_name = $types[0]->type_name;
                if (!empty($custom_good_id) && !empty($type_name)) {
                    $this->goods_idList[] = $custom_good_id;
                    $this->name_of_goodsList[] = $types[0]->type_name;
                }
            }
        }

        if (count($this->goods_idList) <= 0) {
            $this->goods_idList[] = 1;
            $this->name_of_goodsList[] = 'Geyim';
        }
    }

    function get_goods_noid($items)
    {

        $this->goods_idList = [];
        $this->name_of_goodsList = [];
        $symbol = [',', ';', '.', ':', '!', '?', '"', "'", '(', ')', '[', ']', '{', '}', '<', '>', '/', '\\', '|', '@', '#', '$', '%', '^', '&', '*', '+', '=', '~', '`'];
        foreach ($items as $item) {
            if ($item->ru_type_id && $item->ru_type && $item->ru_type->name_ru) {
                $this->goods_idList[] = 0;
                $cleanedName = str_replace($symbol, '', $item->ru_type->name_ru);
                $cleanedName = substr($cleanedName, 0, 490);
                $this->name_of_goodsList[] = $cleanedName;
            }
        }
        if (count($this->goods_idList) <= 0) {
            $this->goods_idList[] = 0;
            $this->name_of_goodsList[] = '-';
        }
    }


    function get_goods_noid_taobao($items)
    {
//        $this->goods_idList = [];
//        $this->name_of_goodsList = [];
//        $symbol = [',', ';', '.', ':', '!', '?', '"', "'", '(', ')', '[', ']', '{', '}', '<', '>', '/', '\\', '|', '@', '#', '$', '%', '^', '&', '*', '+', '=', '~', '`'];
//
//        foreach ($items as $item) {
//            if ($item->ru_type_id && $item->ru_type && $item->ru_type->name_ru) {
//                $this->goods_idList[] = 0;
//                $cleanedName = str_replace($symbol, '', $item->ru_type->name_ru);
//                $cleanedName = substr($cleanedName, 0, 490);
//                $this->name_of_goodsList[] = $cleanedName;
//            }
//        }
//
//        if (count($this->goods_idList) <= 0) {
//            $this->goods_idList[] = 0;
//            $this->name_of_goodsList[] = '-';
//        }

        $this->goods_idList = [];
        $this->name_of_goodsList = [];
        $symbol = [',', ';', '.', ':', '!', '?', '"', "'", '(', ')', '[', ']', '{', '}', '<', '>', '/', '\\', '|', '@', '#', '$', '%', '^', '&', '*', '+', '=', '~', '`'];

        $this->goods_idList[] = 0;
        $cleanedName = str_replace($symbol, '', $items->detailed_type);
        $cleanedName = substr($cleanedName, 0, 490);
        $this->name_of_goodsList[] = $cleanedName;
    }

    function get_goods_noid_ozon($items)
    {
        $this->goods_idList = [];
        $this->name_of_goodsList = [];
        $symbol = [',', ';', '.', ':', '!', '?', '"', "'", '(', ')', '[', ']', '{', '}', '<', '>', '/', '\\', '|', '@', '#', '$', '%', '^', '&', '*', '+', '=', '~', '`'];

        $this->goods_idList[] = 0;
        $cleanedName = str_replace($symbol, '', $items->detailed_type);
        $cleanedName = substr($cleanedName, 0, 490);
        $this->name_of_goodsList[] = $cleanedName;

//        $this->goods_idList = [];
//        $this->name_of_goodsList = [];
//        $symbol = [',', ';', '.', ':', '!', '?', '"', "'", '(', ')', '[', ']', '{', '}', '<', '>', '/', '\\', '|', '@', '#', '$', '%', '^', '&', '*', '+', '=', '~', '`'];
//
//        foreach ($items as $item) {
//            if ($item->ru_type_id && $item->ru_type && $item->ru_type->name_ru) {
//                $this->goods_idList[] = 0;
//                $cleanedName = str_replace($symbol, '', $item->ru_type->name_ru);
//                $cleanedName = substr($cleanedName, 0, 490);
//                $this->name_of_goodsList[] = $cleanedName;
//            }
//        }
//
//        if (count($this->goods_idList) <= 0) {
//            $this->goods_idList[] = 0;
//            $this->name_of_goodsList[] = '-';
//        }

    }


    function get_carriers_goods($customsTypeId, $typeId, $typeStr, $packageId)
    {
        $this->goods_idList = [];
        $this->name_of_goodsList = [];

        $query = 'select ';
        $query .= 'case when ct.id is not null then ct.id';
        $query .= ' when rt.id is not null then rt.customs_type_id';
        $query .= ' when pt.id is not null then pt.customs_good_id';
        $query .= ' end as customs_good_id';
        $query .= ",case when ct.id is not null then  concat(coalesce(pct.name_az,pct.name_en,pct.name_ru),' / ',coalesce(ct.name_az,ct.name_en,ct.name_ru))";
        $query .= ' when rt.id is not null then coalesce(rt.name_az,rt.name_en,rt.name_ru)';
        $query .= ' when pt.id is not null then coalesce(ptt_az.name,ptt_en.name,ptt_ru.name)';
        $query .= ' end as type_name ';
        $query .= '       from package_goods pg';
        $query .= '       left outer join ru_types rt on pg.ru_type_id=rt.id';
        $query .= '	left outer join package_types pt on pg.type_id=pt.id';
        $query .= '	left outer join customs_types ct on pg.customs_type_id=ct.id';
        $query .= '       left outer join customs_types pct on pct.id=ct.parent_id';
        $query .= "       left outer join package_type_translations ptt_az on (ptt_az.locale='az' and ptt_az.package_type_id=pt.id)";
        $query .= "       left outer join package_type_translations ptt_ru on (ptt_ru.locale='ru' and ptt_ru.package_type_id=pt.id)";
        $query .= "       left outer join package_type_translations ptt_en on (ptt_en.locale='en' and ptt_en.package_type_id=pt.id)";
        $query .= '       where pg.package_id=' . $packageId . ' and pg.deleted_at is NULL';
        //echo $query."\n";
        $items = DB::select($query);
        foreach ($items as $item) {
            $customs_good_id = $item->customs_good_id;
            $type_name = $item->type_name;
            if (!empty($customs_good_id) && !empty($type_name)) {
                $this->goods_idList[] = $customs_good_id;
                $this->name_of_goodsList[] = $type_name;
            }
        }

        if (!empty($typeId) && (count($this->goods_idList) <= 0)) {
            $query = 'select pt.customs_good_id,coalesce(ptt_az.name,ptt_en.name,ptt_ru.name) as type_name ';
            $query .= '       from package_types pt';
            $query .= "       left outer join package_type_translations ptt_az on (ptt_az.locale='az' and ptt_az.package_type_id=pt.id)";
            $query .= "       left outer join package_type_translations ptt_ru on (ptt_ru.locale='ru' and ptt_ru.package_type_id=pt.id)";
            $query .= "       left outer join package_type_translations ptt_en on (ptt_en.locale='en' and ptt_en.package_type_id=pt.id)";
            $query .= "	     where pt.id=" . $typeId;
            $types = DB::select($query);
            if (count($types) > 0) {
                $custom_good_id = $types[0]->customs_good_id;
                $type_name = $types[0]->type_name;
                if (!empty($custom_good_id) && !empty($type_name)) {
                    $this->goods_idList[] = $custom_good_id;
                    $this->name_of_goodsList[] = $types[0]->type_name;
                }
            }
        }

        if (!empty($customsTypeId) && (count($this->goods_idList) <= 0)) {
            $query = "select concat(coalesce(pct.name_az,pct.name_en,pct.name_ru),' / ',coalesce(ct.name_az,ct.name_en,ct.name_ru)) as type_name ";
            $query .= '       from customs_types ct';
            $query .= '       left outer join customs_types pct on pct.id=ct.parent_id';
            $query .= "	     where ct.id=" . $customsTypeId;
            $types = DB::select($query);
            if (count($types) > 0) {
                $custom_good_id = $customsTypeId;
                $type_name = $types[0]->type_name;
                if (!empty($custom_good_id) && !empty($type_name)) {
                    $this->goods_idList[] = $custom_good_id;
                    $this->name_of_goodsList[] = $types[0]->type_name;
                }
            }
        }

        if (!empty($typeStr) && (count($this->goods_idList) <= 0)) {
            $items = explode(";", $typeStr);
            foreach ($items as $item) {
                $items2 = explode(" x ", $item);
                $TypeStr = strtoupper($items2[count($items2) - 1]);
                $query = 'select pt.customs_good_id,coalesce(ptt_az.name,ptt_en.name,ptt_ru.name) as type_name ';
                $query .= '       from package_types pt';
                $query .= "       left outer join package_type_translations ptt_az on (ptt_az.locale='az' and ptt_az.package_type_id=pt.id)";
                $query .= "       left outer join package_type_translations ptt_ru on (ptt_ru.locale='ru' and ptt_ru.package_type_id=pt.id)";
                $query .= "       left outer join package_type_translations ptt_en on (ptt_en.locale='en' and ptt_en.package_type_id=pt.id)";
                $query .= "       where (upper('" . str_replace("'", "''", $TypeStr) . "') = upper(ptt_en.name))";
                $query .= "       or (upper('" . str_replace("'", "''", $TypeStr) . "') = upper(ptt_az.name))";
                $query .= "       or (upper('" . str_replace("'", "''", $TypeStr) . "') = upper(ptt_ru.name)) ";
                $query .= '       limit 1';
                $types = DB::select($query);
                if (count($types) > 0) {
                    $custom_good_id = $types[0]->customs_good_id;
                    $type_name = $types[0]->type_name;
                    if (!empty($custom_good_id) && !empty($type_name)) {
                        $this->goods_idList[] = $types[0]->customs_good_id;
                        $this->name_of_goodsList[] = $types[0]->type_name;
                    }
                }
            }
        }


        if (count($this->goods_idList) <= 0) {
            $this->goods_idList[] = 0;
            $this->name_of_goodsList[] = '-';
        }
    }

    function get_track_goods($customsTypeId, $typeId, $typeStr, $trackId)
    {
        $this->goods_idList = [];
        $this->name_of_goodsList = [];

        $query = 'select ';
        $query .= 'case when ct.id is not null then ct.id';
        $query .= ' when rt.id is not null then rt.customs_type_id';
        $query .= ' when pt.id is not null then pt.customs_good_id';
        $query .= ' end as customs_good_id';
        $query .= ",case when ct.id is not null then  concat(coalesce(pct.name_az,pct.name_en,pct.name_ru),' / ',coalesce(ct.name_az,ct.name_en,ct.name_ru))";
        $query .= ' when rt.id is not null then coalesce(rt.name_az,rt.name_en,rt.name_ru)';
        $query .= ' when pt.id is not null then coalesce(ptt_az.name,ptt_en.name,ptt_ru.name)';
        $query .= ' end as type_name ';
        $query .= '       from package_goods pg';
        $query .= '       left outer join ru_types rt on pg.ru_type_id=rt.id';
        $query .= '	left outer join package_types pt on pg.type_id=pt.id';
        $query .= '	left outer join customs_types ct on pg.customs_type_id=ct.id';
        $query .= '       left outer join customs_types pct on pct.id=ct.parent_id';
        $query .= "       left outer join package_type_translations ptt_az on (ptt_az.locale='az' and ptt_az.package_type_id=pt.id)";
        $query .= "       left outer join package_type_translations ptt_ru on (ptt_ru.locale='ru' and ptt_ru.package_type_id=pt.id)";
        $query .= "       left outer join package_type_translations ptt_en on (ptt_en.locale='en' and ptt_en.package_type_id=pt.id)";
        $query .= '       where pg.package_id=' . $packageId . ' and pg.deleted_at is NULL';
        //echo $query."\n";
        $items = DB::select($query);
        foreach ($items as $item) {
            $customs_good_id = $item->customs_good_id;
            $type_name = $item->type_name;
            if (!empty($customs_good_id) && !empty($type_name)) {
                $this->goods_idList[] = $customs_good_id;
                $this->name_of_goodsList[] = $type_name;
            }
        }

        if (!empty($typeId) && (count($this->goods_idList) <= 0)) {
            $query = 'select pt.customs_good_id,coalesce(ptt_az.name,ptt_en.name,ptt_ru.name) as type_name ';
            $query .= '       from package_types pt';
            $query .= "       left outer join package_type_translations ptt_az on (ptt_az.locale='az' and ptt_az.package_type_id=pt.id)";
            $query .= "       left outer join package_type_translations ptt_ru on (ptt_ru.locale='ru' and ptt_ru.package_type_id=pt.id)";
            $query .= "       left outer join package_type_translations ptt_en on (ptt_en.locale='en' and ptt_en.package_type_id=pt.id)";
            $query .= "	     where pt.id=" . $typeId;
            $types = DB::select($query);
            if (count($types) > 0) {
                $custom_good_id = $types[0]->customs_good_id;
                $type_name = $types[0]->type_name;
                if (!empty($custom_good_id) && !empty($type_name)) {
                    $this->goods_idList[] = $custom_good_id;
                    $this->name_of_goodsList[] = $types[0]->type_name;
                }
            }
        }

        if (!empty($customsTypeId) && (count($this->goods_idList) <= 0)) {
            $query = "select concat(coalesce(pct.name_az,pct.name_en,pct.name_ru),' / ',coalesce(ct.name_az,ct.name_en,ct.name_ru)) as type_name ";
            $query .= '       from customs_types ct';
            $query .= '       left outer join customs_types pct on pct.id=ct.parent_id';
            $query .= "	     where ct.id=" . $customsTypeId;
            $types = DB::select($query);
            if (count($types) > 0) {
                $custom_good_id = $customsTypeId;
                $type_name = $types[0]->type_name;
                if (!empty($custom_good_id) && !empty($type_name)) {
                    $this->goods_idList[] = $custom_good_id;
                    $this->name_of_goodsList[] = $types[0]->type_name;
                }
            }
        }

        if (!empty($typeStr) && (count($this->goods_idList) <= 0)) {
            $items = explode(";", $typeStr);
            foreach ($items as $item) {
                $items2 = explode(" x ", $item);
                $TypeStr = strtoupper($items2[count($items2) - 1]);
                $query = 'select pt.customs_good_id,coalesce(ptt_az.name,ptt_en.name,ptt_ru.name) as type_name ';
                $query .= '       from package_types pt';
                $query .= "       left outer join package_type_translations ptt_az on (ptt_az.locale='az' and ptt_az.package_type_id=pt.id)";
                $query .= "       left outer join package_type_translations ptt_ru on (ptt_ru.locale='ru' and ptt_ru.package_type_id=pt.id)";
                $query .= "       left outer join package_type_translations ptt_en on (ptt_en.locale='en' and ptt_en.package_type_id=pt.id)";
                $query .= "       where (upper('" . str_replace("'", "''", $TypeStr) . "') = upper(ptt_en.name))";
                $query .= "       or (upper('" . str_replace("'", "''", $TypeStr) . "') = upper(ptt_az.name))";
                $query .= "       or (upper('" . str_replace("'", "''", $TypeStr) . "') = upper(ptt_ru.name)) ";
                $query .= '       limit 1';
                $types = DB::select($query);
                if (count($types) > 0) {
                    $custom_good_id = $types[0]->customs_good_id;
                    $type_name = $types[0]->type_name;
                    if (!empty($custom_good_id) && !empty($type_name)) {
                        $this->goods_idList[] = $types[0]->customs_good_id;
                        $this->name_of_goodsList[] = $types[0]->type_name;
                    }
                }
            }
        }


        if (count($this->goods_idList) <= 0) {
            $this->goods_idList[] = 0;
            $this->name_of_goodsList[] = '-';
        }
    }

    function get_carriers_weight_json_str()
    {
        $str = "[\n";
        $str .= "{\n";
        $str .= '    "trackinG_NO":"' . $this->trackingNumber . '",' . "\n";
        $str .= '    "airwaybill":"' . $this->airwaybill . '",' . "\n";
        $str .= '    "depesH_NUMBER":"' . $this->depesH_NUMBER . '"' . ',' . "\n";
        $str .= '    "quantitY_OF_GOODS":' . $this->quantitY_OF_GOODS . ',' . "\n";
        $str .= '    "weighT_GOODS":"' . $this->weighT_GOODS . '"' . ',' . "\n";
        if ($this->goods_idList && count($this->goods_idList) > 0) {
            $str .= '    "goodslist": [' . "\n";
            for ($i = 0; $i <= count($this->goods_idList) - 1; $i++) {
                if ($i > 0) {
                    $str .= ",\n";
                }
                $str .= '    {' . "\n";
                $str .= '    "goods_id": ' . $this->goods_idList[$i] . ',' . "\n";
                $str .= '    "name_of_goods": "' . $this->name_of_goodsList[$i] . '"' . "\n";
                $str .= '    }' . "\n";
            }
            $str .= '    ]' . "\n";
        }
        $str .= "}\n";
        $str .= "]\n";
        return $str;
    }

    function get_carriers_invoice_json_str()
    {
        $str = "[\n";
        $str .= "{\n";
        $str .= '    "trackinG_NO":"' . $this->trackingNumber . '",' . "\n";
        $str .= '    "invoyS_USD":' . $this->invoyS_PRICE . "\n";
        //$str .= '    "invoyS_PRICE":' . $this->invoyS_PRICE . "\n";
        $str .= "}\n";
        $str .= "]\n";
        return $str;
    }


    function get_carriers_update_json_str()
    {
        $str = "[\n";
        $str .= "{\n";
        $str .= '    "trackinG_NO":"' . $this->trackingNumber . '",' . "\n";
        $str .= '    "airwaybill":"' . $this->airwaybill . '",' . "\n";
        $str .= '    "depesH_NUMBER":"' . $this->depesH_NUMBER . '"' . "\n";
        //$str.='    "weighT_GOODS":"'.$this->weighT_GOODS.'"'."\n";
        $str .= "}\n";
        $str .= "]\n";
        return $str;
    }

    function get_carriers_json_str()
    {
        $str = "[\n";
        $str .= "{\n";
        $str .= '    "direction":' . $this->direction . ',' . "\n";
        $str .= '    "trackinG_NO":"' . $this->trackinG_NO . '",' . "\n";
        if ($this->fin == '1VW0L50') {
        } else {
        }
        $str .= '    "transP_COSTS":' . $this->transP_COSTS . ',' . "\n";
        $str .= '    "weighT_GOODS":' . $this->weighT_GOODS . ',' . "\n";
        $str .= '    "quantitY_OF_GOODS":' . $this->quantitY_OF_GOODS . ',' . "\n";
        $str .= '    "invoyS_PRICE":' . $this->invoyS_PRICE . ',' . "\n";
        $str .= '    "currencY_TYPE":"' . $this->currencY_TYPE . '",' . "\n";
        $str .= '    "fin":"' . $this->fin . '",' . "\n";
        $str .= '    "document_type":"' . $this->document_type . '",' . "\n";
        $str .= '    "idxaL_NAME":"' . $this->idxaL_NAME . '",' . "\n";
        $str .= '    "idxaL_ADRESS":"' . $this->idxaL_ADRESS . '",' . "\n";
        $str .= '    "phone":"' . $this->phone . '",' . "\n";
        if ($this->isCommercial) {
            $str .= '    "voen":"' . $this->voen . '",' . "\n";
            $str .= '    "airwaybill":"' . $this->airwaybill . '",' . "\n";
            $str .= '    "depesH_NUMBER":"' . $this->depesH_NUMBER . '",' . "\n";
        }
        $str .= '    "ixraC_NAME":"' . $this->ixraC_NAME . '",' . "\n";
        $str .= '    "ixraC_ADRESS":"' . $this->ixraC_ADRESS . '",' . "\n";
        $str .= '    "goodS_TRAFFIC_FR":"' . $this->goodS_TRAFFIC_FR . '",' . "\n";
        $str .= '    "goodS_TRAFFIC_TO":"' . $this->goodS_TRAFFIC_TO . '",' . "\n";
        $str .= '    "goodslist": [' . "\n";
        for ($i = 0; $i <= count($this->goods_idList) - 1; $i++) {
            if ($i >= 40) {
                break;
            }
            if ($i > 0) {
                $str .= ",\n";
            }

            $dangerousKeywords = [
                'SELECT', 'INSERT', 'UPDATE', 'DELETE', 'DROP', 'TRUNCATE',
                'ALTER', 'CREATE', 'REPLACE', 'EXEC', 'UNION', 'WHERE'
            ];
            $nameOfGoods = $this->name_of_goodsList[$i];
            $sanitizedInput = preg_replace_callback('/\b(' . implode('|', $dangerousKeywords) . ')\b/i', function ($matches) {
                return str_repeat('', strlen($matches[0]));
            }, $nameOfGoods);

            $this->name_of_goodsList[$i] = $sanitizedInput;

            $str .= '    {' . "\n";
            $str .= '    "goods_id": ' . $this->goods_idList[$i] . ',' . "\n";
            $str .= '    "name_of_goods": "' . str_replace('"', '\"', $this->name_of_goodsList[$i]) . '"' . "\n";
            $str .= '    }' . "\n";
        }
        $str .= '    ]' . "\n";
        $str .= "}\n";
        $str .= "]\n";
        if ($this->fin == '1VW0L50') {
        } else {
        }

        return $str;
    }

    function conn_init()
    {
        $ch = curl_init();

        if ($this->curlDebug) {
            curl_setopt($ch, CURLOPT_VERBOSE, true);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, 'curl/7.58.0');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        return $ch;
    }

    function ping()
    {
        $ch = $this->conn_init();

        curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_PING_URL);
        //curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'lang: ' . $this->lang,
            'ApiKey: ' . $this->CM_API_KEY
        ));

        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
    }

    function declarations()
    {
        $ch = $this->conn_init();

        if ($this->isDeleted) {
            curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_DELETED_DECLARATIONS_URL . '/0/1');
        } else {
            curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_DECLARATIONS_URL . '/0/1');
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'lang: ' . $this->lang,
            'ApiKey: ' . $this->CM_API_KEY,
            //"Content-Type: application/json-patch+json",
            "Content-Type: application/json",
            "Connection: close"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_declarations_json_str());
        //echo $str."\n";
        $output = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($output);
        return $res;
    }

    function carriersposts2()
    {

        $ch = $this->conn_init();

        $offset = 0;
        $limit = 100;
        $arr = [];

        $retryCount = 0;
        while (true) {

            //echo "URL:" . $this->CM_BASE_URL . $this->CM_CARRIERSPOSTS_URL . '/' . $offset . '/' . $limit . "\n";
            curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_CARRIERSPOSTS_URL . '/' . $offset . '/' . $limit);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'accept: text/plain',
                'lang: ' . $this->lang,
                'ApiKey: ' . $this->CM_API_KEY,
                //"Content-Type: application/json-patch+json"
                "Content-Type: application/json"
            ));
            //curl_setopt($ch, CURLOPT_POSTFIELDS, '{"dateFrom": "2022-06-05 19:22:48.085Z","dateTo": "2022-06-20 19:22:48.085Z","packagE_TYPE":"2","status":3}');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_carriersposts2_json_str());
            //echo $this->get_carriersposts2_json_str() . "\n";
            $output = curl_exec($ch);
            //echo $output."\n";
            if (empty($output)) {›
                $retryCount++;
                if ($retryCount > $this->retryCount) {
                    break;
                }
                sleep($this->retrySleep);
                continue;
            }
            $res = json_decode($output);
            if (!isset($res->code) || $res->code == "400") {
                $retryCount++;
                if ($retryCount > $this->retryCount) {
                    break;
                }
                sleep($this->retrySleep);
                continue;
            }
            $this->parse_error($res);
            if (isset($res->data) && is_array($res->data) && count($res->data) > 0) {
                $cnt = count($res->data);
                //echo $cnt . "\n";
                $arr = array_merge($arr, $res->data);
                if ($cnt < $limit) {
                    break;
                }
            } else {
                echo $output . "\n";
                break;
            }
            $offset += $limit;
            sleep($this->retrySleep);
            $retryCount = 0;
        }
        curl_close($ch);
        return $arr;
    }

    function air()
    {
        $ch = $this->conn_init();
        $arr = [];
        $res = null;

        $retryCount = 0;

        curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_AIR_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'lang: ' . $this->lang,
            'ApiKey: ' . $this->CM_API_KEY,
            "Content-Type: application/json"
        ));

        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_air_json_str());
        $output = curl_exec($ch);
        if (!empty($output)) {
            $res = json_decode($output);
            $this->parse_error($res);
        }
        while ((empty($output) || !(isset($res->data) && is_array($res->data) && count($res->data) > 0)) && $retryCount < $this->retryCount) {
            $retryCount++;
            if (empty($output)) {
                echo "Error: empty response\n";
            } else {
                echo "Error: " . $output . "\n";
            }
            sleep($this->retrySleep);
            //echo "retrying...\n";
            $output = curl_exec($ch);
            if (!empty($output)) {
                $res = json_decode($output);
                $this->parse_error($res);
            }
        }
        curl_close($ch);
        if (empty($output) || !(isset($res->data) && isset($res->data->packages) && is_array($res->data->packages) && count($res->data->packages) > 0)) {
            if (empty($output)) {
                echo "Error: empty response\n";
            } else {
                echo "Error: " . $output . "\n";
            }
            return null;//$arr;
        }

        //echo $output."\n";
        //print_r($res->data->packages);
        return $res;//->data->packages;
        //return $arr;
    }

    function declarations2($p_offset)
    {
        $ch = $this->conn_init();
        $offset = $p_offset;
        $limit = 100;
        $arr = [];
        $res = null;

        $retryCount = 0;

        //echo "URL: ".$this->CM_BASE_URL.$this->CM_DECLARATIONS_URL.'/'.$offset.'/'.$limit."\n";
        curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_DECLARATIONS_URL . '/' . $offset . '/' . $limit);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'lang: ' . $this->lang,
            'ApiKey: ' . $this->CM_API_KEY,
            //"Content-Type: application/json-patch+json"
            "Content-Type: application/json"
        ));
        //curl_setopt($ch, CURLOPT_POSTFIELDS, '{"packagE_TYPE":"2","status":0}');
        //curl_setopt($ch, CURLOPT_POSTFIELDS, '{"packagE_TYPE":"1","status":1}');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_declarations2_json_str());
        $output = curl_exec($ch);
        if (!empty($output)) {
            $res = json_decode($output);
            $this->parse_error($res);
        }
        while ((empty($output) || !(isset($res->data) && is_array($res->data) && count($res->data) > 0)) && $retryCount < $this->retryCount) {
            $retryCount++;
            if (empty($output)) {
                echo "Error: empty response\n";
            } else {
                echo "Error: " . $output . "\n";
            }
            sleep($this->retrySleep);
            echo "retrying...\n";
            $output = curl_exec($ch);
            if (!empty($output)) {
                $res = json_decode($output);
                $this->parse_error($res);
            }
        }
        curl_close($ch);
        if (empty($output) || !(isset($res->data) && is_array($res->data) && count($res->data) > 0)) {
            if (empty($output)) {
                echo "Error: empty response\n";
            } else {
                echo "Error: " . $output . "\n";
            }
            return null;//$arr;
        }
        //echo $output."\n";
        //file_put_contents('/var/log/ase_test.log',$output."\n\n",FILE_APPEND);
        return $res->data;
        //return $arr;
    }

    function carriersposts()
    {
        $ch = $this->conn_init();
        curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_CARRIERSPOSTS_URL . '/0/1');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'lang: ' . $this->lang,
            'ApiKey: ' . $this->CM_API_KEY,
            //"Content-Type: application/json-patch+json"
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_carriersposts_json_str());
        //echo "Url: ".$this->CM_BASE_URL.$this->CM_CARRIERSPOSTS_URL.'/0/1'."<br>\n";
        //echo  "Request Body: ".$this->get_carriersposts_json_str()."<br>\n";
        $output = curl_exec($ch);
        //echo "Response: ".$output."<br>\n";
        curl_close($ch);
        $res = json_decode($output);
        return $res;
    }

    function delete_carriers()
    {
        $ch = $this->conn_init();
        curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_CARRIERS_DELETE_URL . '/' . $this->trackingNumber);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        //curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'lang: ' . $this->lang,
            'ApiKey: ' . $this->CM_API_KEY,
            //"Content-Type: application/json-patch+json"
            "Content-Type: application/json"
        ));
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_carriers_json_str());
        //echo $str."\n";
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
        $res = json_decode($output);
        return $res;
    }

    function invoice_carriers()
    {
        $this->errorStr = '';
        $ch = $this->conn_init();

        curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_CARRIERS_URL);
        //curl_setopt($ch, CURLOPT_URL, $this->get_carriersposts_url());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'lang: ' . $this->lang,
            'ApiKey: ' . $this->CM_API_KEY,
            //"Content-Type: application/json-patch+json"
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_carriers_invoice_json_str());
        echo $this->get_carriers_invoice_json_str() . "\n";
        $output = curl_exec($ch);
        echo $output . "\n";
        curl_close($ch);
        $res = json_decode($output);
        $errorMessage = '';
        $validationError = '';
        if (isset($res->exception) && is_object($res->exception)) {
            $exception = $res->exception;
            $errorMessage = $exception->errorMessage;
            $errs = [];
            if (is_array($exception->validationError))
                $errs = $exception->validationError;
            if (is_object($exception->validationError))
                $errs = get_object_vars($exception->validationError);
            foreach ($errs as $x => $x_value) {
                if (!empty($validationError)) {
                    $validationError .= " , ";
                    $validationError .= $x . "=>" . $x_value;
                }
                //$validationError=json_encode($exception->validationError);
            }
            $this->errorStr = $errorMessage . " " . $validationError . " (" . $res->code . ")";
        }
        if (!isset($res->code) || $res->code != 200) {
            //    file_put_contents("/var/log/ase_customs_put.log",$output."\n",FILE_APPEND);
        }
        return $res;
    }


    function weight_carriers()
    {
        $this->errorStr = '';
        $ch = $this->conn_init();

        curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_CARRIERS_URL);
        //curl_setopt($ch, CURLOPT_URL, $this->get_carriersposts_url());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'lang: ' . $this->lang,
            'ApiKey: ' . $this->CM_API_KEY,
            //"Content-Type: application/json-patch+json"
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_carriers_weight_json_str());
        echo $this->get_carriers_weight_json_str() . "\n";
        $output = curl_exec($ch);
        echo $output . "\n";
        curl_close($ch);
        $res = json_decode($output);
        $errorMessage = '';
        $validationError = '';
        if (isset($res->exception) && is_object($res->exception)) {
            $exception = $res->exception;
            $errorMessage = $exception->errorMessage;
            $errs = [];
            if (is_array($exception->validationError))
                $errs = $exception->validationError;
            if (is_object($exception->validationError))
                $errs = get_object_vars($exception->validationError);
            foreach ($errs as $x => $x_value) {
                if (!empty($validationError)) {
                    $validationError .= " , ";
                    $validationError .= $x . "=>" . $x_value;
                }
                //$validationError=json_encode($exception->validationError);
            }
            $this->errorStr = $errorMessage . " " . $validationError . " (" . $res->code . ")";
        }
        if (!isset($res->code) || $res->code != 200) {
            //    file_put_contents("/var/log/ase_customs_put.log",$output."\n",FILE_APPEND);
        }
        return $res;
    }

    function update_carriers()
    {
        $this->errorStr = '';
        $ch = $this->conn_init();
        curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_CARRIERS_URL);
        //curl_setopt($ch, CURLOPT_URL, $this->get_carriersposts_url());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'lang: ' . $this->lang,
            'ApiKey: ' . $this->CM_API_KEY,
            //"Content-Type: application/json-patch+json"
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_carriers_update_json_str());
        //echo $this->get_carriers_update_json_str()."\n";
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
        $res = json_decode($output);
        $errorMessage = '';
        $validationError = '';
        if (isset($res->exception) && is_object($res->exception)) {
            $exception = $res->exception;
            $errorMessage = $exception->errorMessage;
            $errs = [];
            if (is_array($exception->validationError))
                $errs = $exception->validationError;
            if (is_object($exception->validationError))
                $errs = get_object_vars($exception->validationError);
            foreach ($errs as $x => $x_value) {
                if (!empty($validationError)) {
                    $validationError .= " , ";
                    $validationError .= $x . "=>" . $x_value;
                }
                //$validationError=json_encode($exception->validationError);
            }
            $this->errorStr = $errorMessage . " " . $validationError . " (" . $res->code . ")";
        }
        if (!isset($res->code) || $res->code != 200) {
            file_put_contents("/var/log/ase_customs_put.log", $output . "\n", FILE_APPEND);
        }
        return $res;
    }

    function add_carriers()
    {

        $ch = $this->conn_init();

        //if($this->isCommercial)
        //    curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL.$this->CM_CARRIERS_COMMERCIAL_URL);
        //else
        //    curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL.$this->CM_CARRIERS_URL);
        curl_setopt($ch, CURLOPT_URL, $this->get_carriersposts_url());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'lang: ' . $this->lang,
            'ApiKey: ' . $this->CM_API_KEY,
            //"Content-Type: application/json-patch+json"
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_carriers_json_str());
        #echo "URL: ".$this->get_carriersposts_url()."\n";
        #echo $this->get_carriers_json_str()."\n";
        //return;
        $output = curl_exec($ch);
        //echo $output."\n";
        curl_close($ch);
        $res = json_decode($output);
        return $res;
    }

    /*  function declarations()
      {

          $ch = curl_init();

      if($this->curlDebug)
             curl_setopt($ch, CURLOPT_VERBOSE, true);

      curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL.$this->CM_DECLARATIONS_URL);
      curl_setopt($ch, CURLOPT_USERAGENT,'curl/7.58.0');
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
             'accept: text/plain',
             'lang: '.$this->lang,
             'ApiKey: '.$this->CM_API_KEY,
         //"Content-Type: application/json-patch+json"
         "Content-Type: application/json"
          ));
      $str="  {\n";
      $str.='   "dateFrom":'.'"'.$this->dateFrom.'"'.",\n";
      $str.='   "dateTo":'.'"'.$this->dateTo.'"'.",\n";;
      $str.='   "pinNumber":'.'"'.$this->pinNumber.'"'.",\n";
      $str.='   "trackingNumber":'.'"'.$this->trackingNumber.'"'."\n";;
      $str.="  }\n";
      curl_setopt($ch, CURLOPT_POSTFIELDS, $str);
      //echo $str."\n";
      $output = curl_exec($ch);
      curl_close($ch);
      $res=json_decode($output);
      return $res;
    }*/

    function approvesearch()
    {
        $ch = $this->conn_init();
        curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_APPROVESEARCH_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'lang: ' . $this->lang,
            'dataType: Declarations',
            'ApiKey: ' . $this->CM_API_KEY,
            //"Content-Type: application/json-patch+json"
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_approvesearch_request());
        $output = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($output);
        //echo "approvesearch: ".$output."\n";
        //print_r($res);
        return $res;
    }

    function get_approvesearch_request()
    {
        $str = "  [{\n";
        $str .= '   "regNumber":' . '"' . $this->regNumber . '"' . "\n";
        $str .= "  }]\n";
        return $str;
    }

    function cancel_addtoboxes()
    {
        $ch = $this->conn_init();
        curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_CANCEL_ADDTOBOXES_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'lang: ' . $this->lang,
            'ApiKey: ' . $this->CM_API_KEY,
            //"Content-Type: application/json-patch+json"
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_track_request());
        //echo  "cancel addtoboxex url: ".$this->get_track_request()."\n";
        $output = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($output);
        //echo "cancel addtoboxes output: ".$output."\n";
        //print_r($res);
        return $res;
    }

    function addtoboxes()
    {

        $ch = $this->conn_init();

        curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_ADDTOBOXES_URL);
        //echo "addtoboxes url: ".$this->CM_BASE_URL.$this->CM_ADDTOBOXES_URL."\n";
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'lang: ' . $this->lang,
            'ApiKey: ' . $this->CM_API_KEY,
            //"Content-Type: application/json-patch+json"
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_addtoboxes_request());
        //echo "addtoboxes request: ".$this->get_addtoboxes_request()."\n";
        $output = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($output);
        //echo "addtoboxes response: ".$output."\n";
        //print_r($res);
        return $res;
    }

    function get_addtoboxes_request()
    {
        $str = "  [{\n";
        $str .= '   "regNumber":' . '"' . $this->regNumber . '"' . ",\n";
        $str .= '   "trackingNumber":' . '"' . $this->trackingNumber . '"' . "\n";
        $str .= "  }]\n";
        return $str;
    }

    function get_track_request()
    {
        $str = "  [{\n";
        $str .= '   "trackingNumber":' . '"' . $this->trackingNumber . '"' . "\n";
        $str .= "  }]\n";
        return $str;
    }

    function get_depesh_request()
    {
        $str = "  [{\n";
        $str .= '   "regNumber":' . '"' . $this->regNumber . '"' . ",\n";
        $str .= '   "trackingNumber":' . '"' . $this->trackingNumber . '"' . ",\n";
        $str .= '   "airWaybill":' . '"' . $this->airWaybill . '"' . ",\n";
        $str .= '   "depeshNumber":' . '"' . $this->depeshNumber . '"' . "\n";
        $str .= "  }]\n";
        return $str;
    }

    function updateGoods()
    {
        $ch = $this->conn_init();

        curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_GOODS_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'lang: ' . $this->lang,
            'ApiKey: ' . $this->CM_API_KEY,
            //"Content-Type: application/json-patch+json"
            "Content-Type: application/json"
        ));
        $output = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($output);
        if (!isset($res->code))
            return 'Empty Response';
        if ($res->code != 200)
            return 'Error code:' . $res->code;
        if (!isset($res->data))
            return 'Empty data';
        $ldate = date('Y-m-d H:i:s');
        //echo $output."\n";
        $rows = DB::select('select count(*) as cnt from customs_types where deleted_at is null');
        $cntDB = 0;
        if (count($rows) > 0 && $rows[0]->cnt > 0)
            $cntDB = $rows[0]->cnt;
        DB::update("update customs_types set deleted_at=? where deleted_at is null", [$ldate]);
        $cntIns = 0;
        $cntUpd = 0;
        foreach ($res->data as $good) {
            if ($good->isDeleted) {
                continue;
            }
            $rows = DB::select('select count(*) as cnt from customs_types where id=' . $good->id);
            $nameAz = trim(str_replace(["\n"], "", $good->goodsNameAz));
            $nameEn = trim(str_replace(["\n"], "", $good->goodsNameEn));
            $nameRu = trim(str_replace(["\n"], "", $good->goodsNameRu));
            if (count($rows) > 0 && $rows[0]->cnt > 0) {
                $str = "update customs_types set parent_id=?,name_az=?,name_en=?,name_ru=?,updated_at=?,deleted_at=null where id=?";
                DB::update($str, [$good->parentId, $nameAz, $nameEn, $nameRu, $ldate, $good->id]);
                $cntUpd++;
            } else {
                $str = "insert into customs_types(id,parent_id,name_az,name_en,name_ru,created_at,updated_at)";
                $str .= " values(?,?,?,?,?,?,?)";
                DB::insert($str, [$good->id, $good->parentId, $nameAz, $nameEn, $nameRu, $ldate, $ldate]);
                $cntIns++;
            }
        }
        return "inserted:" . $cntIns . " updated:" . $cntUpd . " deleted:" . ($cntDB - $cntUpd);
    }

    function cancel_depesh()
    {

        $ch = $this->conn_init();

        curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_CANCEL_DEPESH_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'lang: ' . $this->lang,
            'ApiKey: ' . $this->CM_API_KEY,
            //"Content-Type: application/json-patch+json"
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_track_request());
        //echo "cancel depesh url: ".$this->get_track_request()."\n";
        $output = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($output);
        //echo "cancel depesh ourput: ".$output."\n";
        //print_r($res);
        return $res;
    }

    function depesh()
    {

        $ch = $this->conn_init();

        curl_setopt($ch, CURLOPT_URL, $this->CM_BASE_URL . $this->CM_DEPESH_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'accept: text/plain',
            'lang: ' . $this->lang,
            'ApiKey: ' . $this->CM_API_KEY,
            //"Content-Type: application/json-patch+json"
            "Content-Type: application/json"
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->get_depesh_request());
        //echo "depesh request: ".$this->get_depesh_request()."\n";
        $output = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($output);
        //echo "depesh ourput: ".$output."\n";
        //print_r($res);
        return $res;
    }

    function parse_error($res)
    {
        $errorMessage = '';
        $validationError = '';
        $this->errorMessage = $errorMessage;
        $this->validationError = $validationError;
        if (isset($res->exception) && is_object($res->exception)) {
            $exception = $res->exception;
            $errorMessage = $exception->errorMessage;
            //print_r($exception);
            $errs = [];
            if (is_array($exception->validationError))
                $errs = $exception->validationError;
            if (is_object($exception->validationError))
                $errs = get_object_vars($exception->validationError);
            foreach ($errs as $x => $x_value) {
                if (!empty($validationError))
                    $validationError .= " , ";
                $validationError .= $x . "=>" . $x_value;
            }
            //$validationError=json_encode($exception->validationError);
        }
        $this->errorMessage = $errorMessage;
        $this->validationError = $validationError;
    }

    function getCost()
    {
        $error = false;
        $cost = 0;
        $costUSD = 0;
        $currency = 0;
        $currencyType = 0;
        $invoicePrice = 0;
        $invoicePriceUSD = 0;
        $res = $this->get_declarations();
        if ($res && isset($res->code) && ($res->code == 200) && isset($res->data) && is_array($res->data) && count($res->data) > 0) {
            $cpost = $res->data[0];
            if (isset($cpost->goodsList) && is_array($cpost->goodsList) && count($cpost->goodsList) > 0) {
                foreach ($cpost->goodsList as $good) {
                    $currencyType = $good->currencyType;
                    $invoicePrice = $good->invoicePrice;
                    $invoicePriceUSD = $good->invoicePriceUsdNumber;
                    $costUSD += $invoicePriceUSD;
                    if (!$currency) {
                        $currency = $currencyType;
                    }
                    if ($currencyType == $currency) {
                        $cost += $invoicePrice;
                    } else {
                        $cost += number_format(0 + round($invoicePrice * getCustomsCurrencyRate($currencyType) / getCustomsCurrencyRate($currency), 2), 2, ".", "");
                    }
                    //echo $currencyType." ".$invoicePrice." ".$invoicePriceUSD."\n";
                }
            }
        }
        return (object)['error' => $error, 'cost' => $cost, 'costUSD' => $costUSD, 'currency' => $currency];
    }

    function getRegNumber()
    {
        $regNumber = '';
        $payStatus = 0;
        $insertDate = '';
        $trackingNumber = '';
        $error = false;

        $cost = 0;
        $costUSD = 0;
        $currency = 0;
        $currencyType = 0;
        $invoicePrice = 0;
        $invoicePriceUSD = 0;

        $res = $this->get_declarations();
        //echo "request: ".$this->get_declarations_json_str()."\n";
        //echo "result: ".json_encode($res, JSON_PRETTY_PRINT)."\n";
        if ($res && isset($res->code) && ($res->code == 200) && isset($res->data) && is_array($res->data) && count($res->data) > 0) {
            $regNumber = '';
            $payStatus = 0;
            $insertDate = '';
            $trackingNumber = '';
            $cpost = $res->data[0];
            $vSet = false;
            if (isset($cpost->payStatus_Id)) {
                $vSet = true;
                $payStatus = $cpost->payStatus_Id;
            }
            if (isset($cpost->trackingNumber)) {
                $vSet = true;
                $trackingNumber = $cpost->trackingNumber;
            }
            if (isset($cpost->insertDate)) {
                $vSet = true;
                $insertDate = $cpost->insertDate;
            }
            if (isset($cpost->regNumber)) {
                $vSet = true;
                $regNumber = $cpost->regNumber;
            }

            if (isset($cpost->goodsList) && is_array($cpost->goodsList) && count($cpost->goodsList) > 0) {
                foreach ($cpost->goodsList as $good) {
                    $currencyType = $good->currencyType;
                    $invoicePrice = $good->invoicePrice;
                    $invoicePriceUSD = $good->invoicePriceUsdNumber;
                    $costUSD += $invoicePriceUSD;
                    if (!$currency)
                        $currency = $currencyType;
                    if ($currencyType == $currency)
                        $cost += $invoicePrice;
                    else {
                        $cost += number_format(0 + round($invoicePrice * getCustomsCurrencyRate($currencyType) / getCustomsCurrencyRate($currency), 2), 2, ".", "");
                    }
                    //echo $currencyType." ".$invoicePrice." ".$invoicePriceUSD."\n";
                }
            }
        } else {
            $error = true;
        }

        return (object)['error' => $error, 'trackingNumber' => $trackingNumber, 'regNumber' => $regNumber, 'payStatus' => $payStatus, 'insertDate' => $insertDate, 'cost' => $cost, 'costUSD' => $costUSD, 'currency' => $currency];
    }

    function get_carrierposts2()
    {
        $cpost = new stdClass();
        $cpost->code = NULL;
        $cpost->inserT_DATE = NULL;
        $cpost->insertDateDeclaration = NULL;
        $cpost->airwaybill = NULL;
        $cpost->depesH_NUMBER = NULL;
        $cpost->depesH_DATE = NULL;
        $cpost->status = NULL;
        $cpost->ecoM_REGNUMBER = NULL;
        $cpost->ecoM_REGNUMBER_OLD = NULL;
        $cpost->errorMessage = '';
        $cpost->validationError = '';
        $cpost->request = '';
        $cpost->result = '';
        $cpost->trackinG_NO = '';
        $cpost->cost = 0;
        $cpost->costUSD = 0;
        $cpost->currency = 0;
        $cpost->weighT_GOODS = 0;

        $res = $this->get_carrierposts();

        $cpost->result = json_encode($res, JSON_PRETTY_PRINT);
        $cpost->request = $this->get_carriersposts_json_str();

        if (!isset($res->code)) {
            $cpost->code = 999;
            $cpost->errorMessage = 'Empty response';
            return $cpost;
        } else if ($res->code != 200) {
            $cpost->code = $res->code;
            $this->parse_error($res);
            $cpost->errorMessage = $this->errorMessage;
            $cpost->validationError = $this->validationError;
        } else if ($res->code == 200) {
            $cpost->code = $res->code;
            if (isset($res->data) && is_array($res->data) && count($res->data) > 0) {
                $dpost = $res->data[0];
                $cpost = new stdClass();
                $cpost->code = $res->code;
                $cpost->ecoM_REGNUMBER_OLD = $dpost->ecoM_REGNUMBER;
                $cpost->errorMessage = '';
                $cpost->validationError = '';

                $cpost->inserT_DATE = $dpost->inserT_DATE;
                $cpost->insertDateDeclaration = NULL;
                $cpost->airwaybill = $dpost->airwaybill;
                $cpost->depesH_NUMBER = $dpost->depesH_NUMBER;
                $cpost->depesH_DATE = $dpost->depesH_DATE;
                $cpost->status = $dpost->status;
                $cpost->fin = $dpost->fin;
                $cpost->ecoM_REGNUMBER = NULL;//$dpost->ecoM_REGNUMBER;
                $cpost->trackinG_NO = $dpost->trackinG_NO;
                $cpost->cost = 0;
                $cpost->costUSD = 0;
                $cpost->currency = 0;
                $cpost->weighT_GOODS = $dpost->weighT_GOODS;
                $cpost->data = $dpost;
            }
        }

        //if(!empty($cpost->inserT_DATE)) {
        //   $this->dateFrom=$cpost->inserT_DATE;
        //   $this->dateTo=$cpost->inserT_DATE;
        //}
        /*$dec=$this->getRegNumber();
        if($dec->error)
            return $cpost;
        if(!empty($dec->insertDate))
            $cpost->insertDateDeclaration=$dec->insertDate;
        if(!empty($dec->regNumber) && !empty($dec->payStatus) && $dec->payStatus>0)
        {
            $cpost->ecoM_REGNUMBER=$dec->regNumber;
                  $cpost->cost=$dec->cost;
                $cpost->costUSD=$dec->costUSD;
                $cpost->currency=$dec->currency;
        }*/
        //else
        //    $cpost->ecoM_REGNUMBER='';
        return $cpost;
    }

    function get_carrierposts()
    {
        $totalRes = null;
        $res = $this->carriersposts();
        if (!isset($res->code)) {
            for ($r = 1; $r <= $this->retryCount; $r++) {
                if ($this->retrySleep > 0)
                    sleep($this->retrySleep);
                $res = $this->carriersposts();
                if (isset($res->code))
                    break;
            }
        }
        //echo "carriersposts:\n";
        //echo "Request: ".$this->get_carriersposts_json_str()."\n";
        //echo "Response: ".json_encode($res, JSON_PRETTY_PRINT)."\n";
        return $res;
    }

    function get_declarations()
    {
        if ($this->isCommercial)
            return null;
        $res = $this->declarations();
        if (!isset($res->code))
            for ($r = 1; $r <= $this->retryCount; $r++) {
                if ($this->retrySleep > 0)
                    sleep($this->retrySleep);
                //echo $r." ".$this->retryCount." ".$this->retrySleep."\n";
                $res = $this->declarations();
                if (isset($res->code))
                    break;
            }
        //echo "declarations:\n";
        //echo "Request: ".$this->get_declarations_json_str()."\n";
        //echo "Result: ".json_encode($res, JSON_PRETTY_PRINT)."\n";
        return $res;
    }

    function updateTrackDB2($track_id, $fin, $trackingNo, $ldate, $code, $country_id = NULL, $idxal_name = NULL, $ixrac_name = NULL, $airwaybill = NULL, $depesH_NUMBER = NULL, $airwaybill_1 = NULL, $depesH_NUMBER_1 = NULL)
    {
        $rows = [];
        if (!$track_id) {
            $trackingNo2 = $trackingNo;
            if ($this->trackingNumber && $this->trackingNumber != $trackingNo2) {
                $trackingNo2 = $this->trackingNumber;
            }
            $rows = DB::select('select id from package_carriers where deleted_at is null and trackingNumber=?', [$trackingNo2]);
        } else
            $rows = DB::select('select id from package_carriers where deleted_at is null and track_id=' . $track_id);
        if (count($rows) > 0) {
            $pc_id = $rows[0]->id;
            $str = "update package_carriers";
            $str .= " set code=?,trackingNumber=?";
            $str .= " ,created_at=?,is_commercial=?";
            $str .= " ,country_id=?,idxal_name=?,ixrac_name=?";
            $str .= " ,airwaybill=?,depesH_NUMBER=?";
            $str .= " ,airwaybill_1=?,depesH_NUMBER_1=?";
            $str .= " ,errorMessage=?,validationError=?";
            $str .= " where id=?";

            DB::update($str, [$code, $trackingNo
                , $ldate, $this->isCommercial
                , $country_id, $idxal_name, $ixrac_name
                , $airwaybill, $depesH_NUMBER
                , $airwaybill_1, $depesH_NUMBER_1
                , $this->errorMessage, $this->validationError
                , $pc_id]);
        } else {
            $str = "insert into package_carriers (track_id,fin,trackingNumber";
            $str .= " ,code,created_at,is_commercial";
            $str .= " ,country_id,idxal_name,ixrac_name";
            $str .= " ,airwaybill,depesH_NUMBER";
            $str .= " ,airwaybill_1,depesH_NUMBER_1";
            $str .= " ,errorMessage,validationError)";
            $str .= " values(?,?,?";
            $str .= " ,?,?,?";
            $str .= " ,?,?,?";
            $str .= " ,?,?";
            $str .= " ,?,?";
            $str .= " ,?,?)";

            DB::insert($str, [$track_id, $fin, $trackingNo
                , $code, $ldate, $this->isCommercial
                , $country_id, $idxal_name, $ixrac_name
                , $airwaybill, $depesH_NUMBER
                , $airwaybill_1, $depesH_NUMBER_1
                , $this->errorMessage, $this->validationError
            ]);
        }
    }

    function updateDB2($package_id, $fin, $trackingNo, $ldate, $code, $country_id = NULL, $idxal_name = NULL, $ixrac_name = NULL, $airwaybill = NULL, $depesH_NUMBER = NULL, $airwaybill_1 = NULL, $depesH_NUMBER_1 = NULL)
    {
        $rows = [];
        if (!$package_id) {
            $trackingNo2 = $trackingNo;
            if ($this->trackingNumber && $this->trackingNumber != $trackingNo2) {
                $trackingNo2 = $this->trackingNumber;
            }
            $rows = DB::select('select id from package_carriers where deleted_at is null and trackingNumber=?', [$trackingNo2]);
        } else
            $rows = DB::select('select id from package_carriers where deleted_at is null and package_id=' . $package_id);
        if (count($rows) > 0) {
            $pc_id = $rows[0]->id;
            $str = "update package_carriers";
            $str .= " set code=?,trackingNumber=?";
            $str .= " ,created_at=?,is_commercial=?";
            $str .= " ,country_id=?,idxal_name=?,ixrac_name=?";
            $str .= " ,airwaybill=?,depesH_NUMBER=?";
            $str .= " ,airwaybill_1=?,depesH_NUMBER_1=?";
            $str .= " ,errorMessage=?,validationError=?";
            $str .= " where id=?";

            DB::update($str, [$code, $trackingNo
                , $ldate, $this->isCommercial
                , $country_id, $idxal_name, $ixrac_name
                , $airwaybill, $depesH_NUMBER
                , $airwaybill_1, $depesH_NUMBER_1
                , $this->errorMessage, $this->validationError
                , $pc_id]);
        } else {
            $str = "insert into package_carriers (package_id,fin,trackingNumber";
            $str .= " ,code,created_at,is_commercial";
            $str .= " ,country_id,idxal_name,ixrac_name";
            $str .= " ,airwaybill,depesH_NUMBER";
            $str .= " ,airwaybill_1,depesH_NUMBER_1";
            $str .= " ,errorMessage,validationError)";
            $str .= " values(?,?,?";
            $str .= " ,?,?,?";
            $str .= " ,?,?,?";
            $str .= " ,?,?";
            $str .= " ,?,?";
            $str .= " ,?,?)";

            DB::insert($str, [$package_id, $fin, $trackingNo
                , $code, $ldate, $this->isCommercial
                , $country_id, $idxal_name, $ixrac_name
                , $airwaybill, $depesH_NUMBER
                , $airwaybill_1, $depesH_NUMBER_1
                , $this->errorMessage, $this->validationError
            ]);
        }
    }

    function updateDB($package_id, $fin, $trackingNo, $ldate, $cpost)
    {
        //$rows=DB::select('select id from package_carriers where package_id='.$package_id);
        $rows = [];
        $code = 200;
        if (isset($cpost->code)) $code = $cpost->code;
        if (!$package_id) {
            $trackingNo2 = $trackingNo;
            if ($this->trackingNumber && $this->trackingNumber != $trackingNo2) {
                $trackingNo2 = $this->trackingNumber;
            }
            $rows = DB::select('select id from package_carriers where deleted_at is null and trackingNumber=?', [$trackingNo2]);
        } else
            $rows = DB::select('select id from package_carriers where deleted_at is null and package_id=' . $package_id);
        if (count($rows) > 0) {
            $pc_id = $rows[0]->id;
            if (empty($cpost->ecoM_REGNUMBER)) {
                $str = "update package_carriers";
                $str .= " set code=?";
                $str .= " ,inserT_DATE=?,airwaybill=?";
                $str .= " ,depesH_NUMBER=?,depesH_DATE=?";
                $str .= " ,status=?";//,ecoM_REGNUMBER=?";
                $str .= " ,created_at=?,is_commercial=?";
                $str .= " ,errorMessage=?,validationError=?";
                $str .= " ,weighT_GOODS1=?";
                $str .= " where id=?";

                DB::update($str, [$code
                    , $cpost->inserT_DATE, $cpost->airwaybill
                    , $cpost->depesH_NUMBER, $cpost->depesH_DATE
                    , $cpost->status//,$cpost->ecoM_REGNUMBER
                    , $ldate, $this->isCommercial
                    , $this->errorMessage, $this->validationError
                    , $cpost->weighT_GOODS
                    , $pc_id]);
            } else {
                $str = "update package_carriers";
                $str .= " set code=?";
                $str .= " ,inserT_DATE=?,airwaybill=?";
                $str .= " ,depesH_NUMBER=?,depesH_DATE=?";
                $str .= " ,status=?,ecoM_REGNUMBER=?";
                $str .= " ,created_at=?,is_commercial=?";
                $str .= " ,errorMessage=?,validationError=?";
                $str .= " ,weighT_GOODS1=?";
                $str .= " where id=?";

                DB::update($str, [$code
                    , $cpost->inserT_DATE, $cpost->airwaybill
                    , $cpost->depesH_NUMBER, $cpost->depesH_DATE
                    , $cpost->status, $cpost->ecoM_REGNUMBER
                    , $ldate, $this->isCommercial
                    , $this->errorMessage, $this->validationError
                    , $cpost->weighT_GOODS
                    , $pc_id]);
            }
        } else {
            $str = "insert into package_carriers (package_id,fin,trackingNumber";
            $str .= " ,code,created_at,is_commercial";
            $str .= " ,inserT_DATE,airwaybill";
            $str .= " ,depesH_NUMBER,depesH_DATE";
            $str .= " ,status,ecoM_REGNUMBER";
            $str .= " ,errorMessage,validationError,weighT_GOODS1)";
            $str .= " values(?,?,?";
            $str .= " ,?,?,?";
            $str .= " ,?,?";
            $str .= " ,?,?";
            $str .= " ,?,?";
            $str .= " ,?,?,?)";

            DB::insert($str, [$package_id, $fin, $trackingNo
                , $code, $ldate, $this->isCommercial
                , $cpost->inserT_DATE, $cpost->airwaybill
                , $cpost->depesH_NUMBER, $cpost->depesH_DATE
                , $cpost->status, $cpost->ecoM_REGNUMBER
                , $this->errorMessage, $this->validationError
                , $cpost->weighT_GOODS
            ]);
        }
    }

    function deleteDB($package_id)
    {
        DB::delete('delete from package_carriers where package_id=?', [$package_id]);
    }

    function deleteDBTrack($track_id)
    {
        DB::delete('delete from package_carriers where track_id=?', [$track_id]);
    }
}

