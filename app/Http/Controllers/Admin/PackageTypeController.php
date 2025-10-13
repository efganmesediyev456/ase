<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;

class PackageTypeController extends Controller
{
    protected $route = 'package_types';

    protected $view = [
        'name' => 'Package Types',
        'sub_title' => 'Package types when user upload invoice',
        'listColumns' => 8,
        'formColumns' => 6,
    ];

    protected $with = ['parent'];

    protected $list = [
        'icon' => [
            'type' => 'image',
        ],
        'translateOrDefault.name' => [
            'label' => 'Name'
        ],
        'parent.name' => [
            'label' => 'Parent'
        ]
    ];

    protected $fields = [
        [
            'name' => 'icon',
            'type' => 'image',
            'label' => 'Icon',
            'validation' => 'nullable|image',
        ],
        [
            'label' => 'Parent',
            'type' => 'select2',
            'name' => 'parent_id',
            'attribute' => 'name',
            'model' => 'App\Models\PackageType',
            'allowNull' => true,
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'validation' => 'required|string|min:3',
        ],
    ];
}
