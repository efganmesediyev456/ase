<?php

namespace App\Models\AzeriExpress;

use App\Models\City;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AzeriExpressOffice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'foreign_id',
        'city_id',
        'name',
        'name_en',
        'description',
        'description_en',
        'address',
        'address_en',
        'latitude',
        'longitude',
        'contact_phone',
        'contact_name'
    ];
    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function getWorkTimeAttribute()
    {
        return $this->getWorkTimeText();
    }

    public function getWorkTimeText()
    {
        if (
            !$this->monday_opening_time && !$this->monday_closing_time
            && !$this->tuesday_opening_time && !$this->tuesday_closing_time
            && !$this->wednesday_opening_time && !$this->wednesday_closing_time
            && !$this->thursday_opening_time && !$this->thursday_closing_time
            && !$this->friday_opening_time && !$this->friday_closing_time
            && !$this->saturday_opening_time && !$this->saturday_closing_time
            && !$this->sunday_opening_time && !$this->sunday_closing_time
        ) {
            return '';
        }

        if (
            $this->monday_opening_time == $this->tuesday_opening_time && $this->monday_closing_time == $this->tuesday_closing_time
            && $this->monday_opening_time == $this->wednesday_opening_time && $this->monday_closing_time == $this->wednesday_closing_time
            && $this->monday_opening_time == $this->thursday_opening_time && $this->monday_closing_time == $this->thursday_closing_time
            && $this->monday_opening_time == $this->friday_opening_time && $this->monday_closing_time == $this->friday_closing_time
            && $this->monday_opening_time == $this->saturday_opening_time && $this->monday_closing_time == $this->saturday_closing_time
            && $this->monday_opening_time == $this->sunday_opening_time && $this->monday_closing_time == $this->sunday_closing_time
        ) {
            return 'Hər gün saat ' . substr($this->monday_opening_time, 0, 5) . '-' . substr($this->monday_closing_time, 0, 5);
        }

        if (
            $this->monday_opening_time == $this->tuesday_opening_time && $this->monday_closing_time == $this->tuesday_closing_time
            && $this->monday_opening_time == $this->wednesday_opening_time && $this->monday_closing_time == $this->wednesday_closing_time
            && $this->monday_opening_time == $this->thursday_opening_time && $this->monday_closing_time == $this->thursday_closing_time
            && $this->monday_opening_time == $this->friday_opening_time && $this->monday_closing_time == $this->friday_closing_time
            && $this->monday_opening_time == $this->saturday_opening_time && $this->monday_closing_time == $this->saturday_closing_time
        ) {
            $str = 'bazar ertəsi - şənbə saat ' . substr($this->monday_opening_time, 0, 5) . '-' . substr($this->monday_closing_time, 0, 5);
            if ($this->sunday_opening_time && $this->sunday_closing_time) {
                $str .= ' bazar günü ' . substr($this->sunday_opening_time, 0, 5) . '-' . substr($this->sunday_closing_time, 0, 5);
            }
            return $str;
        }

        if (
            $this->monday_opening_time == $this->tuesday_opening_time && $this->monday_closing_time == $this->tuesday_closing_time
            && $this->monday_opening_time == $this->wednesday_opening_time && $this->monday_closing_time == $this->wednesday_closing_time
            && $this->monday_opening_time == $this->thursday_opening_time && $this->monday_closing_time == $this->thursday_closing_time
            && $this->monday_opening_time == $this->friday_opening_time && $this->monday_closing_time == $this->friday_closing_time
        ) {
            $str = 'Həftə içi saat ' . substr($this->monday_opening_time, 0, 5) . '-' . substr($this->monday_closing_time, 0, 5);
            if (
                !$this->saturday_opening_time && !$this->saturday_closing_time
                && !$this->sunday_opening_time && !$this->sunday_closing_time
            ) {
                return $str;
            }
            if ($this->saturday_opening_time == $this->sunday_opening_time && $this->saturday_closing_time == $this->sunday_closing_time) {
                $str .= ' şənbə və bazar ' . substr($this->saturday_opening_time, 0, 5) . '-' . substr($this->saturday_closing_time, 0, 5);
                return $str;
            }
            if ($this->saturday_opening_time && $this->saturday_closing_time) {
                $str .= ' şənbə günü ' . substr($this->saturday_opening_time, 0, 5) . '-' . substr($this->saturday_closing_time, 0, 5);
            }
            if ($this->sunday_opening_time && $this->sunday_closing_time) {
                $str .= ' bazar günü ' . substr($this->sunday_opening_time, 0, 5) . '-' . substr($this->sunday_closing_time, 0, 5);
            }
            return $str;
        }
        $str = '';
        if ($this->monday_opening_time && $this->monday_closing_time)
            $str .= ' bazar ertəsi ' . substr($this->monday_opening_time, 0, 5) . '-' . substr($this->monday_closing_time, 0, 5);
        if ($this->tuesday_opening_time && $this->tuesday_closing_time)
            $str .= ' çərşənbə axşamı ' . substr($this->tuesday_opening_time, 0, 5) . '-' . substr($this->tuesday_closing_time, 0, 5);
        if ($this->wednesday_opening_time && $this->wednesday_closing_time)
            $str .= ' çərşənbə ' . substr($this->wednesday_opening_time, 0, 5) . '-' . substr($this->wednesday_closing_time, 0, 5);
        if ($this->thursday_opening_time && $this->thursday_closing_time)
            $str .= ' cümə axşamı ' . substr($this->thursday_opening_time, 0, 5) . '-' . substr($this->thursday_closing_time, 0, 5);
        if ($this->friday_opening_time && $this->friday_closing_time)
            $str .= ' cümə ' . substr($this->friday_opening_time, 0, 5) . '-' . substr($this->friday_closing_time, 0, 5);
        if ($this->saturday_opening_time && $this->saturday_closing_time)
            $str .= ' şənbə ' . substr($this->saturday_opening_time, 0, 5) . '-' . substr($this->saturday_closing_time, 0, 5);
        if ($this->sunday_opening_time && $this->sunday_closing_time)
            $str .= ' bazar ' . substr($this->sunday_opening_time, 0, 5) . '-' . substr($this->sunday_closing_time, 0, 5);
        return $str;
    }
}
