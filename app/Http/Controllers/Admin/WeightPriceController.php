<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\WeightPrice;

class WeightPriceController extends Controller
{
    protected $view = [
	'name' => 'Weight Prices',
        'formColumns' => 20,
        'sub_title'   => 'Weight Prices',
        'search'      => [
            [
                'type'              => 'select2',
                'name'              => 'warehouse_id',
                'attribute'         => 'company_name,country.name',
                'model'             => 'App\Models\Warehouse',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull'         => 'All warehouses',
            ],
	],
    ];

    protected $route = 'weight_prices';
    protected $modelName = 'WeightPrice';

    protected $list = [	
       'active', 
	'name' 			=> [
	    'label' => 'Name',
	],
       'warehouse.country'     => [
            'label' => 'Country',
            'type'  => 'country',
        ],
	'weight_from' 		=> [
	    'label' => 'Weight greater or equal to',
	],
        'weight_to'              => [
            'label' => 'Weight less than',
        ],
        'shipping_amount'               => [
            'label' => 'Price',
        ],
	'created_at'            => [
            'label' => 'CreatedAt',
	    'type'  => 'date',
        ],
   ];

    protected $fields = [
        [
            'name'              => 'is_active',
            'label'             => 'Is Active',
            'type'              => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
        ],
        [
            'label'             => 'WareHouse',
            'type'              => 'select2',
            'name'              => 'warehouse_id',
            'attribute'         => 'company_name,country.name',
            'model'             => 'App\Models\Warehouse',
            'wrapperAttributes' => [
                'class' => ' col-md-3',
            ],
            'validation'        => 'nullable|integer',
            'allowNull'         => true,
        ],
        [
            'label'             => 'Name',
            'type'              => 'text',
            'name'              => 'name',
            'wrapperAttributes' => [
                'class' => ' col-md-2',
            ],
            'validation'        => 'nullable|string|max:50',
        ],
        [
            'name'              => 'weight_from',
            'label'             => 'Weight greater or equal to',
            'type'              => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation'        => 'nullable|numeric',
        ],
        [
            'name'              => 'weight_to',
            'label'             => 'Weight less than',
            'type'              => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation'        => 'nullable|numeric',
        ],
        [
            'name'              => 'shipping_amount',
            'label'             => 'Price',
            'type'              => 'text',
            'prefix'            => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation'        => 'nullable|numeric|required',
        ],

    ];

    public function __construct()
    {
	    $this->fields[0]["default"]=1;
            parent::__construct();
    }

    public function indexObject()
    {
        $validator = \Validator::make(\Request::all(), [
            'warehouse_id ' => 'integer',
        ]);

        if ($validator->failed()) {
            \Alert::error('Unexpected variables!');

            return redirect()->route("my.dashboard");
        }
	$items = WeightPrice::latest();


        if (\Request::get('warehouse_id') != null) {
            $items->where('warehouse_id', \Request::get('warehouse_id'));
        }
	$items = $items->orderBy('is_active','desc')->orderBy('warehouse_id','asc')->orderBy('weight_from','asc')->orderBy('weight_to','asc');
        $items = $items->paginate($this->limit);

        return $items;
    }
};
