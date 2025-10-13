<?php

namespace App\Models;

use App\Models\AzeriExpress\AzeriExpressOffice;
use App\Models\Azerpost\AzerpostOffice;
use App\Models\Kargomat\KargomatOffice;
use App\Models\Surat\SuratOffice;
use App\Models\YeniPoct\YenipoctOffice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Filial extends Model
{
    use SoftDeletes;

    protected $table = "filials_v";
    public $dates = ['deleted_at'];
    protected $primaryKey = 'type_id';

    public static function getMach($address = NULL, $city = NULL, $region = NULL)
    {
        if (empty($address) && empty($city) && empty($region)) return NULL;
        //Find all maches
        $items = FilialKey::latest();
        $items = $items->whereNull('deleted_at');
        $items->where(function ($query) use ($address, $city, $region) {
            if (!empty($address)) {
                $query = $query->orWhereRaw("('" . $address . "' like concat('%',filial_keys.name,'%') and filial_keys.type='ADDRESS')");
            }
            if (!empty($city)) {
                $query = $query->orWhereRaw("('" . $city . "' like concat('%',filial_keys.name,'%') and filial_keys.type='CITY')");
            }
            if (!empty($region)) {
                $query = $query->orWhereRaw("('" . $region . "' like concat('%',filial_keys.name,'%') and filial_keys.type='REGION')");
            }
        });
        $items = $items->whereRaw("exists(select filials_v.id from filials_v where filials_v.deleted_at is NULL and filials_v.type_id=filial_keys.filial_type_id)");
        $items = $items->get();
        //----------------
        if (!$items) return NULL;
        //Assgin to each filial maches count
        $typeIdCounts = []; // filial maches count
        $typeIdCityCounts = []; // filial city mach count
        foreach ($items as $item) {
            $typeId = $item->filial_type_id;
            if (array_key_exists($typeId, $typeIdCounts)) {
                $typeIdCounts[$typeId]++;
            } else {
                $typeIdCounts[$typeId] = 1;
                $typeIdCityCounts[$typeId] = 0;
            }
            if ($item->type == 'CITY')
                $typeIdCityCounts[$typeId]++;
        }
        //----------------------------------
        //find if have city maches with not this city and remove this filials
        $qStr = "exists(select filial_keys.id from filial_keys where filial_keys.type='CITY' and filial_keys.deleted_at is NULL and filials_v.type_id=filial_keys.filial_type_id)";
        if (!empty($city)) {
            $qStr = "(" . $qStr . " and ";
            $qStr .= "not exists(select filial_keys.id from filial_keys where filial_keys.type='CITY' and filial_keys.deleted_at is NULL and filials_v.type_id=filial_keys.filial_type_id and ('" . $city . "' like concat('%',filial_keys.name,'%')))";
            $qStr .= ")";
        }
        $items = Filial::whereRaw($qStr);
        $items = $items->whereIn("filials_v.type_id", array_keys($typeIdCounts));
        $items = $items->get();
        if ($items) {
            foreach ($items as $item) {
                $typeId = $item->id;
                if (array_key_exists($typeId, $typeIdCounts)) {
                    unset($typeIdCounts[$typeId]);
                    unset($typeIdCityCounts[$typeId]);
                }
            }
        }
        //---------------------------------------
        //find now if we have city mach
        $haveCity = false;
        foreach ($typeIdCityCounts as $typeId => $typeIdCityCount) {
            if ($typeIdCityCount > 0) {
                if (($typeIdCounts[$typeId] - $typeIdCityCount) <= 0) { // remove if mach have only city and dont remove if filial have only city mach
                    $fkeys = FilialKey::whereNull('deleted_at')->where('filial_type_id', $typeId)->where('type', '!=', 'CITY')->get();
                    if ($fkeys && (count($fkeys) > 0)) {
                        unset($typeIdCounts[$typeId]);
                        unset($typeIdCityCounts[$typeId]);
                        continue;
                    }
                }
                $haveCity = true;
                continue;
            }
        }
        //----------------------
        //if we have city and dont mach city then exit
        if (!empty($city) && !$haveCity) {
            return NULL;
        }
        //------------------
        //if we have city mach then
        //remove filials with no city mach because city mach is more privilege
        if ($haveCity) { //
            foreach ($typeIdCityCounts as $typeId => $typeIdCityCount) {
                if ($typeIdCityCount <= 0) { // remove if mach have no city
                    unset($typeIdCounts[$typeId]);
                    unset($typeIdCityCounts[$typeId]);
                    continue;
                }
            }
        }
        //--------------------
        //find filial with maximum maches
        $maxTypeId = NULL;
        $maxTypeIdCount = 0;
        foreach ($typeIdCounts as $typeId => $typeIdCount) {
            if ($typeIdCount > $maxTypeIdCount) {
                $maxTypeIdCount = $typeIdCount;
                $maxTypeId = $typeId;
            }
        }
        //-----------------------
        if (!$maxTypeId) return NULL;
        $filial = Filial::where('type_id', $maxTypeId)->first();
        return $filial;
    }

    public function getWorkTimeAttribute()
    {
        return $this->getWorkTimeText();
    }

    public function getLunchTimeAttribute()
    {
        return $this->getLunchTimeText();
    }

    public function getLunchTimeText(){
        if (!$this->lunch_break_opening_time || !$this->lunch_break_closing_time) {
            return '';
        }

        if ($this->lunch_break_opening_time && $this->lunch_break_closing_time){
            return 'Nahar fasiləsi ' . substr($this->lunch_break_opening_time, 0, 5). '-' . substr($this->lunch_break_closing_time, 0, 5);
        }
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

    public static function findByTypeId($typeId)
    {
        $item = NULL;
        list($type, $id) = explode('-', $typeId);
        switch ($type) {
            case 'ASE':
                $item = DeliveryPoint::find($id);
                break;
            case 'AZPOST':
                $item = AzerpostOffice::find($id);
                break;
            case 'AZEXP':
                $item = AzeriExpressOffice::find($id);
                break;
            case 'SURAT':
                $item = SuratOffice::find($id);
                break;
            case 'YP':
                $item = YenipoctOffice::find($id);
                break;
            case 'KARGOMAT':
                $item = KargomatOffice::find($id);
                break;
            case 'UNKNOWN':
                $item = UnknownOffice::find($id);
                break;
        }
        return $item;
    }

    public static function setTrackFilial($track, $filial)
    {
        if (!$filial || !$track) return;
        switch ($filial->type) {
            case 'ASE':
                $track->store_status = $filial->fid;
                break;
            case 'AZPOST':
                $track->azerpost_office_id = $filial->fid;
                break;
            case 'AZEXP':
                $track->azeriexpress_office_id = $filial->fid;
                break;
            case 'SURAT':
                $track->surat_office_id = $filial->fid;
                break;
            case 'YP':
                $track->yenipoct_office_id = $filial->fid;
                break;
            case 'KARGOMAT':
                $track->kargomat_office_id = $filial->fid;
                break;
            case 'UNKNOWN':
                $track->unknown_office_id = $filial->fid;
                break;
        }
    }

    public static function createByType($type)
    {
        $item = NULL;
        switch ($type) {
            case 'ASE':
                $item = DeliveryPoint::create();
                break;
            case 'AZPOST':
                $item = AzerpostOffice::create();
                break;
            case 'AZEXP':
                $item = AzeriExpressOffice::create();
                break;
            case 'SURAT':
                $item = SuratOffice::create();
                break;
            case 'YP':
                $item = YenipoctOffice::create();
                break;
            case 'KARGOMAT':
                $item = KargomatOffice::create();
                break;
            case 'UNKNOWN':
                $item = UnknownOffice::create();
                break;
        }
        return $item;
    }

    public function getTypeNameAttribute()
    {
        return $this->type . '-' . $this->name;
    }

    public function getTypeIdNameAttribute()
    {
        return $this->type_id . ' ' . $this->name;
    }

    public function getIdAttribute()
    {
        return $this->attributes['type_id'];
    }


    public function getForeignIdAttribute()
    {
        return $this->attributes['foreign_id'];
    }


    public function getTypeIdAttribute()
    {
        return $this->type . '-' . $this->attributes['id'];
    }

    public function getLocationUrlAttribute()
    {
        return $this->latitude && $this->longitude ? 'https://maps.google.com/?q=' . $this->latitude . ',' . $this->longitude : NULL;
    }

    public function getRouteKeyName()
    {
        return 'type_id';
    }

    public function getAllKeysAttribute()
    {
        $str = '';
        foreach ($this->keys as $key) {
            if (empty($str)) $str = $key->name . '(' . $key->type[0] . ')';
            else $str .= ', ' . $key->name . '(' . $key->type[0] . ')';
        }
        return $str;
    }

    public function keys()
    {
        return $this->hasMany(FilialKey::class, 'filial_type_id', 'type_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function getCityNameAttribute()
    {
        if (!$this->city_id)
            return "";
        $city = $this->city();
        if ($city && $city->first())
            return $city->first()->name;
        return "";
    }

}
