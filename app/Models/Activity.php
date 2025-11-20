<?php

namespace App\Models;

use App\Models\Surat\SuratOffice;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * App\Models\Activity
 *
 * @property int $id
 * @property int $admin_id
 * @property int $content_id
 * @property string $content_type
 * @property string $action
 * @property string $description
 * @property string $details
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Admin $admin
 * @method static Builder|Activity newModelQuery()
 * @method static Builder|Activity newQuery()
 * @method static Builder|Activity query()
 * @method static Builder|Activity whereAction($value)
 * @method static Builder|Activity whereAdminId($value)
 * @method static Builder|Activity whereContentId($value)
 * @method static Builder|Activity whereContentType($value)
 * @method static Builder|Activity whereCreatedAt($value)
 * @method static Builder|Activity whereDescription($value)
 * @method static Builder|Activity whereDetails($value)
 * @method static Builder|Activity whereId($value)
 * @method static Builder|Activity whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Activity extends Model
{
    protected $fillable = [
        'admin_id',
        'worker_id',
        'user_id',
        'courier_id',
        'content_id',
        'content_type',
        'action',
        'description',
        'details',
        'ip',
        'user_agent',
    ];

    protected $with = ['admin', 'worker'];

    public function content()
    {
        return $this->morphTo(__FUNCTION__, 'content_type', 'content_id');
    }

    // helper accessor
    public function getContentCodeAttribute()
    {
        if ($this->content_type === 'App\Models\Track') {
            return $this->content->tracking_code ?? null;
        }

        if ($this->content_type === 'App\Models\Package') {
            return $this->content->custom_id ?? null;
        }

        return null;
    }

    public function getPartnerNameAttribute()
    {
        if ($this->content_type === 'App\Models\Track') {
            return $this->content->partner->name ?? null;
        }

        return null;
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function courier()
    {
        return $this->belongsTo(Courier::class)->withTrashed();
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class)->withTrashed();
    }


    public function getDataAttribute()
    {
        $arr = json_decode($this->attributes['details'], true);
        $data = "<ul>";
        foreach ($arr as $key => $value) {

            if ($key == 'parent_id' and(optional(auth()->user()->role)->id==10 or optional(auth()->user()->role)->id==26 )) continue;

            $str = '';
            if ($key == 'user_id' || $key == 'parent_id') {
//            if ($key == 'user_id') {
                $user = User::find($value);
                if ($user) {
                    $str = " (" . $user->full_name . ")";
		} else {
                    $str = " (NOT FOUND " . $value . ")";
		}
	    }
            if ($key == 'city_id') {
                $city = City::find($value);
                if ($city && $city->first()) {
                    $str = " (" . $city->first()->name . ")";
                }
            }
            if ($key == 'courier_id') {
                $courier = Courier::find($value);
                if ($courier) {
                    $str = " (" . $courier->name . ")";
                }
            }if ($key == 'shelf_id') {
                $shelf = CourierShelf::find($value);
                if ($shelf) {
                    $str = " (" . $shelf->name . ")";
                }
            }
            if ($key == 'courier_delivery_id') {
                $cd = CD::withTrashed()->where('id', $value)->first();
                $courier = null;
                if ($cd) {
                    $courier = $cd->courier;
                    $str = " (";
                    if ($courier) {
                        $str .= " courier: " . $courier->name . " ";
                    }
                    $str .= " status: " . $cd->status_with_label . " ";
                    if (!empty($cd->courier_comment)) {
                        $str .= " comm: " . $cd->courier_comment . " ";
                    }
                    if ($cd->deleted_at) {
                        $str .= " deleted: " . $cd->deleted_at . " ";
                    }

                    $str .= ")";
                }
            }
            if ($key == 'azeri_express_office_id') {
                $ae = AzeriExpress\AzeriExpressOffice::withTrashed()->where('id', $value)->first();
                if ($ae) {
                    $str = " (" . $ae->description . ")";
                }
            }
            if ($key == 'azerpost_office_id') {
                $ap = Azerpost\AzerpostOffice::withTrashed()->where('id', $value)->first();
                if ($ap) {
                    $str = " (" . $ap->name . ")";
                }
            }
            if ($key == 'surat_office_id') {
                $ap = SuratOffice::withTrashed()->where('id', $value)->first();
                if ($ap) {
                    $str = " (" . $ap->name . ")";
                }
            }
            if ($key == 'store_status') {
                $dp = DeliveryPoint::withTrashed()->where('id', $value)->first();
                if ($dp) {
                    $str = " (" . $dp->description . ")";
                }
            }

            switch ($this->content_type) {
                case 'App\Models\Package':
                    switch ($key) {
                        case 'status':
                            $str = " (" . config('ase.attributes.package.status')[$value] . ")";
                            break;
                        case 'paid':
                            $str = " (" . config('ase.attributes.package.paid')[$value] . ")";
                            break;
                    }
                    break;
                case 'App\Models\CD':
                    switch ($key) {
                        case 'status':
                            $str = " (" . config('ase.attributes.cd.status')[$value] . ")";
                            break;
                        case 'paid':
                            $str = " (" . config('ase.attributes.yes_no')[$value] . ")";
                            break;
                        case 'recieved':
                            $key = "Money received";
                            $str = " (" . config('ase.attributes.yes_no')[$value] . ")";
                            break;
                    }
                    break;
                case 'App\Models\Track':
                    if ($key == 'status' && $value) {
                        $str = " (" . config('ase.attributes.track.status')[$value] . ")";
                    }
                    break;
            }

            if (is_string($value)) {
                $data .= "<li><b>" . $key . "</b> : <i>" . str_limit($value, 130) . $str . "</i></li>";
            } else if (is_array($value)) {
                foreach ($value as $_key => $_value) {
                    $data .= "<li><b>" . $_key . "</b> : <i>" . str_limit($_value, 130) . "</i></li>";
                }
            } else {
                $data .= "<li><b>" . $key . "</b> : <i>" . $value . $str . "</i></li>";
            }
        }
        $data .= "</ul>";

        return $data;
    }
}
