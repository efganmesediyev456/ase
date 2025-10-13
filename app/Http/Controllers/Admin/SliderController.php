<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;

class SliderController extends Controller
{
    protected $view = [
        'formColumns' => 10,
        'sub_title' => 'Slider items in home page',
    ];

    protected $list = [
        'image' => [
            'type' => 'image',
        ],
        'name',
        'url',
    ];

    protected $fields = [
        [
            'name' => 'image',
            'type' => 'image',
            'label' => 'Background image',
            'validation' => 'nullable|image'
        ],
        [
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'validation' => 'required|string',
        ],
        [
            'name' => 'title',
            'label' => 'Title',
            'type' => 'summernote',
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'content',
            'label' => 'Content',
            'type' => 'summernote',
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'url',
            'label' => 'URL',
            'type' => 'url',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-8'
            ],
            'validation' => 'nullable|url',
        ],
        [
            'name' => 'button_label',
            'label' => 'Button label text',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-lg-4'
            ],
            'validation' => 'nullable|string',
        ],

    ];
}