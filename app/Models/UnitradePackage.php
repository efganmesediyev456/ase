<?php

namespace App\Models;

use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Model;

class UnitradePackage extends Model
{

    /**
     * @var array
     */
    protected $guarded = [];

//    protected $fillable = [
//        'package_id',
//        'track_id',
//        'uid',
//        'user_id',
//        'customer_id',
//        'type',
//        'package_code',
//        'delivery_number',
//        'warehouse_id',
//        'comment',
//        'is_liquid',
//        'is_door',
//        'seller_name',
//        'seller_email',
//        'seller_phone',
//        'seller_address',
//        'seller_ioss_number',
//        'seller_country',
//        'seller_city',
//        'buyer_city',
//        'buyer_country',
//        'buyer_phone_number',
//        'buyer_email_address',
//        'buyer_first_name',
//        'buyer_last_name',
//        'buyer_zip_code',
//        'buyer_pin_code',
//        'buyer_region',
//        'buyer_shipping_address',
//        'buyer_billing_address',
//        'invoice_currency',
//        'invoice_price',
//        'invoice_due_date',
//        'invoice_url',
//        'shipping_invoice_price',
//        'shipping_invoice_due',
//        'shipping_invoice_url',
//        'shipping_currency',
//        'shipping_cost',
//        'request_json',
//        'status',
//        'weight',
//    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function getBuyerFullNameAttribute(): string
    {
        return $this->buyer_first_name . ' ' . $this->buyer_last_name;
    }
}
