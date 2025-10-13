<?php

namespace App\Traits;

trait Password
{
    /**
     * Set default crypt password
     *
     * @param $value
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = bcrypt($value);
    }
}
