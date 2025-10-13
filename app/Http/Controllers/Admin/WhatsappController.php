<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;

class WhatsappController extends Controller
{
    protected $can = [
//        'update' => false,
    ];
    protected $view = [
        'formColumns' => 10,
        'sub_title' => 'Whatsapp Templates',
    ];

    protected $modelName = 'WhatsappTemplate';

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
            'validation' => 'required|string|min:2|unique:whatsapp_templates,key',
        ],
        [
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'validation' => 'required|string|min:2',
        ],
        [
            'name' => 'content',
            'label' => 'Content for Whatsapp',
            'type' => 'textarea',
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
                                    <li><b>:package_city</b> : Package`s city name</li>
                                    <li><b>:filial_name</b> : User`s azeri filial name</li>
                                    <li><b>:filial_address</b> : User`s filial address</li>
                                    <li><b>:filial_url</b> : User`s filial location url</li>
                                    <li><b>:filial_work_time</b> : User`s filial work time</li>
                                    <li><b>:package_filial_name</b> : Package`s azeri filial name</li>
                                    <li><b>:package_filial_address</b> : Package`s filial address</li>
                                    <li><b>:package_filial_contact_name</b> : Package`s filial contact name</li>
                                    <li><b>:package_filial_contact_phone</b> : Package`s filial contact phone</li>
                                    <li><b>:package_filial_url</b> : Package`s filial location url</li>
                                    <li><b>:package_filial_work_time</b> : Package`s filial work time</li>
                                    <li><b>:package_filial_lunch_time</b> : Package`s filial lunch time</li>
                                    <li><b>:track_filial_name</b> : Track`s azeri filial name</li>
                                    <li><b>:incustom_url</b> : in custom url</li>
                                    <li><b>:broker_url</b> : broker payment url</li>
                                    <li><b>:incustom_price</b> : in custom price</li>
                                    <li><b>:track_filial_address</b> : Track`s filial address</li>
                                    <li><b>:track_filial_contact_name</b> : Track`s filial contact name</li>
                                    <li><b>:track_filial_contact_phone</b> : Track`s filial contact phone</li>
                                    <li><b>:track_filial_url</b> : Track`s filial location url</li>
                                    <li><b>:track_filial_work_time</b> : Track`s filial work time</li>
                                    <li><b>:track_filial_lunch_time</b> : Track`s filial lunch time</li>
                                    <li><b>:track_code</b> : Package Tracking number</li>
                                     <li><b>:cwb</b> : Package ASE CWB number </li>
                                     <li><b>:cwb</b> : Track tracking code</li>
                                     <li><b>:label_pdf</b> : Track Label pdf url</li>
                                     <li><b>:fin_url</b> : Track Fin change url</li>
                                     <li><b>:pay_url</b> : Track Payment url</li>
                                     <li><b>:paid</b> : Track Payment status</li>
                                    <li><b>:price</b> : Delivery price $xx/xx₼ </li>
                                    <li><b>:web_site</b> : Web Site name</li>
                                    <li><b>:country</b> : Country name</li>
                                    <li><b>:weight</b> : Weight</li>
                                </ul>
                            </div>',
        ],

        [
            'name' => 'content_sms',
            'label' => 'Content for SMS',
            'type' => 'textarea',
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
            'html' => '<div class="form-group col-lg-12 mt-10"><br/></div>',
        ],
        [
            'name' => 'active',
            'label' => 'Active',
            'type' => 'checkbox',
        ],
    ];
}
