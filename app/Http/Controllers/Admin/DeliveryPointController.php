<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\DeliveryPoint;

class DeliveryPointController extends Controller
{
    protected $route = 'delivery_points';
    protected $view = [
        'name' => "Delivery Point",
    ];

    protected $list = [
        'name',
        'description',
        'city_name' => [
            'label' => 'City',
        ],
        'address',
        'contact_name',
        'contact_phone',
    ];

    protected $fields = [
        [
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'validation' => 'required|string',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
        ],
        [
            'name' => 'description',
            'label' => 'Description',
            'type' => 'text',
            'validation' => 'required|string',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'label' => 'City',
            'type' => 'select2',
            'name' => 'city_id',
            'attribute' => 'name',
            'model' => 'App\Models\City',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'address',
            'label' => 'Address',
            'type' => 'textarea',
            'attributes' => [
                'rows' => '2',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'name' => 'contact_name',
            'label' => 'Contact name',
            'type' => 'text',
            'validation' => 'nullable|string',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
        ],
        [
            'name' => 'contact_phone',
            'label' => 'Contact phone',
            'type' => 'text',
            'validation' => 'nullable|string',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
        ],
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function indexObject()
    {
        $items = DeliveryPoint::paginate($this->limit);

        return $items;
    }
}
