<?php

namespace App\Models;

use Carbon\Carbon;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\PackageOwner
 *
 * @property int $id
 * @property int $user_id
 * @property int $package_id
 * @property string|null $invoice
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Package $package
 * @property-read User $user
 * @method static Builder|PackageOwner whereCreatedAt($value)
 * @method static Builder|PackageOwner whereId($value)
 * @method static Builder|PackageOwner whereInvoice($value)
 * @method static Builder|PackageOwner whereNote($value)
 * @method static Builder|PackageOwner wherePackageId($value)
 * @method static Builder|PackageOwner whereUpdatedAt($value)
 * @method static Builder|PackageOwner whereUserId($value)
 * @mixin Eloquent
 * @method static Builder|PackageOwner newModelQuery()
 * @method static Builder|PackageOwner newQuery()
 * @method static Builder|PackageOwner query()
 */
class PackageOwner extends Model
{
    /**
     * @return BelongsTo
     */
    public function package()
    {
        return $this->belongsTo('App\Models\Package');
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }
}
