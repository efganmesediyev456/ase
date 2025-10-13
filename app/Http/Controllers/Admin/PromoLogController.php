<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Exports\Admin\PromoLogsExport;
use App\Http\Requests;
use App\Models\PromoLog;
use Excel;
use Request;
use Validator;

class PromoLogController extends Controller
{
    protected $view = [
        'name' => 'Promo logs',
        'formColumns' => 10,
        'sub_title' => 'Promo logs',
        'search' => [
            [
                'type' => 'select2',
                'name' => 'warehouse_id',
                'attribute' => 'company_name,country.name',
                'model' => 'App\Models\Warehouse',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'All warehouses',
            ],
        ],
    ];

    protected $route = 'promo_logs';
    protected $modelName = 'PromoLog';
    protected $actions = [];

    protected $list = [
        'promo.active' => [
            'label' => 'Active',
        ],
        'promo.name' => [
            'label' => 'Name',
        ],
        'promo.code' => [
            'label' => 'Code',
        ],
        'warehouse.country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
        'promo.activation_with_label' => [
            'label' => 'Activation',
        ],
        'num_used' => [
            'label' => 'Num',
        ],
        'package.custom_id' => [
            'label' => 'Package CWB #',
        ],
        'package.weight_with_type' => [
            'label' => 'Weight',
        ],
        'package.merged_delivery_price' => [
            'label' => 'Delivery price',
        ],
        'package.discount_percent_with_label' => [
            'label' => 'Discount',
        ],
        'package.promo_discount_with_label' => [
            'label' => 'Promo',
        ],
        'package.ulduzum_discount_percent_with_label' => [
            'label' => 'Ulduzum',
        ],
        'package.merged_delivery_price_discount' => [
            'label' => 'Delivery Price (with discount)',
        ],
        'user.fullname' => [
            'label' => 'User',
        ],
        'created_at' => [
            'label' => 'CreatedAt',
            'type' => 'date',
            'order' => 'created_at',
        ],
    ];

    public function indexObject()
    {
        $validator = Validator::make(Request::all(), [
            'warehouse_id ' => 'integer',
        ]);

        if ($validator->failed()) {
            Alert::error('Unexpected variables!');

            return redirect()->route("my.dashboard");
        }
        $items = PromoLog::latest();


        if (Request::get('warehouse_id') != null) {
            $items->where('warehouse_id', Request::get('warehouse_id'));
        }
        $items = $items->orderBy('created_at', 'desc');
        $items = $items->paginate($this->limit);

        return $items;
    }

    public function export($items = null)
    {
        $items = PromoLog::where('promo_id', $items)->get();

        $type = 'Xlsx';
        $ext = 'Xlsx';

        return Excel::download(new PromoLogsExport($items), 'promo_logs_' . uniqid() . '.' . $ext, $type);
    }

}
