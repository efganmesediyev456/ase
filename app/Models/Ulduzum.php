<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ulduzum extends Model
{
    use SoftDeletes;
    use ModelEventLogger;

    protected $table = "ulduzums";
    //public $width = ['warehouse'];
    public $dates = ['deleted_at'];//, 'start_at', 'stop_at'];

    public function getCompletedttribute()
    {
        if ($this->is_completed)
            return 'YES';
        return 'NO';
    }

}
