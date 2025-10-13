<?php

namespace App\Http\Controllers\Admin;

class ApplicationController extends Controller
{
    protected $can = [
        'create' => false,
        'edit' => false,
    ];

    protected $view = [
        'sub_title' => 'Applications',
        'formColumns' => 10,
    ];

    protected $list = [

        'vacancy_name',
        'name',
        'surname',
        'phone',
        'email',
        'file' => [
            'type' => 'url',
        ],

    ];
}