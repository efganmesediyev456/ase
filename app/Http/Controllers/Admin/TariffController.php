<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Http\Requests;
use App\Models\Tariff;
use Request;
use Validator;

class TariffController extends Controller
{
    protected $view = [
        'name' => 'Tariffs',
        'formColumns' => 10,
        'sub_title' => 'Tariffs',
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

    protected $route = 'tariffs';
    protected $modelName = 'Tariff';

    protected $list = [
        'active' => [
            'label' => 'Active',
        ],
        'name' => [
            'label' => 'Name',
        ],
        'warehouse.country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
        'admin.name' => [
            'label' => 'Creator',
        ],
        'created_at' => [
            'label' => 'CreatedAt',
            'type' => 'date',
            'order' => 'created_at',
        ],
    ];

    protected $extraActions = [
        [
            'key' => 'id',
            'label' => 'Weights',
            'icon' => 'map',
            'route' => 'tariff_weights.index',
            'color' => 'success',
        ],
    ];

    protected $fields = [
        [
            'name' => 'is_active',
            'label' => 'Is Active',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-2',
            ],
            'default' => '1',
        ],
        [
            'label' => 'WareHouse',
            'type' => 'select2',
            'name' => 'warehouse_id',
            'attribute' => 'company_name,country.name',
            'model' => 'App\Models\Warehouse',
            'wrapperAttributes' => [
                'class' => ' col-md-3',
            ],
            'validation' => 'nullable|integer',
            'allowNull' => true,
        ],
        [
            'label' => 'Name',
            'type' => 'text',
            'name' => 'name',
            'wrapperAttributes' => [
                'class' => ' col-md-2',
            ],
            'validation' => 'nullable|string|max:50',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
        ],
    ];

    public function __construct()
    {
        //$this->fields[0]["default"]=1;
        parent::__construct();
    }

    public function indexObject()
    {
        $validator = Validator::make(Request::all(), [
            'warehouse_id ' => 'integer',
        ]);

        if ($validator->failed()) {
            Alert::error('Unexpected variables!');

            return redirect()->route("my.dashboard");
        }
        $items = Tariff::latest();


        if (Request::get('warehouse_id') != null) {
            $items->where('warehouse_id', Request::get('warehouse_id'));
        }
        $items = $items->orderBy('is_active', 'desc')->orderBy('warehouse_id', 'asc');
        $items = $items->paginate($this->limit);

        return $items;
    }

}
