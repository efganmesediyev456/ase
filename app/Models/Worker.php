<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use App\Traits\Password;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Worker extends Authenticatable
{
    use Password;
    use SoftDeletes;
    use ModelEventLogger;

    protected $with = ['warehouse'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
