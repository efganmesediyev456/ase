<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;

class CareerController extends Controller
{

    protected $view = [
        'sub_title' => 'Online and offline careers',
        'formColumns' => 10,
    ];

    protected $list = [

        'is_active' => [
            'label' => 'Status',
        ],
        'name'
    ];

    protected $fields = [

        [
            'label' => 'Name',
            'name' => 'name',
            'type' => 'text',
            'validation' => 'required|string|min:3',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
            ],
        ],

        [
            'label' => 'City',
            'name' => 'city',
            'type' => 'text',
            'validation' => 'required|string|min:3',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
            ],
        ],

    ];

}