<?php

namespace App\Http\Resources\Ozon;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;

class WarehouseResource extends Resource
{
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */

    public function toArray($request): array
    {
        $data = $this->resource->transform(function ($warehouse) {
            $days = [1 => 'monday', 2 => 'tuesday', 3 => 'wednesday', 4=>'thursday', 5=>'friday', 6=>'saturday', 7=>'sunday'];
            $daySchedule = [];
            foreach ($days as $key => $day){
                $openingTime = $warehouse['data']->{$day . '_opening_time'} ?? null;
                $closingTime = $warehouse['data']->{$day . '_closing_time'} ?? null;

                if ($openingTime && $closingTime) {
                    $openingTimeFormatted = Carbon::parse($openingTime)->format('H:i');
                    $closingTimeFormatted = Carbon::parse($closingTime)->format('H:i');
                    $fromTo = $openingTimeFormatted . '-' . $closingTimeFormatted;
                } else {
                    $fromTo = null;
                }

                $daySchedule[] = [
                    "Day" => $key,
                    "IsHoliday" => $warehouse['data']->{$day.'_opening_time'} === null,
                    "FromTo" => $fromTo,
                    "BreakFromTo" => null,
                ];
            }
            return [
                "id" => $warehouse['data']->id,
                "uid" => $warehouse['uid'],
                "type_id" => $warehouse['type_id'],
                "name" =>$warehouse['name'],
                "name_en" => $warehouse['name_en'],
                "city" =>$warehouse['city'],
                "city_en" => $warehouse['city_en'],
                "country" => "Azərbaycan",
                "country_en" => "Azerbaijan",
                "description" =>$warehouse['description'],
                "description_en" => $warehouse['description_en'],
                "address" => $warehouse['address'],
                "address_en" => $warehouse['city_en'] .', '. $warehouse['address_en'],

                "HowToGet" => $warehouse['uid'],
                "StoragePeriod" => 0,
                "Latitude" => $warehouse['data']->latitude,
                "Longitude" =>  $warehouse['data']->longitude,
                "PostalCode" => $warehouse['zip_code'],
                "MinMaxRestrictionPrice" => [
                    "Min" => 0,
                    "Max" => 0
                ],
                "MinMaxRestrictionWeight" => [
                    "Min" => 0,
                    "Max" => 0
                ],
                "MinMaxRestrictionLength" => [
                    "Min" => 0,
                    "Max" => 0
                ],
                "MinMaxRestrictionWidth" => [
                    "Min" => 0,
                    "Max" => 0
                ],
                "MinMaxRestrictionHeight" => [
                    "Min" => 0,
                    "Max" => 0
                ],
                "MinMaxRestrictionDimensionSum" => [
                    "Min" => 0,
                    "Max" => 0
                ],
                "DaySchedule" => $daySchedule,
                "IsActive" => true,
            ];
        });
        return [
            'status' => true,
            'message' => "Warehouses fetched successfully!",
            'data' => $data,
        ];
    }
}
