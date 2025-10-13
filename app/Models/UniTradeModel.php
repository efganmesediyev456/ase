<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;
use ZipArchive;

class UniTradeModel extends Model
{

    function getPackageXmlStr($package)
    {
        $ltime = time();
        $ldate = date('Y-m-d\TH:i:sP', $ltime);
        $str = '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        $str .= '<AltaIndPost time="' . $ldate . '" user="" Version="2.0.247.27" FileName="*{7B7980D5-8E19-4D0D-B504-220ECB272042}\{F35CB6C8-62DE-4A02-9829-D6D2A8EAA5F6}.IndP" EDVer="5_15_0" Comment="">' . "\n";
        if (isset($package->custom_id))
            $str .= '  <NUM>' . $package->custom_id . '</NUM>' . "\n";
        if (isset($package->tracking_code))
            $str .= '  <TRACKNUM>' . $package->tracking_code . '</TRACKNUM>' . "\n";
        if (isset($package->bag[0]))
            $str .= '  <BAG>' . $package->bag[0]->custom_id . '</BAG>' . "\n";


        $fakeInvoiceId = str_replace('ASE', 'INV', $package->custom_id);
        $fakeInvoiceDate = $package->created_at->format('Y-m-d');
        $str .= '  <INVNUM>' . $fakeInvoiceId . '</INVNUM>' . "\n";
        $str .= '  <INVDATE>' . $fakeInvoiceDate . '</INVDATE>' . "\n";

        $str .= '  <SENDER>АО ЮНИТРЕЙД</SENDER>' . "\n";
        $str .= '  <CONSIGNOR_CHOICE>2</CONSIGNOR_CHOICE>' . "\n";
        $str .= '  <COUNTRYCODE>RU</COUNTRYCODE>' . "\n";
        $str .= '  <COUNTRYNAME>РОССИЯ</COUNTRYNAME>' . "\n";
        $str .= '  <CONSIGNOR_ADDRESS_REGION>МОСКВА</CONSIGNOR_ADDRESS_REGION>' . "\n";
        $str .= '  <CONSIGNOR_ADDRESS_CITY>МОСКВА</CONSIGNOR_ADDRESS_CITY>' . "\n";
        $str .= '  <CONSIGNOR_ADDRESS_STREETHOUSE>НОВАЯ БАСМАННАЯ</CONSIGNOR_ADDRESS_STREETHOUSE>' . "\n";
        $str .= '  <CONSIGNOR_ADDRESS_HOUSE>14 стр.4</CONSIGNOR_ADDRESS_HOUSE>' . "\n";
        $str .= '  <CONSIGNOR_ADDRESS_POSTALCODE>107078</CONSIGNOR_ADDRESS_POSTALCODE>' . "\n";

        $str .= '  <CONSIGNOR_PHONE>7 (495) 620-49-49</CONSIGNOR_PHONE>' . "\n";
        $str .= '  <CONSIGNOR_RFORGANIZATIONFEATURES_OGRN>5077746710399</CONSIGNOR_RFORGANIZATIONFEATURES_OGRN>' . "\n";
        $str .= '  <CONSIGNOR_RFORGANIZATIONFEATURES_KPP>770101001</CONSIGNOR_RFORGANIZATIONFEATURES_KPP>' . "\n";
        $str .= '  <CONSIGNOR_RFORGANIZATIONFEATURES_INN>7701719519</CONSIGNOR_RFORGANIZATIONFEATURES_INN>' . "\n";
        $str .= '  <DEPARTUREPOINT_IATACODE>DME</DEPARTUREPOINT_IATACODE>' . "\n";

        $str .= '  <CONSIGNEE_CHOICE>1</CONSIGNEE_CHOICE>' . "\n";
        $str .= '  <CONSIGNEE_ADDRESS_COUNTRYCODE>AZ</CONSIGNEE_ADDRESS_COUNTRYCODE>' . "\n";
        $str .= '  <CONSIGNEE_ADDRESS_COUNRYNAME>Азербайджан</CONSIGNEE_ADDRESS_COUNRYNAME>' . "\n";

        if (isset($package->user)) {
            $user = $package->user;
            $passportSeria = '';
            $passportNumber = '';
            $arr = explode('-', $user->passport);
            if (count($arr) == 2) {
                $passportSeria = $arr[0];
                $passportNumber = $arr[1];
            } else {
                $passportNumber = $user->passport;
            }
            $fromArr = ['Ə', 'ə', 'Ü', 'ü', 'Ö', 'ö', 'I', 'ı', 'Ç', 'ç', 'Ş', 'ş', 'İ', 'i', 'Ğ', 'ğ'];
            $toArr = ['E', 'e', 'U', 'u', 'O', 'o', 'I', 'i', 'Ch', 'ch', 'Sh', 'sh', 'I', 'i', 'G', 'g'];
            $str .= '  <PERSONSURNAME>' . str_replace($fromArr, $toArr, $user->surname) . '</PERSONSURNAME>' . "\n";
            $str .= '  <PERSONNAME>' . str_replace($fromArr, $toArr, $user->name) . '</PERSONNAME>' . "\n";
            $str .= '  <STREETHOUSE>' . str_replace($fromArr, $toArr, $user->address) . '</STREETHOUSE>' . "\n";
            $str .= '  <PHONEMOB>' . $user->phone . '</PHONEMOB>' . "\n";
            $str .= '  <REGION>' . str_replace($fromArr, $toArr, $user->city_name) . '</REGION>' . "\n";
            $str .= '  <CITY>' . str_replace($fromArr, $toArr, $user->city_name) . '</CITY>' . "\n";
            $str .= '  <IDENTITYCARDNAME>Паспорт</IDENTITYCARDNAME>' . "\n";
            $str .= '  <IDENTITYCARDSERIES>' . $passportSeria . '</IDENTITYCARDSERIES>' . "\n";
            $str .= '  <IDENTITYCARDNUMBER>' . $passportNumber . '</IDENTITYCARDNUMBER>' . "\n";
        }

        $currency = config('ase.attributes.currencies')[$package->shipping_amount_cur];
        $str .= '  <ALLCOST>' . $package->shipping_amount_goods . '</ALLCOST>' . "\n";
        $str .= '  <CURRENCY>' . $currency . '</CURRENCY>' . "\n";
        $str .= '  <ALLWEIGHT>' . $package->weight_goods . '</ALLWEIGHT>' . "\n";

        $str .= '  <DELIVERYTERMS_TRADINGCOUNTRYCODE>RU</DELIVERYTERMS_TRADINGCOUNTRYCODE>' . "\n";
        $str .= '  <DELIVERYTERMS_DISPATCHCOUNTRYCODE>RU</DELIVERYTERMS_DISPATCHCOUNTRYCODE>' . "\n";
        $str .= '  <DELIVERYPOINT_IATACODE>GYD</DELIVERYPOINT_IATACODE>' . "\n";
        $str .= '  <DELIVERYTERMS_DELIVERYTERMSSTRINGCODE>DAP</DELIVERYTERMS_DELIVERYTERMSSTRINGCODE>' . "\n";
        $str .= '  <Type>0</Type>' . "\n";
        $str .= '  <EnterOrExitCustomsTerritory>2</EnterOrExitCustomsTerritory>' . "\n";

        if (isset($package->goods)) {
            foreach ($package->goods as $good) {
                $ruType = RuType::where('id', $good->ru_type_id)->first();
                if (!$ruType) continue;
                $str .= '  <GOODS>' . "\n";
                $str .= '    <DESCR>' . $ruType->name_ru . '</DESCR>' . "\n";
                $str .= '    <QTY>' . $good->number_items . '</QTY>' . "\n";
                $str .= '    <COST>' . $good->shipping_amount . '</COST>' . "\n";
                $str .= '    <COSTRUB>' . $good->getShippingAmountRUB() . '</COSTRUB>' . "\n";
                $str .= '    <WEIGHT>' . $good->weight . '</WEIGHT>' . "\n";
                $str .= '    <TNVED>' . $ruType->hs_code . '</TNVED>' . "\n";
                $str .= '  </GOODS>' . "\n";
            }
        }

        $str .= '</AltaIndPost>' . "\n";
        return $str;
    }

    function getParcelZip($parcel_id)
    {
        $parcel = Parcel::find($parcel_id);
        if (!$parcel)
            return '';

        $zipFile = 'unitrade_' . $parcel->custom_id . '.zip';
        $zip = new ZipArchive;
        if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE)
            return '';

        foreach ($parcel->packages as $package) {
            $xmlStr = $this->getPackageXmlStr($package);
            $xmlFile = 'unitrade_';
            if (isset($package->bag[0]))
                $xmlFile .= $package->bag[0]->custom_id;
            $xmlFile .= '_' . $package->custom_id . '.xml';
            $zip->addFromString($xmlFile, $xmlStr);
        }
        $zip->close();
        return $zipFile;
    }

}
