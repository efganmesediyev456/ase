<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;

class FaqController extends Controller
{
    protected $view = [
        'listColumns' => 8,
        'sub_title' => 'Frequently asked questions',
    ];

    protected $list = [
        'question',
    ];

    protected $fields = [
        [
            'name' => 'question',
            'label' => 'Question',
            'type' => 'text',
            'validation' => 'required|string|min:5'
        ],
        [
            'name' => 'answer',
            'label' => 'Answer',
            'type' => 'summernote',
            'validation' => 'required|string|min:5'
        ]
    ];
}
