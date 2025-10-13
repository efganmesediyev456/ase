<?php

namespace App\Traits;

trait Plucky
{
    /**
     * Generate id, name list for select box
     *
     * @param $query
     *
     * @return mixed
     */
    public function scopePlucky($query)
    {
        return $query->pluck('name', 'id')->all();
    }
}
