<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;

class CouponController extends Controller
{
    protected $with = ['categories'];

    protected $view = [
        'formColumns' => 10,
        'sub_title' => 'Our hot coupons for members',
    ];

    protected $list = [
        'image' => [
            'type' => 'image',
        ],
        'type_id' => [
            'label' => 'Type',
            'type' => 'radio',
            'options' => [
                'Coupon',
                'Sale',
            ],
        ],
        'store.name' => [
            'label' => 'Store',
        ],
        'categories' => [
            'label' => 'Category',
            'type' => 'category',
        ],
        'name',
        'end_at',
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
            'name' => 'type_id',
            'label' => "Type",
            'type' => 'select2_from_array',
            'options' => [
                'Coupon',
                'Sale',
            ],
            'allowNull' => false,
            'wrapperAttributes' => [
                'class' => 'form-group col-md-4',
            ],
            'validation' => 'required|integer',
        ],
        [
            'name' => 'code',
            'label' => "Code",
            'hint' => 'If type choose Coupon',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'nullable|string',
        ],

        [   // date_picker
            'name' => 'end_at',
            'type' => 'date_picker',
            'label' => 'End Date',
            'wrapperAttributes' => [
                'class' => 'col-lg-4',
            ],
            'validation' => 'required|date'
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
                'class' => 'form-group col-md-4',
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
            'validation' => 'nullable|string|min:10',
        ],
        [
            'name' => 'url',
            'label' => 'Web site address',
            'type' => 'url',
            'validation' => 'nullable|url',
        ],
    ];
}
