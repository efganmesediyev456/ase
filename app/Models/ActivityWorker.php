<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Eloquent;
use Illuminate\Database\Eloquent\Model;

//use Illuminate\Database\Eloquent\SoftDeletes;


/**
 * App\Models\PackageCarrier
 *
 * @property int $name
 * @mixin Eloquent
 */
class ActivityWorker extends Model
{
//    use SoftDeletes;
    use ModelEventLogger;

    protected $table = 'v_activity_worker';
    protected $primaryKey = 'content_id';
}
