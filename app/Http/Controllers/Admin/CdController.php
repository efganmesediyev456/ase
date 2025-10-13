<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Http\Requests;
use App\Models\CD;
use Request;
use Validator;

class CdController extends Controller
{
    protected $view = [
        'name' => 'Courier Delivery',
        'formColumns' => 20,
        'sub_title' => 'Courier Delivery',
        'search' => [
            [
                'name' => 'status',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.cd.status',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All Status',
            ],
            [
                'type' => 'select2',
                'name' => 'courier_id',
                'attribute' => 'name',
                'model' => 'App\Models\Courier',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'All couriers',
            ],
            [
                'type' => 'select2',
                'name' => 'user_id',
                'attribute' => 'full_name,custom_id',
                'model' => 'App\Models\User',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'All Users',
            ],
        ],
    ];

    protected $route = 'cds';
    protected $modelName = 'CD';

    protected $list = [
        'user.full_name' => [
            'label' => 'User',
        ],
        'courier.name' => [
            'label' => 'Courier',
        ],
        'name' => [
            'label' => 'Name',
        ],
        'phone' => [
            'label' => 'Phone',
        ],
        'address' => [
            'label' => 'Address',
        ],
        'status' => [
            'status' => [
                'label' => 'Status',
                'type' => 'select-editable',
                'editable' => [
                    'route' => 'packages.ajax',
                    'type' => 'select',
                    'sourceFromConfig' => 'ase.attributes.package.statusWithLabel',
                ],
            ],
            'paid' => [
                'label' => 'Paid',
                'type' => 'paid',
                'editable' => [
                    'route' => 'packages.ajax',
                    'type' => 'select',
                    'sourceFromConfig' => 'ase.attributes.package.paidWithLabel',
                ],
            ],
            'paid_by' => [
                'label' => 'By',
            ],
            'created_at' => [
                'label' => 'CreatedAt',
                'type' => 'date',
            ],
        ]
    ];

    protected $fields = [
        [
            'label' => 'User',
            'type' => 'select2',
            'name' => 'user_id',
            'attribute' => 'full_name,customer_id',
            'model' => 'App\Models\User',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'required|integer',
            'allowNull' => true,
            'attributes' => [
                'data-validation' => 'required',
                'class' => 'select2-ajax',
                'data-url' => '/search-users',
            ],
        ],
        [
            'label' => 'Courier',
            'type' => 'select2',
            'name' => 'courier_id',
            'attribute' => 'name',
            'model' => 'App\Models\Courier',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|integer',
            'allowNull' => true,
        ],
        [
            'name' => 'status',
            'label' => 'Status',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.cd.status',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'label' => 'Name',
            'type' => 'text',
            'name' => 'name',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'label' => 'Phone',
            'type' => 'text',
            'name' => 'phone',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'label' => 'Address',
            'type' => 'textarea',
            'name' => 'address',
            'attributes' => [
                'rows' => '4',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'label' => 'User Comment',
            'type' => 'textarea',
            'name' => 'user_comment',
            'attributes' => [
                'rows' => '4',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'delivery_price',
            'label' => 'Delivery Price',
            'type' => 'text',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|numeric|required',
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
            'status ' => 'integer',
            'courier_id ' => 'integer',
            'user_id ' => 'integer',
        ]);

        if ($validator->failed()) {
            Alert::error('Unexpected variables!');

            return redirect()->route("my.dashboard");
        }
        $items = CD::latest();


        if (Request::get('status') != null) {
            $items->where('status', Request::get('status'));
        }
        //$items = $items->orderBy('status','asc')->orderBy('warehouse_id','asc')->orderBy('weight_from','asc')->orderBy('weight_to','asc');
        $items = $items->orderBy('created_at', 'desc');
        $items = $items->paginate($this->limit);

        return $items;
    }
}
