<?php

namespace App\Models;

use Dimsav\Translatable\Translatable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Watson\Rememberable\Rememberable;

/**
 * App\Models\Address
 *
 * @property int $id
 * @property int|null $warehouse_id
 * @property string|null $title
 * @property string|null $contact_name
 * @property string $address_line_1
 * @property string|null $address_line_2
 * @property string|null $phone
 * @property string|null $mobile
 * @property string $city
 * @property string|null $state
 * @property string|null $region
 * @property string $zip_code
 * @property string|null $passport
 * @property string|null $attention
 * @property string|null $reminder
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Warehouse|null $warehouse
 * @method static Builder|Address newModelQuery()
 * @method static Builder|Address newQuery()
 * @method static Builder|Address query()
 * @method static Builder|Address whereAddressLine1($value)
 * @method static Builder|Address whereAddressLine2($value)
 * @method static Builder|Address whereAttention($value)
 * @method static Builder|Address whereCity($value)
 * @method static Builder|Address whereContactName($value)
 * @method static Builder|Address whereCreatedAt($value)
 * @method static Builder|Address whereId($value)
 * @method static Builder|Address whereMobile($value)
 * @method static Builder|Address wherePassport($value)
 * @method static Builder|Address wherePhone($value)
 * @method static Builder|Address whereRegion($value)
 * @method static Builder|Address whereReminder($value)
 * @method static Builder|Address whereState($value)
 * @method static Builder|Address whereTitle($value)
 * @method static Builder|Address whereUpdatedAt($value)
 * @method static Builder|Address whereWarehouseId($value)
 * @method static Builder|Address whereZipCode($value)
 * @mixin Eloquent
 */
class Address extends Model
{
    use Translatable;
    use Rememberable;
    protected $with = ['translations'];
    public $translatedAttributes = ['attention'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
