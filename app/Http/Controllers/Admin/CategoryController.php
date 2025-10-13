<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;

class CategoryController extends Controller
{
    protected $view = [
        'sub_title' => 'Categories for store and coupons',
        'listColumns' => 8,
    ];

    protected $with = ['parent'];

    protected $list = [
        'icon' => [
            'type' => 'image',
        ],
        'name',
        'description',
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
            'model' => 'App\Models\Category',
            'allowNull' => true,
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'slug',
            'label' => 'Category Slug (URL)',
            'type' => 'text',
            'prefix' => '<i class="icon-unlink"></i>',
            'hint' => 'Will be automatically generated from your title, if left empty.',
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'validation' => 'required|string|min:3',
        ],
        [
            'name' => 'description',
            'label' => 'Description',
            'type' => 'textarea',
            'validation' => 'required|string|min:10',
        ],
        [
            'name' => 'meta_title',
            'label' => 'Meta title',
            'type' => 'text',
            'hint' => 'Meta title for SEO',
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'meta_description',
            'label' => 'Meta Description',
            'type' => 'textarea',
            'attributes' => [
                'rows' => 8,
            ],
            'hint' => 'Meta Description for SEO',
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'meta_keywords',
            'label' => 'Meta keywords',
            'type' => 'tag',
            'hint' => 'Meta keywords for SEO',
            'validation' => 'nullable|string',
        ],
    ];
}
