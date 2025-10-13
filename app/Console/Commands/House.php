<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use KubAT\PhpSimple\HtmlDomParser;

class House extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'house:grab {--type=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');
        if ($this->option('type') == 'all') {
            $this->getScrapePageUrl('https://tap.az/elanlar/dasinmaz-emlak/menziller?p[740]=3722');
        } else {
            $this->checkSold();
        }
    }

    public function checkSold()
    {
        $time_start = microtime(true);
        $daysBefore = Carbon::now()->subDays(1);

        $houses = \App\Models\Extra\House::whereNull('sold_at')->where('created_at', "<=", $daysBefore)->orderBy('checked_at', 'asc')->orderBy('id', 'asc')->take(60)->get();

        foreach ($houses as $house) {
            sleep(1);
            $this->line($house->id . ")  Checking : " . $house->url);
            $isActive = $this->checkIsActive($house->url);
            $house->checked_at = Carbon::now();
            if (!$isActive) {
                $house->sold_at = Carbon::now();
                $this->error(" Sold");
            } else {
                $this->info(" Is active");
            }
            $house->save();
        }

        $time_end = microtime(true);

        $time = ceil(($time_end - $time_start));
        $this->error($time . ' secs');
    }

    public function getDataFromListItem($html)
    {
        $url = 'https://tap.az' . $html->href;
        $header = $html->find(".products-name", 0)->innertext;
        $partOne = explode(",", $header);
        if (!str_contains($partOne[0], "otaqlı")) {
            return false;
        }
        $rooms = (int)explode("-", $partOne[0])[0];
        $type = str_contains($header, "yeni") ? 'yeni' : 'kohne';
        $place = trim($partOne[1]);
        $parsed = explode(" ", $header);
        $area = floatval($parsed[count($parsed) - 2]);
        if ($area < 10) {
            $area *= 10;
        }

        //$info['typeOfAnnounc'] = '';
        $info['typeOfBuilding'] = $type;

        $info['area'] = $area;
        $info['numberOfRooms'] = $rooms;
        $info['situation'] = $place;

        $info['phone'] = null;
        $info['name'] = null;
        $info['url'] = $url;

        $price = (int)str_replace(" ", "", $html->find('.price-val', 0)->innertext);
        if ($price < 100) {
            $price *= 1000;
        }
        $info['price'] = $price;

        $parseFooter = explode(",", $html->find('.products-created', 0)->innertext);

        $info['city'] = trim($parseFooter[0]);
        $info['time'] = $this->exractTime($parseFooter[1]);
        $parsed = explode("/", $url);
        $info['id'] = (int)end($parsed);

        $this->info($info['city'] . " :: " . $info['price'] . " :: " . $url);


        $this->writeToDB($info);
    }

    public function scrapePage($url)
    {

        $html = HtmlDomParser::file_get_html($url, false, stream_context_create(
            array("http" => array("user_agent" => "any"))));

        $info['city'] = $html->find('.property-value', 0)->innertext;
        $info['typeOfAnnounc'] = $html->find('.property-value', 1)->innertext;
        $info['typeOfBuilding'] = $html->find('.property-value', 2)->innertext;
        $area = floatval($html->find('.property-value', 3)->innertext);
        if ($area < 10) {
            $area *= 10;
        }
        $info['area'] = $area;
        $info['numberOfRooms'] = $html->find('.property-value', 4)->innertext;
        $info['situation'] = $html->find('.property-value', 5)->innertext;

        $info['phone'] = $html->find('.phone', 0)->innertext;
        $info['name'] = $html->find('.phone', 0)->nextSibling()->innertext;
        $info['time'] = $this->exractTime($html->find('.lot-info', 0)->lastChild()->innertext);
        $info['url'] = $url;
        $price = (int)str_replace(" ", "", $html->find('.price-val', 0)->innertext);
        if ($price < 100) {
            $price *= 1000;
        }
        $info['price'] = $price;
        $parsed = explode("/", $url);
        $info['id'] = (int)end($parsed);
        $this->info($info['city'] . " :: " . $info['price'] . " :: " . $url);
        $this->writeToDB($info);
        return $info;
    }

    function getScrapePageUrl($url, $counted = 0)
    {
        try {

            if ($counted > 3700) {
                exit();
            }
            $html = HtmlDomParser::file_get_html($url, false, stream_context_create(
                array("http" => array("user_agent" => "any"))));

            $productCount = count($html->find('.products-link'));
            dump($counted . " ------- ");

            for ($i = $productCount - 48; $i < $productCount; $i++) {
                $element = $html->find('.products-link', $i);
                $this->line($element->href);
                $this->getDataFromListItem($element);
                //$this->ScrapePage('https://tap.az' . $html->find('.products-link',$i)->href);
            }


            //die();

            $newPageUrl = $html->find('.next', 0)->firstChild()->href;

            if ($newPageUrl) {
                $newPageUrl = 'https://tap.az' . $newPageUrl;
                //dump($newPageUrl);
                $counted += $productCount;
                $this->getScrapePageUrl($newPageUrl, $counted);
            }
        } catch (Exception $exception) {
            $this->getScrapePageUrl($url);
        }

    }

    function exractTime($time)
    {
        $time = trim(strtolower($time));

        dump($time);
        $months = ['yanvar' => '01',
            'fevral' => '02',
            'mart' => '03',
            'aprel' => '04',
            'may' => '05',
            'iyun' => '06',
            'iyul' => '07',
            'avqust' => '08',
            'sentyabr' => '09',
            'oktyabr' => '10',
            'noyabr' => '11',
            'dekabr' => '12'
        ];

        if ($time == 'bugün') {
            $rtime['date_time'] = Carbon::now();
            $rtime['timestamp'] = Carbon::now()->timestamp;
        } else if ($time == 'dünən') {
            $rtime['date_time'] = Carbon::now()->subDay();
            $rtime['timestamp'] = Carbon::now()->subDay()->timestamp;
        } else {
            $arr = explode(' ', $time);
            $ratime = $arr[0] . "-" . $months[$arr[1]] . "-" . $arr[2];
            $rtime['date_time'] = Carbon::createFromFormat('d-m-Y', $ratime);
            $rtime['timestamp'] = Carbon::createFromFormat('d-m-Y', $ratime)->timestamp;
            //dd($rtime->timestamp);
        }
        return $rtime;
    }

    public function writeToDB($info)
    {
        $check = \App\Models\Extra\House::where('custom_id', $info['id'])->where('provider', 'tap')->first();
        if (!$check && $info['price'] > 3000) {
            DB::table('houses')->insert(
                [
                    'custom_id' => $info['id'],
                    'city' => $info['city'],
                    //'type' => $info['typeOfAnnounc'],
                    'condition' => $info['typeOfBuilding'],
                    'area' => $info['area'],
                    'number_of_rooms' => $info['numberOfRooms'],
                    'phone' => $info['phone'],
                    'name' => $info['name'],
                    'place_or_district' => $info['situation'],
                    'uploaded_at' => $info['time']['date_time'],
                    'created_at' => Carbon::now(),
                    'url' => $info['url'],
                    'price' => $info['price']
                ]
            );
        }

    }

    public function checkIsActive($url)
    {


        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, TRUE);

        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);

        /* Check for 404 (file not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if ($httpCode == 404) {
            return false;
        }

        curl_close($handle);

        $html = HtmlDomParser::file_get_html($url, false, stream_context_create(
            array("http" => array("user_agent" => "any"))));
        $lot = $html->find('.lot-expired');
        $notfound = $html->find('.not-found-page');
        if ($notfound || $lot) {
            if ($lot) {
                $this->warn("  -- Lot expired");
            } else {
                $this->warn("  -- Not Found");
            }
            return false;
        } else {
            return true;
        }

    }
}
