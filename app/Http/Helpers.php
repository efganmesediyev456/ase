<?php

use Telegram\Bot\Api;
use GuzzleHttp\Client;

if (!function_exists('findAndReplace')) {
    function findAndReplace($string, $query)
    {
        return str_ireplace($query, "<span class='replace-it'>" . $query . "</span>", $string);
    }
}

if (!function_exists('errorText')) {
    function errorText($string)
    {
        return "<span class='error-text'>" . $string . "</span>";
    }
}

if (!function_exists('labelText')) {
    function labelText($string, $type)
    {
        return "<span class='label label-" . $type . "'>" . $string . "</span>";
    }
}

if (!function_exists('clearKey')) {
    function clearKey($key)
    {
        return ucfirst(str_replace("_", " ", $key));
    }
}

function XSSCheck($value) {
    return preg_replace(
        array('/&(?!amp;|quot;|nbsp;|gt;|lt;|laquo;|raquo;|copy;|reg;|#[0-9]{1,5};|#x[0-9A-F]{1,4};)/', '/#(?![0-9]{1,5};|x[0-9A-F]{1,4};)/',       '|<|',  '|>|',  '|"|',      "|'|"   ),
        array('&amp;', '&#35;', '&lt;', '&gt;', '&#34;', '&#39;'),
        stripslashes($value)
    );
}

if (!function_exists('parseRelation')) {
    function parseRelation($item, $key)
    {
        $parsed = explode('.', $key);
        $_obj = $item;

        foreach ($parsed as $rel) {
            if (str_contains($rel, '()')) {
                $rel = str_replace('()', '', $rel);
                if ($_obj) {
                    $_obj = $_obj->{$rel}();
                }
            } elseif (str_contains($rel, 'translateOrDefault')) {
                if ($_obj) {
                    $_obj = $_obj->{$rel}('en');
                }
            } else {
                if ($_obj) {
                    $_obj = $_obj->{$rel};
                }
            }
        }

        return $_obj;
    }
}

if (!function_exists('classActiveRoute')) {
    function classActiveRoute($route)
    {
        return Request::routeIs($route . '*') ? ' class="active"' : '';
    }
}

if (!function_exists('removeHttp')) {
    function removeHttp($url)
    {
        $disallowed = [
            'http://',
            'https://',
            'http:/',
            'htpp://',
            'https:/',
            'htp://',
            'htps://',
            'htpp://',
            'htpps://',
        ];
        foreach ($disallowed as $d) {
            if (strpos($url, $d) === 0) {
                return str_replace($d, '', $url);
            }
        }

        return $url;
    }
}

if (!function_exists('getDomain')) {
    function getDomain($url)
    {
        $pieces = parse_url('http://' . removeHttp($url));
        $domain = isset($pieces['host']) ? $pieces['host'] : '';

        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
            return $regs['domain'];
        }

        return false;
    }
}

if (!function_exists('getOnlyDomain')) {
    function getOnlyDomain($url)
    {
        $domain = getDomain($url);
        if ($domain) {
            $domain = ucfirst(explode(".", $domain)[0]);
        } else {
            $domain = ucfirst($url);
        }

        return $domain;
    }
}

if (!function_exists('ifelse')) {
   function ifelse($content,$data) {
      //echo $content."\n";
      $len=strlen($content);
      while($len>=11) {
        $l=0;
        do {
           $pos1=strpos($content,'@if(',$l);
           if($pos1 !== false) {
                $cond='';
                $cont_true='';
                $cont_false='';
                $pos1+=4;
                $l=$pos1;
                do {
                    $pos2=strpos($content,')',$l);
                    if($pos2 !== false) {
                        $cond=trim(substr($content,$pos1,$pos2-$pos1));
			if($cond[0]==':') $cond=substr($cond,1,strlen($cond)-1);
                        //echo "Condition: ".$cond."\n";
                        $l=$pos2;
                        break;
                    }
                    $l++;
                } while ($l<$len);
                $else_found=false;
                do {
                   $pos3=strpos($content,'@else',$l);
                   if($pos3 !== false) {
                        $pos_nextif=strpos($content,'@if',$l);
                        if($pos_nextif !== false && $pos_nextif<=$pos3) break;
                        $else_found=true;
                        $cont_true=trim(substr($content,$pos2+1,$pos3-$pos2-1));
                        //echo "Content true: ".$cont_true."\n";
                        $l=$pos3;
                        break;
                   }
                    $l++;
                } while ($l<$len);
                if(!$else_found) {
                   $l=$pos2+1;
                   $pos3=$l-5;
                }
                do {
                   $pos4=strpos($content,'@endif',$l);
                   if($pos4 !== false) {
                        if($else_found) {
                            $cont_false=trim(substr($content,$pos3+5,$pos4-$pos3-5));
                            //echo "Content false: ".$cont_false."\n";
                        } else {
                            $cont_true=trim(substr($content,$pos3+5,$pos4-$pos3-5));
                            //echo "Content true: ".$cont_true."\n";
                        }
                        $l=$pos4;
                        break;
                   }
                    $l++;
                } while ($l<$len);
                $pos1-=4;
                $pos4+=5;
                //echo " from: ".$pos1." to ".$pos4."\n";
                if(array_key_exists($cond,$data) && $data[$cond]) {
                   $content=substr_replace($content,$cont_true,$pos1,$pos4-$pos1+1);
                } else {
                   $content=substr_replace($content,$cont_false,$pos1,$pos4-$pos1+1);
                }
               break;
           }
           $l++;
        } while ($l<$len);
        if($l>=($len-11)) break;
        $len=strlen($content);
      }
      $content=str_replace('@endif','',$content);
      //echo $content."\n";
      return $content;
   }
}

if (!function_exists('clarifyContent')) {
    function clarifyContent($content, $data = [])
    {
        if (is_array($data) & !empty($data)) {
	    $content=ifelse($content,$data);
            foreach ($data as $key => $value) {
		//file_put_contents('/var/log/ase_error.log', date('Y-m-d H:i:s') . " key: ".$key." value: ".$value."\n", FILE_APPEND);
                $content = str_replace(':' . $key, $value, $content);
            }
        }
	//file_put_contents('/var/log/ase_error.log', date('Y-m-d H:i:s') . " content: ".$content."\n", FILE_APPEND);

        return $content;
    }
}
if (!function_exists('specialPrice')) {
    function specialPrice($number)
    {
        return str_replace(",", ".", number_format((float)$number, 2, ',', ''));
    }
}

function sendTGMessage($message, $reply_to_message_id = false)
{
    if (!env('TELEGRAM_NOTIFICATION', false)) {
        return null;
    }

    try {
        $telegram = new Api(env('TELEGRAM_BOT_TOKEN'));

        $data = [
            'chat_id' => env('CHANNEL_ID'),
            'text' => $message,
            'parse_mode' => 'HTML',
            'disable_web_page_preview' => true,
        ];

        if ($reply_to_message_id) {
            $data['reply_to_message_id'] = $reply_to_message_id;
        }


        $response = json_decode($telegram->sendMessage($data), true);

        return array_key_exists('message_id', $response) ? $response['message_id'] : null;
    } catch (Exception $exception) {
        Bugsnag::notifyException($exception);
    }
}

function isOfficeWord($str)
{
   $str1=trim($str);
   if(in_array($str1, ['OFFİCE', 'OFİS', 'OFFİS', 'OFİCE', 'OFİSE', 'OFFİSE', 'OFFİC', 'OFİC','OFIS','OFISE','OFICE','OFFICE','OFIS']))
	return true;
   $str1=strtolower($str1);
   if(in_array($str1, ['office', 'ofis', 'offis', 'ofice', 'ofise', 'offise', 'offic', 'ofic','ofıs','ofıse','ofıce','offıce','ofıs']))
	return true;
   return false;
}

function getBarcodeImage($text, $dir = '', $file_name = '')
{
    $text_file = $text;
    if (!empty($file_name))
        $text_file = $file_name;
    $file_gif = '/barcode/' . $text_file . '.gif';
    $file_jpg = '/barcode/' . $text_file . '.jpg';
    $a_url='?tm='.time();
    if (!empty($dir)) {
        $file_gif = '/barcode/' . $dir . '/' . $text_file . '.gif';
        $file_jpg = '/barcode/' . $dir . '/' . $text_file . '.jpg';
    }
    if (file_exists(public_path() . $file_gif))
        return env('APP_URL') . $file_gif.$a_url;
    if (file_exists(public_path() . $file_jpg))
        return env('APP_URL') . $file_jpg.$a_url;
    $url = 'https://barcode.tec-it.com/barcode.ashx?data=' . $text . '&code=Code128&dpi=400&dataseparator=""';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $raw = curl_exec($ch);
    $status = curl_getinfo($ch);
    if (!curl_errno($ch) && $status['http_code'] == 200) {
        curl_close($ch);
        $fp = fopen(public_path() . $file_gif, 'x');
        fwrite($fp, $raw);
        fclose($fp);
        return env('APP_URL') . $file_gif.$a_url;
    }
    $url = 'https://www.cognex.com/api/Sitecore/Barcode/Get?data=' . $text . '&code=BCL_CODE128&width=600&imageType=PNG&foreColor=%23000000&backColor=%23FFFFFF&rotation=RotateNoneFlipNone';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $raw = curl_exec($ch);
    $status = curl_getinfo($ch);
    if (!curl_errno($ch) && $status['http_code'] == 200) {
        curl_close($ch);
        $fp = fopen(public_path() . $file_jpg, 'x');
        fwrite($fp, $raw);
        fclose($fp);
        return env('APP_URL') . $file_jpg;
    }
    curl_close($ch);
    return "";
}


function getCustomsCurrencyRate($customsCurrency)
{
    $currencyIndex = 0;
    $customs_currencies = array(840 => 'USD', 932 => 'AZN', 978 => 'EUR', 949 => 'TRY', 643 => 'RUB', 826 => 'GBP', 156 => 'CNY', 784 => 'AED',398 => 'KZT',410 => 'KRW');
    if (array_key_exists($customsCurrency, $customs_currencies))
        $currencyIndex = $customs_currencies[$customsCurrency];
    return getCurrencyRate($currencyIndex);

}

function convertToUSD($amount,$currencyStr)
{
    $curIndex=0;
    foreach(config('ase.attributes.currencies') as $c_key => $c_value) {
	    if($currencyStr==$c_value) {
		$curIndex=$c_key;
		break;
	    }
    }
    $mult=(1 / getCurrencyRate($curIndex));
    return round($amount * $mult, 2);
}

function convertToAZN($amount,$currencyStr)
{
    $curIndex=0;
    foreach(config('ase.attributes.currencies') as $c_key => $c_value) {
	    if($currencyStr==$c_value) {
		$curIndex=$c_key;
		break;
	    }
    }
    $mult=(getCurrencyRate(1) / getCurrencyRate($curIndex));
    return round($amount * $mult, 2);
}


function locationUrl($latitude,$longitude) {
    return $latitude && $longitude ? 'https://maps.google.com/?q='.$latitude.','.$longitude :  NULL;
}

function a2l($ustr)
{
    return str_replace(['Ə', 'ə', 'Ü', 'ü', 'Ö', 'ö', 'I', 'ı', 'Ş', 'ş', 'Ç', 'ç', 'Ğ', 'ğ'], ['E', 'e', 'U', 'u', 'O', 'o', 'I', 'i', 'S', 's', 'C', 'c', 'G', 'g'], $ustr);
}

function AZNWithLabel($amount,$currency)
{
   $mult=(getCurrencyRate(1) / getCurrencyRate($currency));
   return round($amount * $mult, 2).' AZN';
}

function getCurrencyRate($currencyIndex)
{
    if ($currencyIndex == 0) {
        return 1;
    }
    $currencies = ['USD', 'AZN', 'EUR', 'TRY', 'RUB', 'GBP', 'CNY', 'AED','KZT','KRW'];
    $currency = $currencies[$currencyIndex];
    $rates = [
        'USD' => 1,
        'AZN' => 1.7,
        'EUR' => 18.623799,
        'TRY' => 18.623799,
        'RUB' => 60.66503,
        'GBP' => 0.84675,
        'CNY' => 7.167301,
        'AED' => 3.67301,
        'KRW' => 1433.6
    ];

    $rates = DB::select("select rate from currency_rate where code=? order by created_at desc limit 1", ['USD' . $currency]);
    if($currency == 'AZN'){
        $rate = 1.7;
        return $rate;
    }
    if (count($rates) > 0) {
        $rate = $rates[0]->rate;
//        file_put_contents('/var/log/ase_currency_rate.log', 'USD' . $currency . " " . $rate . " \n", FILE_APPEND);
        return $rate;
    }


    try {
        $rate = Cache::remember($currency, 3 * 60 * 60, function () use (
            $currency,
            $rates
        ) {
            $url = "http://apilayer.net/api/live?access_key=" . env('APILAYER') . "&currencies=TRY,AZN,GBP,RUB,EUR,AED,CNY,USD,KZT,KRW&source=USD&format=1";
            //$url = "http://api.currencylayer.com/live?access_key=d2674a1e7f00bc353c8229fe6430a721&&currencies=TRY,AZN,GBP,RUB,EUR,AED,CNY,USD&source=USD&format=1";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            $server_output = curl_exec($ch);
            $content = json_decode($server_output, true);
            $try_count = 3;
            while ($try_count > 0 && !(isset($content['success']) && $content['success'] && isset($content['quotes']) && isset($content['quotes']['USD' . $currency]) && $content['quotes']['USD' . $currency] != 0)) {
                sleep(1);
                $server_output = curl_exec($ch);
                $content = json_decode($server_output, true);
                $try_count--;
            }
            curl_close($ch);

            if (isset($content['success']) && $content['success'] && isset($content['quotes']) && isset($content['quotes']['USD' . $currency]) && $content['quotes']['USD' . $currency] != 0) {
//                file_put_contents('/var/log/ase_currency_rate.log', '[' . (round($content['quotes']['USD' . $currency], 4)) . "]\n", FILE_APPEND);
                return round($content['quotes']['USD' . $currency], 4);
            }
//            file_put_contents('/var/log/ase_currency_rate.log', '[ wrong ' . $rates[$currency] . "]\n" . $server_output . "\n", FILE_APPEND);

            return (isset($rates[$currency]) ? $rates[$currency] : 0);
        });
//        file_put_contents('/var/log/ase_currency_rate.log', date('Y-m-d H:i:s') . ' ' . $currency . ' [rate ' . $rate . "]\n", FILE_APPEND);

        return $rate;
    } catch (Exception $exception) {
        Cache::forget($currency);

        return (isset($rates[$currency]) ? $rates[$currency] : 1);
    }
}

if (!function_exists('getOnlyDomainWithExt')) {
    function getOnlyDomainWithExt($url)
    {
        if (!$url) {
            return "-";
        }
        if (str_contains($url, '%3A')) {
            $url = urldecode($url);
        }

        preg_match("/[a-z0-9\-]{1,63}\.[a-z\.]{2,6}$/", parse_url($url, PHP_URL_HOST), $domain);

        return (isset($domain[0]) && $domain[0]) ? strtolower($domain[0]) : null;
    }
}

function listCells()
{
   $cellList=[];
   foreach (cellStructure() as $let => $value) {
        for ($i = 1; $i <= $value; $i++) {
            $cell = $let . $i;
	    $cellList[]=$cell;
        }
   }
   return $cellList;
}

function generateCells($json = true)
{
    $cells = [];
    foreach (cellStructure() as $let => $value) {
        for ($i = 1; $i <= $value; $i++) {
            $cell = $let . $i;
            if ($json) {
                $cells[] = '{value: "' . $cell . '", text: "' . $cell . '"}';
            } else {
                $cells[$cell] = $cell;
            }
        }
    }

    return $json ? '[' . implode(",", $cells) . ']' : $cells;
}
//
//function findCell($barcode)
//{
//    if (strpos($barcode, ':') === false)
//        return '';
//    list($val_str, $num_str) = explode(':', $barcode);
//    $num = (int)$num_str;
//    $val = strtoupper($val_str);
//    foreach (cellStructure() as $let => $value) {
//        for ($i = 1; $i <= $value; $i++) {
//            if ($let == $val && $num == $i) {
//                return $let . $i;
//            }
//        }
//    }
//    return '';
//}



function findCell($barcode)
{
    if (strpos($barcode, ':') === false) {
        return '';
    }

    [$val, $num] = explode(':', $barcode);
    $val = strtoupper($val);
    $num = (int) $num;

    if ($num <= 0) {
        return '';
    }

    $cells = cellStructure();

    if (!isset($cells[$val])) {
        return '';
    }

    if ($num > $cells[$val]) {
        return '';
    }

    return $val . $num;
}


//function cellStructure()
//{
//    $cells = config('ase.warehouse.cells');
//    if (auth()->guard('admin')->check() && auth()->guard('admin')->user()->cells) {
//        $decoded = \GuzzleHttp\json_decode(auth()->guard('admin')->user()->cells, true);
//        if (is_array($decoded)) {
//            $cells = $decoded;
//        }
//    }
//
//    return $cells;
//}


function cellStructure()
{
    static $cells = null;

    if ($cells !== null) {
        return $cells;
    }

    $cells = config('ase.warehouse.cells');

    if (auth()->guard('admin')->check() && auth()->guard('admin')->user()->cells) {
        $decoded = json_decode(auth()->guard('admin')->user()->cells, true);
        if (is_array($decoded)) {
            $cells = $decoded;
        }
    }

    return $cells;
}


function luminance($steps)
{
    $steps = 255 - 3 * $steps;
    $hex = 'ff0000';
    // Steps should be between -255 and 255. Negative = darker, positive = lighter
    $steps = max(-255, min(255, $steps));

    // Normalize into a six character long hex string
    $hex = str_replace('#', '', $hex);
    if (strlen($hex) == 3) {
        $hex = str_repeat(substr($hex, 0, 1), 2) . str_repeat(substr($hex, 1, 1), 2) . str_repeat(substr($hex, 2, 1), 2);
    }

    // Split into three parts: R, G and B
    $color_parts = str_split($hex, 2);
    $return = '#';

    foreach ($color_parts as $color) {
        $color = hexdec($color); // Convert to decimal
        $color = max(0, min(255, $color + $steps)); // Adjust color
        $return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
    }

    return substr($return, 0, 9);
}

function cleanString($string)
{
    $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.

    return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
}

if (!function_exists('liveDebug')) {
    function liveDebug($data)
    {
        if ((isset($_SERVER['REMOTE_ADDR']) && in_array($_SERVER['REMOTE_ADDR'], ['94.20.38.158','193.105.123.207','5.44.39.181','46.22.229.47'])) || env('APP_ENV') === 'local') {
            dd($data);
        }
    }
}

if (!function_exists('clearNumber')) {
    function clearNumber($number, $addPrefix = false, $space = null)
    {
        $number = explode(";", $number)[0];
        $number = explode(",", $number)[0];
        $number = explode("/", $number)[0];
        $number = explode('\\', $number)[0];
        $number = str_replace(" ", "", $number);
        $number = str_replace("_", "", $number);
        $number = str_replace("-", "", $number);
        $number = str_replace("(", "", $number);
        $number = str_replace(")", "", $number);
        $number = trim($number);
        if (substr($number, 0, 1) === '+') {
            $number = str_replace("+", "", $number);
        }
        if (substr($number, 0, 2) === '00') {
            $number = str_replace("00", "", $number);
        }
        if (substr($number, 0, 3) === '994') {
            $number = substr($number, 3);
        }
        if (substr($number, 0, 2) === '94') {
            $number = substr($number, 2);
        }
        if (strlen($number) == 10 || substr($number, 0, 1) === '0') {
            $number = substr($number, 1);
        }
        $number = preg_replace('/\D/', '', $number);
        if ($addPrefix && strlen($number) == 9) {
            $number = "994" . $space . $number;
        }
        if (strlen($number) < 9) {
            $number = null;
        }
        return $number;
    }
}




if (!function_exists('sendTelegramMessage')) {

    function sendTelegramMessage($message)
    {
        $config = config('initest');

        try {
            $client = new Client();
            $client->post("https://api.telegram.org/bot" . $config['bot_id'] . "/sendMessage", [
                'form_params' => [
                    'chat_id' => $config['chat_id'],
                    'text' => $message,
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Telegram message failed: ' . $e->getMessage());
        }
    }
}
