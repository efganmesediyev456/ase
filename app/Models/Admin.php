<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use App\Traits\Password;
use App\Traits\Plucky;
use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laratrust\Traits\LaratrustUserTrait;

/**
 * App\Models\Admin
 *
 * @property int $id
 * @property int|null $role_id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $avatar
 * @property string|null $remember_token
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|Admin onlyTrashed()
 * @method static Builder|Admin plucky()
 * @method static bool|null restore()
 * @method static Builder|Admin whereAvatar($value)
 * @method static Builder|Admin whereCreatedAt($value)
 * @method static Builder|Admin whereDeletedAt($value)
 * @method static Builder|Admin whereEmail($value)
 * @method static Builder|Admin whereId($value)
 * @method static Builder|Admin whereName($value)
 * @method static Builder|Admin wherePassword($value)
 * @method static Builder|Admin whereRememberToken($value)
 * @method static Builder|Admin whereRoleId($value)
 * @method static Builder|Admin whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Admin withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Admin withoutTrashed()
 * @mixin Eloquent
 * @property-read Collection|Permission[] $permissions
 * @property-read Role|null $role
 * @property-read Collection|Role[] $roles
 * @method static Builder|Admin orWherePermissionIs($permission = '')
 * @method static Builder|Admin orWhereRoleIs($role = '', $team = null)
 * @method static Builder|Admin wherePermissionIs($permission = '', $boolean = 'and')
 * @method static Builder|Admin whereRoleIs($role = '', $team = null, $boolean = 'and')
 * @property int $show_menu
 * @property-read Collection|City[] $cities
 * @property-read int|null $cities_count
 * @property-read int|null $permissions_count
 * @property-read int|null $roles_count
 * @method static Builder|Admin newModelQuery()
 * @method static Builder|Admin newQuery()
 * @method static Builder|Admin query()
 * @method static Builder|Admin whereShowMenu($value)
 */
class Admin extends Authenticatable
{
    use SoftDeletes;
    use Plucky;
    use Password;
    use LaratrustUserTrait;
    use ModelEventLogger;

    /**
     * @var string
     */
    public $uploadDir = 'uploads/admin/';
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    /**
     * @var array
     */
    protected $fillable = ['name', 'email', 'password', 'role_id', 'show_menu'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @param $value
     * @return string
     */
    public function getAvatarAttribute($value)
    {
        return $value ? asset($this->uploadDir . $value) : asset(config('ase.default.avatar'));
    }

    public function getStoreStatusLabelAttribute($value)
    {
	if(!$this->delivery_point) return '';
	return $this->delivery_point->name;
    }

    public function delivery_point()
    {
        return $this->belongsTo('App\Models\DeliveryPoint','store_status');
    }

    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    public function cities()
    {
        return $this->belongsToMany(City::class, 'admin_city');
    }
}
