<?php

namespace App\Models\Extra;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Extra\House
 *
 * @property int $id
 * @property string $provider
 * @property int $custom_id
 * @property string|null $city
 * @property string $type
 * @property string $condition
 * @property float $area
 * @property int $number_of_rooms
 * @property string|null $place_or_district
 * @property string $uploaded_at
 * @property string|null $name
 * @property string|null $phone
 * @property Carbon|null $sold_at
 * @property string $url
 * @property int $price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|House newModelQuery()
 * @method static Builder|House newQuery()
 * @method static Builder|House query()
 * @method static Builder|House whereArea($value)
 * @method static Builder|House whereCity($value)
 * @method static Builder|House whereCondition($value)
 * @method static Builder|House whereCreatedAt($value)
 * @method static Builder|House whereCustomId($value)
 * @method static Builder|House whereDeletedAt($value)
 * @method static Builder|House whereId($value)
 * @method static Builder|House whereName($value)
 * @method static Builder|House whereNumberOfRooms($value)
 * @method static Builder|House wherePhone($value)
 * @method static Builder|House wherePlaceOrDistrict($value)
 * @method static Builder|House wherePrice($value)
 * @method static Builder|House whereProvider($value)
 * @method static Builder|House whereSoldAt($value)
 * @method static Builder|House whereType($value)
 * @method static Builder|House whereUpdatedAt($value)
 * @method static Builder|House whereUploadedAt($value)
 * @method static Builder|House whereUrl($value)
 * @mixin Eloquent
 */
class House extends Model
{
    protected $dates = ['checked_at', 'sold_at', 'deleted_at'];
}
