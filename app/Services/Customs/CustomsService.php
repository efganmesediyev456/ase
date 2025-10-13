<?php

namespace App\Services\Customs;

use App\Models\Bundle;
use App\Models\Track;
use App\Services\Integration\UnitradeService;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Log;

class CustomsService
{
    private $apiUrl = "";
    private $apiKey = "";
    private $lang = "az";

    public function sendToCustoms(Track $track)
    {
        $goodList = $bundle->bundles->map(function ($b) {
            $name_of_goods  = str_replace(["insert"],[""],$b->productType->title);
            return [
                "goods_id" => $b->id,
                "name_of_goods" => $name_of_goods//$b->productType->title,
            ];
        })->toArray();
        $data = array([
            "direction" => 0,
            "tracking_no" => $bundle->sky_tracking,
            "transp_costs" => $bundle->delivery_price ? $bundle->delivery_price : 3,
            "weight_goods" => $bundle->cargo_weight ? $bundle->cargo_weight / 1000 : 0.499,
            "quantity_of_goods" => $bundle->qty, //count of products inside Bundle
            "invoys_price" => $bundle->group_price,
            "currency_type" => "949",
            "document_type" => "PinCode",
            "idxal_name" => truncate($bundle->user->full_name, 100),
            "idxal_adress" => truncate($bundle->user->home_address, 200),
            "ixrac_name" => truncate(is_numeric($bundle->shop) ? $bundle->store->name : $bundle->shop, 100),
            "ixrac_adress" => truncate(is_numeric($bundle->shop) ? $bundle->store->name : $bundle->shop, 200),
            "fin" => $bundle->user->passport_fin,
            "phone" => $bundle->user->phone,
            "goods_traffic_fr" => "792",
            "goods_traffic_to" => "031",
            "goodsList" => $goodList,
        ]);
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'ApiKey' => $this->apiKEY,
            'lang' => $this->lang,
        ])->post($this->apiV2URL . '/carriers', $data);
        return $response->json();

    }

    public function updateCarriersPosts($bundles, $air_waybill)
    {
        $data = $bundles->map(function ($bundle) use ($air_waybill) {
            return [
                "tracking_no" => $bundle->sky_tracking,
                "quantity_of_goods" => $bundle->qty,
                "phone" => $bundle->user->phone,
                "ixrac_name" => is_numeric($bundle->shop) ? $bundle->store->name : $bundle->shop,
                "ixrac_adress" => is_numeric($bundle->shop) ? $bundle->store->name : $bundle->shop,
                "goods_traffic_fr" => "792",
                "goods_traffic_to" => "031",
                "airwaybill" => $air_waybill,
                "depesh_number" => "BOX" . $bundle->pouch->pouch_number
            ];
        })->toArray();

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'ApiKey' => $this->apiKEY,
            'lang' => $this->lang,
        ])->put($this->apiV2URL . '/carriers', $data);
        return $response->json();
    }

    public function deleteFromCarriers(Bundle $bundle)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'ApiKey' => $this->apiKEY,
            'lang' => $this->lang,
        ])->delete($this->apiV2URL . "/carriers/{$bundle->sky_tracking}");
        return $response->json();
    }

    public function carriersPosts(string $pinNumber = null, string $trackingNumber = "", $dateFrom = null, $dateTo = null)
    {
        $data = array(
            "dateFrom" => ($dateFrom && $trackingNumber == null) ? $dateFrom : Carbon::now()->subHours(1),
            "dateTo" => ($dateTo && $trackingNumber == null) ?: Carbon::now()->addHours(6),
            "pinNumber" => $pinNumber ?: null,
            "trackingNumber" => $trackingNumber ?: null,
        );
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'ApiKey' => $this->apiKEY,
            'lang' => $this->lang,
        ])->post($this->apiV2URL . '/carriers/carriersposts/0/100', $data);
        return $response->json();
    }

    public function airwaybillpackages($airWaybill, $depeshNumber = null)
    {
        $data = array(
            "airWaybill" => $airWaybill ?: null,
            "depeshNumber" => $depeshNumber ?: null,
        );
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'ApiKey' => $this->apiKEY,
            'lang' => $this->lang,
        ])->post($this->apiV2URL . '/carriers/airwaybillpackages', $data);
        return $response->json();
    }

    public function getDeclarationsByDate($from = null, $to = null, int $page = 0, $tracking = null)
    {
        $data = array(
            "dateFrom" => $from,
            "dateTo" => $to,
            "pinNumber" => null,
            "trackingNumber" => $tracking,
        );
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'ApiKey' => $this->apiKEY,
            'lang' => $this->lang,
        ])->post($this->apiV2URL . '/carriers/declarations/' . $page . '/100', $data);
        return $response->json();
    }

    public function getDeletedDeclarationsByFilter($from = null, $to = null, $page = 0, $tracking = null)
    {
        $data = array(
            "dateFrom" => $from,
            "dateTo" => $to,
            "pinNumber" => null,
            "trackingNumber" => $tracking,
            "status" => 0,
        );
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'ApiKey' => $this->apiKEY,
            'lang' => $this->lang,
        ])->post($this->apiV2URL . '/carriers/deleteddeclarations/' . $page . '/100', $data);
        return $response->json();
    }

    public function setDeletedBundles()
    {
        $items = $this->getDeletedDeclarations();
        $count = 0;
        if (is_array($items) && is_array($items['data']) && count($items['data'])) {
            foreach ($items['data'] as $item) {
                $bundle = Bundle::with(['bundles'])->firstWhere('customs_regnumber', $item['REGNUMBER']);
                if ($bundle && $bundle->customs_user_deleted_at == null) {
                    Bundle::where('customs_regnumber', $item['REGNUMBER'])->update([
                        'customs_user_deleted_at' => Carbon::now(),
                        'customs_user_declared_at' => null,
                    ]);
                    $count++;
                }
            }
        }
        Log::info("Cron for Customs Deleted Bundles($count bundles) working at: " . Carbon::now()->format('d/m/Y H:i'));
        return;
    }

    public function getDeletedDeclarations($trackingNumber = null)
    {
        $data = array(
            "dateFrom" => Carbon::now()->subHours(1),
            "dateTo" => Carbon::now()->addHours(6),
            "pinNumber" => null,
            "trackingNumber" => $trackingNumber ?: null,
            "status" => 0,
        );
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'ApiKey' => $this->apiKEY,
            'lang' => $this->lang,
        ])->post($this->apiV2URL . '/carriers/deleteddeclarations/0/100', $data);
        return $response->json();
    }

    public function setDeclaredBundles()
    {
        $items = $this->getDeclarations();
        $count = 0;
        if (is_array($items) && is_array($items['data']) && count($items['data'])) {
            foreach ($items['data'] as $item) {
                $invoicePrice = 0;
                $invoicePriceUsd = 0;
                $deliveryPrice = 0;
                $invoicePriceCurrencyType = 949;
                $bundle = Bundle::with(['bundles'])->firstWhere('sky_tracking', $item['trackingNumber']);
                if ($bundle && $bundle->customs_user_declared_at == null) {
                    foreach ($item['goodsList'] as $goods) {
                        $invoicePrice += floatval($goods['invoicePrice']);
                        $invoicePriceUsd += floatval($goods['invoicePriceUsdNumber']);
                        $deliveryPrice += floatval($goods['shippingCost']);
                        $invoicePriceCurrencyType = $goods['currencyType'];
                    }
                    Bundle::where('sky_tracking', $item['trackingNumber'])->update([
                        'customs_regnumber' => $item['regNumber'],
                        'customs_paystatus_id' => intval($item['payStatus_Id']),
                        'customs_paystatus' => $item['payStatus'],
                        'customs_user_declared_at' => Carbon::now(),
                        'customs_response' => json_encode($item),
                        'customs_store' => $item['exportName'],
                        'customs_category' => $item['goodsList'][0]['goodsName'],
                        'customs_invoice_price' => formatNumber($invoicePrice),
                        'customs_invoice_currency_type' => $invoicePriceCurrencyType,
                        'customs_invoice_price_usd' => formatNumber($invoicePriceUsd),
                        'customs_delivery_price' => formatNumber($deliveryPrice),
                        'customs_value' => formatNumber($invoicePriceUsd + $deliveryPrice),
                        'customs_user_deleted_at' => null,
                        'pouch_id' => 0
                    ]);
                    $count++;
                }
            }
        }
        Log::info("Cron for Customs Declared Bundles($count bundles) working at: " . Carbon::now()->format('d/m/Y H:i'));
        return;
    }

    public function getDeclarations($trackingNumber = null)
    {
        $data = array(
            "dateFrom" => Carbon::now()->subHours(1),
            "dateTo" => Carbon::now()->addHours(6),
            "pinNumber" => null,
            "trackingNumber" => $trackingNumber ?: null,
        );
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'ApiKey' => $this->apiKEY,
            'lang' => $this->lang,
        ])->post($this->apiV2URL . '/carriers/declarations/0/100', $data);
        return $response->json();
    }

    public function addToPouch(Bundle $bundle)
    {
        $data = array([
            "regNumber" => $bundle->customs_regnumber,
            "trackingNumber" => substr($bundle->sky_tracking, 3, 0) . $bundle->pouch->pouch_number,
        ]);
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'ApiKey' => $this->apiKEY,
            'lang' => $this->lang,
        ])->post($this->apiV2URL . '/carriers/addtoboxes', $data);
        return $response->json();
    }

    public function addBundlesToPouch($data)
    {
        if ($data) {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'ApiKey' => $this->apiKEY,
                'lang' => $this->lang,
            ])->post($this->apiV2URL . '/carriers/addtoboxes', $data);
            return $response->json();
        }
        return null;
    }

    public function removeBundlesFromPouch($trackingNumber)
    {
        $data = array([
            "trackingNumber" => $trackingNumber,
        ]);
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'ApiKey' => $this->apiKEY,
            'lang' => $this->lang,
        ])->post($this->apiV2URL . '/carriers/canceladdtoboxes', $data);
        return $response->json();
    }

    public function sendDepesh(array $data)
    {
        $response = [
            'code' => 200,
            'errors' => [],
        ];
        if (count($data) > 500) {
            $data = array_chunk($data, 500);
            foreach ($data as $bundles) {
                $res = Http::withHeaders([
                    'Accept' => 'application/json',
                    'ApiKey' => $this->apiKEY,
                    'lang' => $this->lang,
                ])->post($this->apiV2URL . '/carriers/depesh', $bundles);
                if ($res->failed()) {
                    $res = $res->json();
                    $response['code'] = 400;
                    array_push($response['errors'], [$res['data'], $res['exception']]);
                }
                sleep(1);
            }
        } else {
            $res = Http::withHeaders([
                'Accept' => 'application/json',
                'ApiKey' => $this->apiKEY,
                'lang' => $this->lang,
            ])->post($this->apiV2URL . '/carriers/depesh', $data);
            if ($res->failed()) {
                $res = $res->json();
                $response['code'] = 400;
                array_push($response['errors'], [$res['data'], $res['exception']]);
            }
        }
        return $response;
    }

    public function sendBundlesToCustoms()
    {
        $this->apiUrl = env('CUSTOMS_URL', "https://ecarrier-fbusiness.customs.gov.az:7540");
        $this->apiKey = env('CUSTOMS_KEY', "8CD0F430D478F8E1DFC8E1311B20031E3A669607");

        $bundles = Track::with(['customer', 'goods.ru_type', 'unitradePackage.warehouse.country'])->withCount('goods')
            ->where('status', UnitradeService::STATES['SentToCustoms'])
            ->where('weight', '!=', 0)
//            ->whereNull('customs_declare_at')
            ->limit(25)
            ->get();
        $ids = clone $bundles->pluck('id');
        $customsCountries = DB::select("select * from customs_countries");
        $cmCountries = [];
        foreach ($customsCountries as $item) {
            $cmCountries[strtolower($item->CODE_C)] = $item->CODE_N;
        }
        $bundles = $bundles->map(function ($bundle) use ($cmCountries) {
            $webSiteName = getOnlyDomainWithExt($bundle->website);
            if ($webSiteName == '-') {
                if ($bundle->partner_id == 1) {
                    $webSiteName = 'iherb.com';
                }
                if ($bundle->partner_id == 2) {
                    $webSiteName = 'wildberries.ru';
                }
                if ($bundle->partner_id == 3) {
                    $webSiteName = 'ozon.ru';
                }
            }

            return array(
                "direction" => 0,
                "tracking_no" => $bundle->tracking_code,
                "transp_costs" => $bundle->delivery_price != 0 ? $bundle->delivery_price : 3,
                "weight_goods" => $bundle->weight,
                "quantity_of_goods" => $bundle->goods_count,
                "invoys_price" => $bundle->shipping_amount,
                "currency_type" => $bundle->currency == 'USD' ? 840 : ($bundle->currency == 'RUB' ? 643 : 944),
                "document_type" => "PinCode",
                "idxal_name" => $bundle->customer->fullname,
                "idxal_adress" => $bundle->address,
                "ixrac_name" => $webSiteName,
                "ixrac_adress" => $webSiteName,
                "fin" => $bundle->customer->fin,
                "phone" => $bundle->phone ?? $bundle->customer->phone,
                "goods_traffic_fr" => $cmCountries[$bundle->unitradePackage->warehouse->country->code],
                "goods_traffic_to" => "031",
                "goodsList" => $bundle->goods->map(function ($b) {
                    $name_of_goods  = str_replace(["insert"],[""],$b->ru_type->name_ru);

                    return array(
                        "goods_id" => $b->ru_type->customs_type_id,
                        "name_of_goods" => $name_of_goods// $b->ru_type->name_ru,
                    );
                })->toArray(),
            );
        })->toArray();

        if (count($ids)) {
            $client = new Client([
                'headers' => [
                    'Accept' => 'application/json',
                    'ApiKey' => $this->apiKey,
                    'lang' => $this->lang,
                ]
            ]);

            $count = count($ids);
            Log::info("Cron for --Send Bundles to Customs-- prepared. $count bundles will send to Customs.");

            try {
                $response = $client->post($this->apiUrl . '/api/v2/carriers', [
                    'json' => $bundles
                ]);

                dd($response->getBody());
                if ($response->getStatusCode() == 200) {
                    Track::whereIn('id', $ids)->update([
                        'customs_declare_at' => Carbon::now(),
                    ]);
                    Log::info("Cron for --Send Bundles to Customs-- sent. $count bundles sent to Customs");
                } else {
                    $res = json_decode($response->getBody(), true);
                    $this->handleErrorResponse($res);
                }
            } catch (\Exception $e) {
                dd($e->getMessage());
                Log::error("Error sending bundles to customs: " . $e->getMessage());
            }
        }    }

    function handleErrorResponse($res) {
        Log::info("<-======================================================================================================================>");
        if (is_array($res) && isset($res['exception']) && !empty($res['exception'])) {
            if (is_array($res['exception']) && isset($res['exception']['validationError']) && !empty($res['exception']['validationError'])) {
                $trackings = [];
                foreach ($res['exception']['validationError'] as $tracking => $code) {
                    if ($code == '200') {
                        $trackings[] = $tracking;
                    } else {
                        Log::info("$tracking => $code");
                    }
                }
                Track::whereIn('tracking_code', $trackings)->update([
                    'customs_declare_at' => Carbon::now(),
                ]);
            } else {
                Log::info(json_encode($res));
            }
        } else {
            Log::info(json_encode($res));
        }
        Log::info("<======================================================================================================================->");
    }
}
