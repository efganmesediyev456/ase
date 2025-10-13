<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;

class ProductController extends Controller
{
    protected $with = ['categories'];

    protected $view = [
        'formColumns' => 10,
        'sub_title' => 'Our sale products for members',
    ];

    protected $list = [
        'image' => [
            'type' => 'image',
        ],
        'store.name' => [
            'label' => 'Store',
        ],
        'categories' => [
            'label' => 'Category',
            'type' => 'category',
        ],
        'sale',
        'price',
        'name',
        'url' => [
            'type' => 'url',
        ],
    ];

    protected $fields = [
        [
            'name' => 'image',
            'label' => 'Cover image',
            'type' => 'image',
            'validation' => 'nullable|image',
        ],
        [
            'label' => "Categories",
            'type' => 'select2_multiple',
            'name' => 'categories',
            'entity' => 'categories',
            'attribute' => 'name',
            'model' => 'App\Models\Category',
            'pivot' => true,
            'validation' => 'nullable|array',
        ],

        [
            'name' => 'sale',
            'label' => "Sale",
            'hint' => 'You can enter with % or currency',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
            ],
            'validation' => 'required|string',
        ],
        [
            'name' => 'price',
            'label' => "Current Price",
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
            ],
            'validation' => 'required|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12 clearfix"></div>'
        ],
        [
            'label' => 'Store',
            'type' => 'select2',
            'name' => 'store_id',
            'attribute' => 'name',
            'model' => 'App\Models\Store',
            'allowNull' => true,
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'name',
            'label' => 'Title',
            'type' => 'text',
            'validation' => 'required|string|min:3',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-8',
            ],
        ],
        [
            'name' => 'description',
            'label' => 'Description',
            'type' => 'summernote',
            'validation' => 'required|string|min:5',
        ],
        [
            'name' => 'url',
            'label' => 'Web site address',
            'type' => 'url',
            'validation' => 'required|url',
        ]
    ];
}
