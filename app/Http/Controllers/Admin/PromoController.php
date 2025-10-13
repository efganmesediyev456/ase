<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Http\Requests;
use App\Models\Promo;
use Request;
use Validator;

class PromoController extends Controller
{
    protected $view = [
        'name' => 'Promo codes',
        'formColumns' => 10,
        'sub_title' => 'Promo codes',
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

    protected $route = 'promos';
    protected $modelName = 'Promo';

    protected $list = [
        'active',
        'name' => [
            'label' => 'Name',
        ],
        'code' => [
            'label' => 'Code',
        ],
        'warehouse.country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
        'percent' => [
            'label' => 'Percent',
        ],
        'amount' => [
            'label' => 'Amount(AZN)',
        ],
        'weight' => [
            'label' => 'Weight(kg)',
        ],
        'activation_with_label' => [
            'label' => 'Activation',
        ],
        'start_at' => [
            'label' => 'Start time',
        ],
        'stop_at' => [
            'label' => 'Stop time',
        ],
        'num_to_use' => [
            'label' => 'Counts',
        ],
        'num_used' => [
            'label' => 'Counts used',
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
            'label' => 'Export [XLS]',
            'icon' => 'file-download',
            'route' => 'promo_logs.export',
            'query' => [
                'format' => 'Xlsx'
            ],
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
        [
            'label' => 'Code',
            'type' => 'text',
            'name' => 'code',
            'wrapperAttributes' => [
                'class' => ' col-md-1',
            ],
            'validation' => 'required|string|max:20',
        ],
        [
            'name' => 'activation',
            'label' => 'Activation',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.promo.activation',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'required',
        ],
        [
            'label' => 'Percent',
            'type' => 'text',
            'name' => 'percent',
            'wrapperAttributes' => [
                'class' => ' col-md-1',
            ],
            'validation' => 'required_without_all:amount,weight',
        ],
        [
            'label' => 'Amount(AZN)',
            'type' => 'text',
            'name' => 'amount',
            'wrapperAttributes' => [
                'class' => ' col-md-1',
            ],
            'validation' => 'required_without_all:percent,weight',
        ],
        [
            'label' => 'Weight(kg)',
            'type' => 'text',
            'name' => 'weight',
            'wrapperAttributes' => [
                'class' => ' col-md-1',
            ],
            'validation' => 'required_without_all:percent,amount',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
        ],
        [
            'label' => 'Count times',
            'type' => 'text',
            'name' => 'num_to_use',
            'wrapperAttributes' => [
                'class' => ' col-md-1',
            ],
            'validation' => 'numeric',
        ],
        [
            'label' => 'Start time',
            'type' => 'datetime',
            'name' => 'start_at',
            'wrapperAttributes' => [
                'class' => ' col-md-3',
            ],
        ],
        [
            'label' => 'Stop time',
            'type' => 'datetime',
            'name' => 'stop_at',
            'wrapperAttributes' => [
                'class' => ' col-md-3',
            ],
        ],
    ];

    public function __construct()
    {
        $this->fields[0]["default"] = 1;
        $this->fields[11]["default"] = date('Y-m-d H:i');
        $this->fields[12]["default"] = date('Y-m-d H:i');
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
        $items = Promo::latest();


        if (Request::get('warehouse_id') != null) {
            $items->where('warehouse_id', Request::get('warehouse_id'));
        }
        $items = $items->orderBy('is_active', 'desc')->orderBy('warehouse_id', 'asc')->orderBy('start_at', 'desc')->orderBy('stop_at', 'desc');
        $items = $items->paginate($this->limit);

        return $items;
    }

}
