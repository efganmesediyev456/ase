<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;

class EmailController extends Controller
{

    protected $view = [
        'formColumns' => 10,
        'sub_title' => 'Email Templates',
    ];

    protected $modelName = 'EmailTemplate';

    protected $list = [
        'key',
        'name',
        'content',
        'active'
    ];

    protected $fields = [

        [
            'name' => 'key',
            'label' => 'Key (unique)',
            'type' => 'text',
            'validation' => 'required|string|min:2|unique:email_templates,key'
        ],
        [
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'validation' => 'required|string|min:2'
        ],

        [
            'name' => 'content',
            'label' => 'Content',
            'type' => 'summernote',
            'validation' => 'required|string|min:2',
            'wrapperAttributes' => [
                'class' => ' col-md-9 campaign_content',
            ],
            'attributes' => [
                'rows' => 8
            ]
        ],

        [
            'type' => 'html',
            'html' => '<div class="col-md-3">
                                <h6>Variables you can use</h6>
                                <ul>
                                    <li><b>:id</b> : DB id number</li>
                                    <li><b>:user</b> : User`s fullname</li>
                                    <li><b>:code</b> : User`s ASE code</li>
                                    <li><b>:city</b> : User`s city name</li>
                                    <li><b>:filial_name</b> : User`s filial name</li>
                                    <li><b>:filial_address</b> : User`s filial address</li>
                                    <li><b>:track_code</b> : Tracking number</li>
                                     <li><b>:cwb</b> : ASE CWB number</li>
                                    <li><b>:price</b> : Delivery price $xx/xx₼</li>
                                    <li><b>:web_site</b> : Web Site name</li>
                                    <li><b>:country</b> : Country name</li>
                                    <li><b>:weight</b> : Weight</li>
                                </ul>
                            </div>',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"><br/></div>',
        ],
        [
            'name' => 'active',
            'label' => 'Active',
            'type' => 'checkbox',
        ],
    ];
}
