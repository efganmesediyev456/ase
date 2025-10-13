<?php

namespace App\Models;

use App\Traits\ModelEventLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class FileDownload extends Model
{
    use SoftDeletes;
    protected $table = 'file_downloads';

    /**
     * @var array
     */
    public $dates = ['created_at'];

}
