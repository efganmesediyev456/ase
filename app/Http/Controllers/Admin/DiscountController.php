<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;

class DiscountController extends Controller
{
    protected $view = [
        'name' => 'Discounts',
        'formColumns' => 10,
        'sub_title' => 'Discounts',
    ];

    protected $route = 'discounts';
    protected $modelName = 'Discount';

    protected $list = [
        'active',
        'name' => [
            'label' => 'Name',
        ],
        'warehouse.country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
        'percent' => [
            'label' => 'Percent',
        ],
        'start_at' => [
            'label' => 'Start time',
        ],
        'stop_at' => [
            'label' => 'Stop time',
        ],
        'created_at' => [
            'label' => 'CreatedAt',
            'type' => 'date',
            'order' => 'created_at',
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
            'label' => 'Percent',
            'type' => 'text',
            'name' => 'percent',
            'wrapperAttributes' => [
                'class' => ' col-md-1',
            ],
            'validation' => 'required|numeric|between:0,99.99',
        ],
        [
            'label' => 'Start time',
            'type' => 'datetime',
            'name' => 'start_at',
            'wrapperAttributes' => [
                'class' => ' col-md-3',
            ],
            'validation' => 'required',
        ],
        [
            'label' => 'Stop time',
            'type' => 'datetime',
            'name' => 'stop_at',
            'wrapperAttributes' => [
                'class' => ' col-md-3',
            ],
            'validation' => 'required|after_or_equal:start_at',
        ],
    ];

    public function __construct()
    {
        $this->fields[0]["default"] = 1;
        $this->fields[4]["default"] = date('Y-m-d H:i');
        $this->fields[5]["default"] = date('Y-m-d H:i');
        parent::__construct();
    }

}
