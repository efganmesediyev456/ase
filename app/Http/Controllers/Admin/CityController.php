<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;

class CityController extends Controller
{

    protected $view = [
        'sub_title' => 'Cities',
        'formColumns' => 8,
        'listColumns' => 6,
    ];

    protected $list = [
        'name',
    ];

    protected $fields = [

        [
            'label' => 'Name',
            'name' => 'name',
            'type' => 'text',
            'validation' => 'required|string|min:3',
        ],
        [
            'name' => 'address',
            'label' => 'Address',
            'type' => 'textarea',
            'allowNull' => true,
        ],
    ];
}
