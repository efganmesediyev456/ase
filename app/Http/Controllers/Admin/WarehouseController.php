<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\Warehouse;

class WarehouseController extends Controller
{
    protected $notificationKey = 'company_name';
    protected $createRedirection = 'addresses.create';
    protected $extraActions = [
        [
            'route' => 'addresses.index',
            'key' => 'id',
            'label' => 'Addresses',
            'icon' => 'map',
            'color' => 'success',
        ],
        [
            'route' => 'workers.index',
            'key' => 'id',
            'label' => 'Workers',
            'icon' => 'users',
            'color' => 'success',
        ],
    ];

    protected $list = [
        'country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
        'company_name',
        'per_week' => [
            'label' => 'Flies'
        ],
        'addresses_count' => [
            'label' => 'Addresses'
        ],
    ];

    protected $fields = [
        [
            'label' => 'Country',
            'type' => 'select2',
            'name' => 'country_id',
            'attribute' => 'name',
            'model' => 'App\Models\Country',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-5',
            ],
            'validation' => 'required|integer',
        ],
        [
            'name' => 'company_name',
            'label' => 'Company name',
            'type' => 'text',
            'validation' => 'required|string',
            'wrapperAttributes' => [
                'class' => 'col-md-5',
            ],
        ],
        [
            'name' => 'per_week',
            'label' => 'Flies per week',
            'type' => 'text',
            'validation' => 'required|string',
            'prefix' => '<i class="icon-airplane2"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12 clearfix"></div>'
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12 clearfix"><h3 class="text-center">Custom panel login for the bot</h3></div>'
        ],
        [
            'name' => 'panel_login',
            'label' => 'Login',
            'type' => 'text',
            'validation' => 'nullable|string',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
            ],
        ],
        [
            'name' => 'panel_password',
            'label' => 'Password',
            'type' => 'text',
            'validation' => 'nullable|string',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12 clearfix"><h3 class="text-center">Settings</h3></div>'
        ],
        [
            'name' => 'parcelling',
            'label' => 'Parcelling',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
        ],
        [
            'name' => 'package_processing',
            'label' => 'Package Processing',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
        ],
        [
            'name' => 'auto_print',
            'label' => 'Auto Print (Parcel Processing)',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
        ],
        [
            'name' => 'auto_print_invoice',
            'label' => 'Auto Print Invoice (Parcel Processing)',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
        ],
        [
            'name' => 'auto_print_pp',
            'label' => 'Auto Print (Package processing)',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
        ],
        [
            'name' => 'auto_print_pp_invoice',
            'label' => 'Auto Print Invoice (Package processing)',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
        ],
        [
            'name' => 'allow_make_fake_invoice',
            'label' => 'Fake invoice',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
        ],
        [
            'name' => 'no_invoice',
            'label' => 'No invoice',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
        ],
        [
            'name' => 'only_weight_input',
            'label' => 'Weight/User Packaging',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
        ],
        [
            'name' => 'show_label',
            'label' => 'Show Label on scan',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
        ],
        [
            'name' => 'show_invoice',
            'label' => 'Show Invoice on scan',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
        ],
        [
            'name' => 'check_carriers',
            'label' => 'Check in Smart Customs',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
        ],
        [
            'name' => 'draft_label',
            'label' => 'Draft Label in Package Processing',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
        ],
        [
            'name' => 'customs_auto_delcaration',
            'label' => 'Auto Declaration from Customs',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12 clearfix"><h3 class="text-center">Additional prices for packet</h3></div>'
        ],
        [
            'name' => 'use_additional_delivery_price',
            'label' => 'Use Additional Delivery Price (in USA)',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'form-group col-lg-3',
            ],
        ],
        [
            'name' => 'battery_price',
            'label' => 'Additional price for Battery',
            'type' => 'text',
            'validation' => 'nullable|numeric',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12 clearfix"><h3 class="text-center">Limitations for parcel</h3></div>'
        ],
        [
            'name' => 'web_site',
            'label' => 'Default WebSite',
            'type' => 'text',
            'validation' => 'nullable|string',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
        ],
        [
            'name' => 'label',
            'label' => 'Print label',
            'type' => 'select_from_array',
            'validation' => 'nullable|integer',
            'options' => [
                1 => 1, 2 => 2
            ],
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'name' => 'limit_weight',
            'label' => 'Limit weight (kg)',
            'type' => 'text',
            'validation' => 'nullable|numeric',
            'prefix' => '<i class="icon-meter2"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'name' => 'limit_amount',
            'label' => 'Limit amount',
            'type' => 'text',
            'validation' => 'nullable|numeric',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'name' => 'limit_currency',
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.currencies',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12 clearfix"><h3 class="text-center">Tariffs</h3></div>'
        ],
        [
            'name' => 'per_g',
            'label' => 'Gram',
            'hint' => 'Price for a gram',
            'type' => 'text',
            'validation' => 'nullable|numeric',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
        ],
        [
            'name' => 'half_kg',
            'label' => '0.5 kg',
            'hint' => 'Price for 0.5 kg',
            'type' => 'text',
            'validation' => 'nullable|numeric',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
        ],
        [
            'name' => 'per_kg',
            'label' => 'For 1 kg',
            'hint' => 'Price for 1 kg',
            'type' => 'text',
            'validation' => 'required|numeric',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'name' => 'up_10_kg',
            'label' => '> 10 kg',
            'hint' => 'Price for > 10 kg',
            'type' => 'text',
            'validation' => 'required|numeric',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'name' => 'currency',
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.currencies',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'required|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12 clearfix"><h4 class="text-center">Optional</h4></div>'
        ],

        [
            'name' => 'to_100g',
            'label' => '0-100g',
            'type' => 'text',
            'validation' => 'nullable|numeric',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'name' => 'from_100g_to_200g',
            'label' => '100g-200g',
            'type' => 'text',
            'validation' => 'nullable|numeric',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'name' => 'from_200g_to_500g',
            'label' => '200g-500g',
            'type' => 'text',
            'validation' => 'nullable|numeric',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'name' => 'from_500g_to_750g',
            'label' => '500g-750g',
            'type' => 'text',
            'validation' => 'nullable|numeric',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        [
            'name' => 'from_750g_to_1kq',
            'label' => '750g-1kq',
            'type' => 'text',
            'validation' => 'nullable|numeric',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
    ];

    public function indexObject()
    {
        $items = Warehouse::withCount('addresses')->paginate($this->limit);

        return $items;
    }
}
