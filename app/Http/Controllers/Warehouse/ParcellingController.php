<?php

namespace App\Http\Controllers\Warehouse;

use Alert;
use App\Exports\PackagesExport;
use App\Http\Controllers\Admin\Controller;
use App\Models\Bag;
use App\Models\Extra\Notification;
use App\Models\Package;
use App\Models\Parcel;
use App\Models\Warehouse;
use DB;
use Illuminate\Http\Request;
use Validator;
use View;

class ParcellingController extends Controller
{
    protected $modelName = 'Parcel';

    protected $view = [

        'search' => [
            [
                'name' => 'w_status',
                'label' => 'Only In Warehouse & Sent packages',
                'type' => 'checkbox',
                'default' => 1,
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
            ],
            [
                'name' => 'month',
                'label' => 'Last month',
                'type' => 'checkbox',
                'default' => 1,
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
        ],
        /*'search'    => [
            [
                'name'              => 'q',
                'type'              => 'text',
                'attributes'        => ['placeholder' => 'Search...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3 col-lg-offset-1',
                ],
            ],
            [
                'name'       => 'event_date_range',
                'start_name' => 'start_date',
                'end_name'   => 'end_date',
                'type'       => 'date_range',

                'date_range_options' => [
                    'timePicker' => true,
                    'locale'     => ['format' => 'DD/MM/YYYY'],
                ],
                'wrapperAttributes'  => [
                    'class' => 'col-lg-2',
                ],
            ],
        ],*/
    ];

    protected $route = 'w-parcels';

    protected $notificationKey = 'custom_id';

    protected $extraActionsForBag = [
        [
            'key' => 'custom_id',
            'label' => 'Export [PDF]',
            'icon' => 'file-download',
            'route' => 'w-packages.export',
            'query' => [
                'format' => 'Mpdf',
                'bag' => '1',
            ],
            'color' => 'info',
        ],
        [
            'key' => 'custom_id',
            'label' => 'Export [XLS]',
            'icon' => 'file-download',
            'route' => 'w-packages.export',
            'query' => [
                'format' => 'Xlsx',
                'bag' => '1',
            ],
            'color' => 'warning',
        ],
    ];

    protected $extraActions = [
        [
            'key' => 'departed',
            'custom' => true,
            'button' => 'departed_button',
        ],
        [
            'key' => 'sent',
            'custom' => true,
        ],
        [
            'key' => 'not_inserted',
            'custom' => true,
            'button' => 'logic_button',
        ],
        [
            'key' => 'id',
            'label' => 'UNITRADE Export',
            'icon' => 'file-download',
            'route' => 'w-packages.utexport',
            'color' => 'info',
            'button' => 'unitrade_button',
            'country_code' => 'ru',
        ],
        [
            'key' => 'custom_id',
            'label' => 'Export [PDF]',
            'icon' => 'file-download',
            'route' => 'w-packages.export',
            'query' => [
                'format' => 'Mpdf',
            ],
            'color' => 'info',
        ],
        [
            'key' => 'custom_id',
            'label' => 'Export [XLS]',
            'icon' => 'file-download',
            'route' => 'w-packages.export',
            'query' => [
                'format' => 'Xlsx',
            ],
            'color' => 'warning',
        ],
        [
            'key' => 'is_not_logic',
            'label' => 'Manifest [PDF]',
            'icon' => 'download',
            'route' => 'w-packages.manifest',
            'color' => 'success',
            'query' => [
                'format' => 'Mpdf',
            ],
        ],
        [
            'key' => 'is_not_logic',
            'label' => 'Manifest [XLS]',
            'icon' => 'download',
            'route' => 'w-packages.manifest',
            'color' => 'warning',
            'query' => [
                'format' => 'Xlsx',
            ],
        ],
    ];

    protected $fields = [
        [
            'name' => 'pkg_goods',
            'type' => 'hidden',
            'default' => '1',
            'short' => true,
        ],
        [
            'name' => 'show_label',
            'type' => 'hidden',
            'default' => 1,
            'short' => true,
        ],
        [
            'name' => 'length_type',
            'type' => 'hidden',
            'default' => 0,
            'short' => true,
        ],
        [
            'name' => 'shipping_amount_cur',
            'type' => 'hidden',
            'default' => 0,
            'short' => true,
        ],
        [
            'name' => 'tracking_code',
            'label' => 'Tracking Code',
            'type' => 'text',
            'short' => true,
            'hint' => 'Special Tracking number (optional)',
            'prefix' => '<i class="icon-barcode2"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'attributes' => [
                'autofocus' => true,
                'data-validation-optional' => 'true',
                'data-validation' => 'length custom',
                'data-validation-length' => "min9",
                'data-validation-regexp' => "^[A-Za-z0-9-]+$",
            ],
            'validation' => 'nullable|string|min:9|unique:packages,tracking_code',
        ],
        [
            'label' => 'User',
            'type' => 'select_from_array',
            //'type'              => 'select2',
            'name' => 'user_id',
            //'attribute'         => 'full_name,customer_id',
            //'model'             => 'App\Models\User',

            'wrapperAttributes' => [
                'class' => 'col-md-3 hidden_for_user',
                'id' => 'user_id',
            ],
            'validation' => 'nullable|integer',
            'allowNull' => true,
            'short' => true,
            'attributes' => [
                'data-validation' => 'required',
                'class' => 'select2-ajax',
                'data-url' => '/users',
                //'data-url'        => '/search-users',
            ],
        ],
        [
            'name' => 'website_name',
            'label' => 'WebSite name',
            'type' => 'text',
            'hint' => 'Also accept url',
            'wrapperAttributes' => [
                'class' => 'col-md-3 hidden_for_user',
            ],
            'prefix' => '<i class="icon-link"></i>',
            'validation' => 'nullable|string',
            'default' => '',
            'attributes' => [
                'data-validation' => 'required',
            ],
        ],
        [
            'name' => 'seller_name',
            'label' => 'Seller name',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2 hidden_for_user',
            ],
            'prefix' => '<i class="icon-user-tie"></i>',
            'validation' => 'nullable|string',
            'short' => true,
            'default' => '',
        ],
        [
            'name' => 'shipping_amount',
            'label' => 'Invoiced price',
            'type' => 'text',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2 hidden_for_user',
            ],
            'attributes' => [
                'data-validation' => 'required number',
                'data-validation-allowing' => "float",
            ],
            'validation' => 'nullable|numeric',
        ],
        [
            'name' => 'shipping_amount_cur',
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.currencies',
            'default_by_relation' => 'country.currency',
            'wrapperAttributes' => [
                'class' => 'col-md-1 hidden_for_user',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12"><h3 class="text-center">Weight</h3></div>',
            'short' => true,
        ],
        [
            'name' => 'weight',
            'label' => 'Gross Weight',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2 change_volume weight_id active_weight',
            ],
            'validation' => 'nullable|numeric',
            'short' => true,
            'prefix' => '<i class="icon-meter2"></i>',
            'attributes' => [
                'data-validation' => 'required number',
                'data-validation-allowing' => "float",
            ],
        ],
        [
            'name' => 'weight_type',
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.weight',
            'short' => true,
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-2"> </div>',
        ],
        [
            'name' => 'width',
            'label' => 'Width',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-1 change_volume',
            ],
            'validation' => 'nullable|numeric',
            'attributes' => [
                'data-validation-optional' => 'true',
                'data-validation' => 'number',
                'data-validation-allowing' => "float",
            ],

        ],
        [
            'name' => 'height',
            'label' => 'Height',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-1  change_volume',
            ],
            'validation' => 'nullable|numeric',
            'attributes' => [
                'data-validation-optional' => 'true',
                'data-validation' => 'number',
                'data-validation-allowing' => "float",
            ],

        ],
        [
            'name' => 'length',
            'label' => 'Length',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-1 change_volume',
            ],
            'validation' => 'nullable|numeric',
            'attributes' => [
                'data-validation-optional' => 'true',
                'data-validation' => 'number',
                'data-validation-allowing' => "float",
            ],

        ],
        [
            'name' => 'length_type',
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.length',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'volume_weight',
            'label' => 'Volume Weight (kg)',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2 volume_id',
            ],
            'validation' => 'nullable|numeric',
            'prefix' => '<i class="icon-meter2"></i>',
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12 hidden_for_user"><h3 class="text-center">Product description</h3></div>',
        ],
        [
            'type' => 'html',
            'html' => '<div class="row"><div id="type_section" class="col-lg-6 hidden_for_user"><div class="row type_item" id="main_type_item">',
        ],
        [
            'label' => 'Type',
            'type' => 'select3',
            'name_parent' => 'customs_type_parents[]',
            'name_child' => 'customs_types[]',
            'attribute' => 'name_en',
            'model' => 'App\Models\CustomsType',
            'allowNull' => true,
            'validation' => 'nullable|integer',
            'nodb' => true,
            'wrapperAttributes' => [
                'class' => 'col-md-6 hidden_for_user',
            ],
            'allowNull' => true,
            'attributes' => [
                'data-validation' => 'required',
            ],
        ],
        [
            'name' => 'ru_shipping_amounts[]',
            'label' => 'Price',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2 hidden_for_user',
            ],
            'attributes' => [
                //      'data-validation'          => 'required number',
                'data-validation-allowing' => "float",
            ],
            'validation' => 'nullable|numeric',
        ],
        [
            'name' => 'ru_weights[]',
            'label' => 'Weight',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2 hidden_for_user',
            ],
            'validation' => 'nullable|numeric',
            'short' => true,
            'attributes' => [
                'data-validation-allowing' => "float",
            ],
        ],
        [
            'name' => 'ru_items[]',
            'label' => 'Items',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-1 hidden_for_user',
            ],
            'attributes' => [
                'data-validation' => 'required number',
                'data-validation-allowing' => "range[1;10000]",
            ],
            //'validation'        => 'required|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="col-lg-1 hidden_for_user"> <span class="btn btn-danger btn-icon btn_minus" style="margin-top: 20px"><i
                                        class="icon-minus2"></i></span></div>',
        ],
        [
            'type' => 'html',
            'html' => '</div></div><div class="col-lg-1 hidden_for_user"> <span id="add_type" class="btn btn-primary btn-icon" style="margin-top: 20px"><i
                                        class="icon-plus2"></i></span></div><div class="col-lg-5">',
        ],
        [
            'name' => 'warehouse_comment',
            'label' => 'Comment',
            'type' => 'text',
            'hint' => 'Note for yourself',
            'wrapperAttributes' => [
                'class' => 'col-md-12 hidden_for_user',
            ],
            'validation' => 'nullable|string',
            'prefix' => '<i class="icon-clipboard2"></i>',
        ],
        [
            'type' => 'html',
            'html' => '</div></div>',
        ],
    ];

    protected $ru_fields = [
        [
            'name' => 'weight',
            'type' => 'hidden',
            'default' => 1,
            'short' => true,
        ],
        [
            'name' => 'pkg_goods',
            'type' => 'hidden',
            'default' => '0',
            'short' => true,
        ],
        [
            'name' => 'number_items',
            'type' => 'hidden',
            'default' => 1,
            'short' => true,
        ],
        [
            'name' => 'shipping_amount',
            'type' => 'hidden',
            'default' => 1,
            'short' => true,
        ],
        [
            'name' => 'show_label',
            'type' => 'hidden',
            'default' => 1,
            'short' => true,
        ],
        [
            'name' => 'length_type',
            'type' => 'hidden',
            'default' => 0,
            'short' => true,
        ],
        [
            'name' => 'shipping_amount_cur',
            'type' => 'hidden',
            'default' => 0,
            'short' => true,
        ],
        [
            'name' => 'tracking_code',
            'label' => 'Tracking Code',
            'type' => 'text',
            'short' => true,
            'hint' => 'Special Tracking number (optional)',
            'prefix' => '<i class="icon-barcode2"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'attributes' => [
                'autofocus' => true,
                'data-validation-optional' => 'true',
                'data-validation' => 'length custom',
                'data-validation-length' => "min5",
                'data-validation-regexp' => "^[A-Za-z0-9-]+$",
            ],
            'validation' => 'nullable|string|min:5|unique:packages,tracking_code',
        ],
        [
            'label' => 'User',
            'type' => 'select_from_array',
            'name' => 'user_id',
            'wrapperAttributes' => [
                'class' => 'col-md-3 hidden_for_user',
                'id' => 'user_id',
            ],
            'validation' => 'nullable|integer',
            'allowNull' => true,
            'short' => true,
            'attributes' => [
                'data-validation' => 'required',
                'class' => 'select2-ajax',
                'data-url' => '/users',
            ],
        ],
        [
            'name' => 'website_name',
            'label' => 'WebSite name',
            'type' => 'text',
            'hint' => 'Also accept url',
            'wrapperAttributes' => [
                'class' => 'col-md-3 hidden_for_user',
            ],
            'prefix' => '<i class="icon-link"></i>',
            'validation' => 'nullable|string',
            'default' => '',
            'attributes' => [
                'data-validation' => 'required',
            ],
        ],
        [
            'name' => 'seller_name',
            'label' => 'Seller name',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2 hidden_for_user',
            ],
            'prefix' => '<i class="icon-user-tie"></i>',
            'validation' => 'nullable|string',
            'short' => true,
            'default' => '',
        ],
        [
            'name' => 'shipping_amount',
            'label' => 'Invoiced price',
            'type' => 'text',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2 hidden_for_user',
            ],
            'validation' => 'nullable|numeric',
        ],
        [
            'name' => 'shipping_amount_cur',
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.currencies',
            'default_by_relation' => 'country.currency',
            'wrapperAttributes' => [
                'class' => 'col-md-1 hidden_for_user',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12"><h3 class="text-center">Weight</h3></div>',
            'short' => true,
        ],
        [
            'name' => 'weight',
            'label' => 'Gross Weight',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2 change_volume weight_id active_weight',
            ],
            'validation' => 'nullable|numeric',
            'short' => true,
            'prefix' => '<i class="icon-meter2"></i>',
        ],
        [
            'name' => 'weight_type',
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.weight',
            'short' => true,
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-2"> </div>',
        ],
        [
            'name' => 'width',
            'label' => 'Width',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-1 change_volume',
            ],
            'validation' => 'nullable|numeric',
            'attributes' => [
                'data-validation-optional' => 'true',
                'data-validation' => 'number',
                'data-validation-allowing' => "float",
            ],

        ],
        [
            'name' => 'height',
            'label' => 'Height',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-1  change_volume',
            ],
            'validation' => 'nullable|numeric',
            'attributes' => [
                'data-validation-optional' => 'true',
                'data-validation' => 'number',
                'data-validation-allowing' => "float",
            ],

        ],
        [
            'name' => 'length',
            'label' => 'Length',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-1 change_volume',
            ],
            'validation' => 'nullable|numeric',
            'attributes' => [
                'data-validation-optional' => 'true',
                'data-validation' => 'number',
                'data-validation-allowing' => "float",
            ],

        ],
        [
            'name' => 'length_type',
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.length',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'volume_weight',
            'label' => 'Volume Weight (kg)',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2 volume_id',
            ],
            'validation' => 'nullable|numeric',
            'prefix' => '<i class="icon-meter2"></i>',
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12 hidden_for_user"><h3 class="text-center">Package Goods</h3></div>',
        ],
        [
            'type' => 'html',
            'html' => '<div id="type_section" class="col-lg-10 hidden_for_user"><div class="row type_item" id="main_type_item">',
        ],
        [
            'name' => 'ru_types[]',
            'type' => 'hidden',
        ],
        [
            'name' => 'ru_hscodes[]',
            'label' => 'Hs code',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2 hidden_for_user pkg_type_hs_code',
                'id' => 'pkg_type_hs_code-1',
            ],
            'attributes' => [
                'data-validation' => 'required',
            ],
        ],
        [
            'name' => 'ru_names[]',
            'label' => 'name',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-4 hidden_for_user pkg_type_name',
                'id' => 'pkg_type_name-1',
            ],
            'attributes' => [
                'data-validation' => 'required',
            ],
        ],
        [
            'name' => 'ru_shipping_amounts[]',
            'label' => 'Price',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2 hidden_for_user',
            ],
            'attributes' => [
                'data-validation' => 'required number',
                'data-validation-allowing' => "float",
            ],
            'validation' => 'nullable|numeric',
        ],
        [
            'name' => 'ru_weights[]',
            'label' => 'Weight',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2 hidden_for_user',
            ],
            'validation' => 'nullable|numeric',
            'short' => true,
            'attributes' => [
                'data-validation-allowing' => "float",
            ],
        ],
        [
            'name' => 'ru_items[]',
            'label' => 'Items',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-1 hidden_for_user',
            ],
            'attributes' => [
                'data-validation' => 'required number',
                'data-validation-allowing' => "range[1;10000]",
            ],
            //'validation'        => 'required|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="col-lg-1 hidden_for_user"> <span class="btn btn-danger btn-icon btn_minus" style="margin-top: 20px"><i
                                        class="icon-minus2"></i></span></div>',
        ],
        [
            'type' => 'html',
            'html' => '</div></div><div class="col-lg-1 hidden_for_user"> <span id="add_type" class="btn btn-primary btn-icon" style="margin-top: 20px"><i
                                        class="icon-plus2"></i></span></div><div class="col-lg-5">',
        ],
        [
            'type' => 'html',
            'html' => '</div>',
        ],
        [
            'name' => 'warehouse_comment',
            'label' => 'Comment',
            'type' => 'text',
            'hint' => 'Note for yourself',
            'wrapperAttributes' => [
                'class' => 'col-md-12 hidden_for_user',
            ],
            'validation' => 'nullable|string',
            'prefix' => '<i class="icon-clipboard2"></i>',
        ],
    ];

    protected $extraActionsForPackage = [
        [
            'key' => 'fake_invoice',
            'label' => 'Invoice',
            'icon' => 'file-pdf',
            'color' => 'info',
            'target' => '_blank',
        ],
        [
            'route' => 'w-packages.label',
            'key' => 'is_ready',
            'label' => 'Label',
            'icon' => 'windows2',
            'color' => 'success',
            'target' => '_blank',
        ],
    ];

    protected $listProcessed = [
        'parcel' => [
            'type' => 'parcel',
            'label' => 'Parcel',
        ],
        'bag' => [
            'type' => 'bag',
            'label' => 'Bag',
        ],
        'custom_id' => [
            'label' => 'CWB No',
        ],
        "tracking_code" => [
            'label' => 'Tracking #',
        ],
        'user' => [
            'type' => 'custom.user',
            'label' => 'User',
        ],
        'has_battery' => [
            'label' => 'BTR',
            'type' => 'battery',
        ],
        'shipping_org_price' => [
            'label' => 'Invoice',
            'type' => 'text',
        ],

        'weight_with_type' => [
            'label' => 'Weight',
            'type' => 'text',
        ],

        'number_items_goods' => [
            'label' => 'Items',
        ],

        'status_with_label' => [
            'label' => 'Status',
        ],
        //'worker'            => [
        'activityworker.name' => [
            'label' => 'Worker',
        ],
        'created_at' => [
            'label' => 'Date',
            'type' => 'date',
        ],
    ];

    protected $list = [
        //'bag'          => [
        //    'type' => 'bag',
        //    'label' => 'Bag',
        //],
        'custom_id' => [
            'label' => 'CWB No',
        ],
        "tracking_code" => [
            'label' => 'Tracking #',
        ],
        'user' => [
            'type' => 'custom.user',
            'label' => 'User',
        ],
        'has_battery' => [
            'label' => 'BTR',
            'type' => 'battery',
        ],
        'shipping_org_price' => [
            'label' => 'Invoice',
            'type' => 'text',
        ],

        'weight_with_type' => [
            'label' => 'Weight',
            'type' => 'text',
        ],

        'number_items_goods' => [
            'label' => 'Items',
        ],
        'carrier' => [
            'type' => 'carrier',
            'label' => 'Customs',
        ],
        'status_with_label' => [
            'label' => 'Status',
        ],
        //'worker'            => [
        'activityworker.name' => [
            'label' => 'Worker',
        ],
        'created_at' => [
            'label' => 'Date',
            'type' => 'date',
        ],
    ];

    public function __construct()
    {
        $this->limit = 10;
        parent::__construct();

        View::share('bodyClass', 'sidebar-xs');
        View::share('extraActionsForPackage', $this->extraActionsForPackage);
        View::share('extraActionsForBag', $this->extraActionsForBag);
        View::share('listProcessed', $this->listProcessed);

    }

    /**
     * Get id
     *
     * @return mixed
     */
    public function id()
    {
        return $this->me()->getAuthIdentifier();
    }

    public function me()
    {
        return auth()->guard('worker')->user()->warehouse;
    }

    public function indexObject()
    {

        //$items = Parcel::with(['packages'])->withCount(['packages'])->whereHas('packages')->where('warehouse_id', $this->id())->orderBy('sent', 'asc')->latest()->paginate();
        $status = 1;
        $month = 1;
        if (\Request::has('w_status'))
            $status = \Request::get('w_status');
        if (\Request::has('month'))
            $month = \Request::get('month');
        if (!$status) {
            if ($month)
                $items = Parcel::with(['bags', 'packages'])->withCount(['packages', 'waiting', 'packagecarriers', 'packagecarriersreg', 'packagecarriersdepesh'])->whereRaw('created_at > (NOW() - INTERVAL ' . $month . ' MONTH)')->whereHas('packages')->where('warehouse_id', $this->id())->orderBy('sent', 'asc')->latest()->paginate($this->limit);
            else
                $items = Parcel::with(['bags', 'packages'])->withCount(['packages', 'waiting', 'packagecarriers', 'packagecarriersreg', 'packagecarriersdepesh'])->whereHas('packages')->where('warehouse_id', $this->id())->orderBy('sent', 'asc')->latest()->paginate($this->limit);
        } else {
            if ($month)
                $items = Parcel::with(['bags', 'packages'])->withCount(['packages', 'waiting', 'packagecarriers', 'packagecarriersreg', 'packagecarriersdepesh'])->whereRaw('created_at > (NOW() - INTERVAL ' . $month . ' MONTH)')->whereHas('packages', function ($query) {
                    $query->whereIn('status', [0, 1,7]);
                })->where('warehouse_id', $this->id())->orderBy('sent', 'asc')->latest()->paginate($this->limit);
            else
                $items = Parcel::with(['bags', 'packages'])->withCount(['packages', 'waiting', 'packagecarriers', 'packagecarriersreg', 'packagecarriersdepesh'])->whereHas('packages', function ($query) {
                    $query->whereIn('status', [0, 1, 7]);
                })->where('warehouse_id', $this->id())->orderBy('sent', 'asc')->latest()->paginate($this->limit);
        }

        $packages = [];
        if (auth()->guard('worker')->user()->warehouse->check_carriers) {
            $packages = Package::where('warehouse_id', $this->id());
            $packages = $packages->leftJoin('package_carriers', 'packages.id', 'package_carriers.package_id')->select('packages.*', 'package_carriers.inserT_DATE');
            $packages = $packages->incustoms()->ready()->where('packages.status', 0)->count();
        } else {
            $packages = Package::where('warehouse_id', $this->id())->ready()->where('packages.status', 0)->count();
        }
        View::share(['ready_packages' => $packages]);

        return $items;
    }

    public function panelView($blade)
    {
        return 'warehouse.parcel.index';
    }

    public function store(Request $request)
    {
        $defaultValue = Parcel::generateCustomId();

        return view('warehouse.parcel.create', compact('defaultValue'));
    }

    public function edit($id)
    {
        $bag = Bag::where('id', $id)->first();
        if (!$bag) {
            Alert::error('Bag not exists');
            return redirect()->route('w-parcels.index');
        }

        $parcel = 0;
        if ($bag->parcel_id) {
            $parcel = Parcel::where('warehouse_id', $this->id())->where('sent', 0)->where('id', $bag->parcel_id)->first();
            if (!$parcel) {
                Alert::error("The bags's parcel was sent! You cannot add new package. Please create new one.");

                return redirect()->route('w-parcels.index');
            }
        } else {
            Alert::error('Parcel not exists');
            return redirect()->route('w-parcels.index');
        }

        /*$bagName= $bag->custom_id;

        if(!$bagName)
        {
             $warehouse = Warehouse::find($parcel->warehouse_id);
             $countryCode='__';
             if (isset($warehouse->country->code)) $countryCode=strtoupper($warehouse->country->code);
             $str="select custom_id from bags";
             $str.=" where id in (";
             $str.="select b.id from bags b";
             $str.=" left outer join bag_package bp on bp.bag_id=b.id";
             $str.=" where b.parcel_id=".$parcel->id." and b.custom_id like '".'ASE'.$countryCode."%'";
             $str.=" group by b.id having count(bp.package_id)>0";
             $str.=") order by custom_id desc limit 1";
             $items = DB::select($str);
             $bagsCount=0;
             if(count($items) > 0)
             {
                 $custom_id=$items[0]->custom_id;
                 if(strlen($custom_id)==9 && substr($custom_id,0,5)=='ASE'.$countryCode)
                 {
                     $lastNum=substr($custom_id,-4);
                   $bagsCount=(int)$lastNum;
                 }
             }
             $bagsCount++;
             $bagName='ASE'.$countryCode.sprintf('%04d', $bagsCount);
             $bag->custom_id=$bagName;
             $bag->save();
        }*/

        //$packages = Package::where('warehouse_id', $this->id())->ready()->where('status', 0)->count();
        $packages = [];
        if (auth()->guard('worker')->user()->warehouse->check_carriers) {
            $packages = Package::where('warehouse_id', $this->id());
            $packages = $packages->leftJoin('package_carriers', 'packages.id', 'package_carriers.package_id')->select('packages.*', 'package_carriers.inserT_DATE');
            $packages = $packages->incustoms()->ready()->where('packages.status', 0)->count();
        } else {
            $packages = Package::where('warehouse_id', $this->id())->ready()->where('packages.status', 0)->count();
        }

        if ($this->me()->country->code == 'ru') {
            $this->fields = $this->ru_fields;
            if ($this->me()->web_site)
                $this->fields[9]['default'] = $this->me()->web_site;
            if (!auth()->guard('worker')->user()->warehouse->allow_make_fake_invoice) {
                $this->fields[7]['hint'] = 'Special Tracking number (required)';
                unset($this->fields[7]['attributes']['data-validation-optional']);
            }
        } else {
            if ($this->me()->web_site)
                $this->fields[6]['default'] = $this->me()->web_site;
            if ($this->me()->country->code == 'tr')
                $this->fields[0]['default'] = '0';
            if (!auth()->guard('worker')->user()->warehouse->allow_make_fake_invoice) {
                $this->fields[4]['hint'] = 'Special Tracking number (required)';
                unset($this->fields[4]['attributes']['data-validation-optional']);
            }
        }
        if ($this->me()->country->code == 'uk') {
            $this->fields[4]['validation'] = 'required|string|min:9|unique:packages,tracking_code';
            $this->fields[4]['hint'] = 'Special Tracking number (required)';
            unset($this->fields[4]['attributes']['data-validation-optional']);
            $this->fields[11]['attributes']['data-validation-allowing'] = "float,range[0.001;10000]";
        }
        View::share('fields', $this->fields);

        return view('warehouse.parcel.edit', compact('parcel', 'bag', 'packages'));
    }

    public function package_process()
    {
        //$packages = Package::where('warehouse_id', $this->id())->ready()->where('status', 0)->paginate(5);
        $ldate = date('Y-m-d H:i:s', time() - 3600);
        $packages = Package::whereWarehouseId($this->id())->whereNotNull('processed_at')->where('processed_at', '>=', $ldate)->orderBy('processed_at', 'desc')->limit(10)->paginate(10);
        //$packages=[];
        //return response()->json([
        //    'error' => 'packages count:'.count($packages->count()),
        //]);
        if ($this->me()->country->code == 'ru') {
            $this->fields = $this->ru_fields;
            if ($this->me()->web_site)
                $this->fields[9]['default'] = $this->me()->web_site;
            if (!auth()->guard('worker')->user()->warehouse->allow_make_fake_invoice) {
                $this->fields[7]['hint'] = 'Special Tracking number (required)';
                unset($this->fields[7]['attributes']['data-validation-optional']);
            }
        } else {
            if ($this->me()->web_site)
                $this->fields[6]['default'] = $this->me()->web_site;
            if ($this->me()->country->code == 'tr') {
                $this->fields[0]['default'] = '0';
                $this->fields[6]['short'] = true;
                $this->fields[6]['wrapperAttributes']['class'] = str_replace(' hidden_for_user', '', $this->fields[6]['wrapperAttributes']['class']);
            }
            if (!auth()->guard('worker')->user()->warehouse->allow_make_fake_invoice) {
                $this->fields[4]['hint'] = 'Special Tracking number (required)';
                unset($this->fields[4]['attributes']['data-validation-optional']);
            }
        }
        if ($this->me()->country->code == 'uk') {
            $this->fields[4]['validation'] = 'required|string|min:9|unique:packages,tracking_code';
            $this->fields[4]['hint'] = 'Special Tracking number (required)';
            unset($this->fields[4]['attributes']['data-validation-optional']);
            $this->fields[11]['attributes']['data-validation-allowing'] = "float,range[0.001;10000]";
        }
        View::share('fields', $this->fields);
        //    $ready_packages = Package::where('warehouse_id', $this->id())->ready()->where('status', 0)->count();
        $ready_packages = [];
        if (auth()->guard('worker')->user()->warehouse->check_carriers) {
            $ready_packages = Package::where('warehouse_id', $this->id());
            $ready_packages = $ready_packages->leftJoin('package_carriers', 'packages.id', 'package_carriers.package_id')->select('packages.*', 'package_carriers.inserT_DATE');
            $ready_packages = $ready_packages->incustoms()->ready()->where('packages.status', 0)->count();
        } else {
            $ready_packages = Package::where('warehouse_id', $this->id())->ready()->where('packages.status', 0)->count();
        }
        return view('warehouse.process', compact('packages', 'ready_packages'));
    }

    public function create()
    {
        $parcelName = request()->get('name');
        $parcel = 0;

        /*if($parcelName)
        {
                $parcel = Parcel::where('custom_id', $parcelName)->where('warehouse_id', $this->id())->first();
        }*/

        if (!$parcel) {
            $str = "select id from parcels";
            $str .= " where id in (";
            $str .= "select pl.id from parcels pl";
            $str .= " left outer join parcel_package pp on pp.parcel_id=pl.id";
            $str .= " where pl.sent=0 and pl.warehouse_id=" . $this->id();
            $str .= " group by pl.id having count(pp.package_id)>0";
            $str .= ")  order by created_at desc limit 1";
            $items = DB::select($str);
            if (count($items) >= 1) {
                //$parcelName=$items[0]->custom_id;
                $parcel = Parcel::where('id', $items[0]->id)->first();
            }
        }

        if (!$parcel) {
            if (!$parcelName)
                $parcelName = Parcel::generateCustomId();

            $parcel = Parcel::create([
                'custom_id' => $parcelName,
                'warehouse_id' => $this->id(),
            ]);
        }

        $bagName = request()->get('bname');

        if (!$bagName) {
            $warehouse = Warehouse::find($parcel->warehouse_id);
            $countryCode = '__';
            if (isset($warehouse->country->code)) $countryCode = strtoupper($warehouse->country->code);
            $str = "select custom_id from bags";
            $str .= " where id in (";
            $str .= "select b.id from bags b";
            $str .= " left outer join bag_package bp on bp.bag_id=b.id";
            $str .= " where b.parcel_id=" . $parcel->id . " and b.custom_id like '" . 'ASE' . $countryCode . "%'";
            $str .= " group by b.id having count(bp.package_id)>0";
            $str .= ") order by custom_id desc limit 1";
            $items = DB::select($str);
            $bagsCount = 0;
            if (count($items) > 0) {
                $custom_id = $items[0]->custom_id;
                if (strlen($custom_id) == 9 && substr($custom_id, 0, 5) == 'ASE' . $countryCode) {
                    $lastNum = substr($custom_id, -4);
                    $bagsCount = (int)$lastNum;
                }
            }
            $bagsCount++;
            $bagName = 'ASE' . $countryCode . sprintf('%04d', $bagsCount);
        }

        $bag = Bag::create([
            'parcel_id' => $parcel->id,
            'custom_id' => $bagName,
        ]);

        if (request()->has('items') && request()->get('items')) {
            $items = request()->get('items');

            if ($items) {
                $parcel->packages()->attach($items);
                $bag->packages()->attach($items);
                /*$cm=new CustomsModel();
                $cmIndex=0;
                foreach($items as $item) {
                    $packageCarrier = PackageCarrier::where('package_id',$item->id)->first();
                    if($packageCarrier && $packageCarrier->is_commercial)
                    {
                        $cm->trackingNumber=$packageCarrier->trackingNumber;
                        //$cm->voen=$item->user->voen;
                        $cm->airwaybill=$parcel->custom_id;
                        $cm->depesH_NUMBER=$bag->custom_id;
                        $cm->isCommercial=$packageCarrier->is_commercial;
                        $cmIndex++;
                        if($cmIndex>1) sleep(1);
                                    $res=$cm->update_carriers();
                    }
                }*/
            }

            return redirect()->route($this->route . ".index");
        }

        return redirect()->route($this->route . '.edit', $bag->id);
    }

    public function deletePackage($id, $packageId)
    {
        /* Attach new package to the parcel */
        $bag = Bag::where("id", $id)->first();
        if (!$bag) {
            return response()->json([
                'error' => 'Bag not exists!',
            ]);
        }
        if ($bag->parcel_id) {
            $parcel = Parcel::where("id", $bag->parcel_id)->where('sent', 0)->first();
            if (!$parcel) {
                return response()->json([
                    'error' => 'Parcel already was sent, You cannot delete the package. Sorry!',
                ]);
            }
            $parcel->packages()->detach($packageId);
        }

        return $bag->packages()->detach($packageId);
    }

    public function departed($id)
    {
        $parcel = Parcel::with('packages')->find($id);
        if ($parcel and $parcel->packages) {

            foreach ($parcel->packages as $package) {
                if ($package->status < 1 && $package->status != 7) {
                    $package->status = 7;
                    $package->save();

                    /* Send Notification */
                    Notification::sendPackage($package->id, '7');
                }
            }

            $parcel->departed = true;
            $parcel->save();
        }
        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Parcel',
            'key' => 'customId',
            'value' => $parcel->custom_id,
            'action' => 'departed',
        ]));

        return redirect()->back();
    }

    public function sent($id)
    {
        $parcel = Parcel::with('packages')->find($id);
        if ($parcel and $parcel->packages) {

            foreach ($parcel->packages as $package) {
                if ($package->status < 1 || $package->status==7) {
                    $package->status = 1;
                    $package->save();

                    /* Send Notification */
                    Notification::sendPackage($package->id, '1');
                }
            }

            $parcel->sent = true;
            $parcel->save();
        }
        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Parcel',
            'key' => 'customId',
            'value' => $parcel->custom_id,
            'action' => 'sent',
        ]));

        return redirect()->back();
    }


    public function insert($id)
    {
        $parcel = Parcel::with('packages')->find($id);
        //$bag = Bag::where('parcel_id', $parcel->id)->first();
        if ($parcel and $parcel->packages) {
            $parcel->inserted = 1;
            $parcel->save();


            /*foreach ($parcel->packages as $package) {
                if ($package->status < 1) {
                    $package->status = 1;
		    $package->save();*/

            /* Send Notification */
            /*        Notification::sendPackage($package->id, '1');
                }
            }

            $parcel->sent = true;
	    $parcel->save();*/
        }


        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Parcel',
            'key' => 'customId',
            'value' => $parcel->custom_id,
            'action' => 'updated',
        ]));

        return redirect()->back();
    }

    public function destroy($id)
    {
        $bag = Bag::where('id', $id)->first();
        if (!$bag) {
            Alert::error('Bag not exists');
            return redirect()->back();
        }
        $parcel = Parcel::where('warehouse_id', $this->id())->where('id', $bag->parcel_id)->first();
        if ($parcel) {
            $parcelCheck = Parcel::where('warehouse_id', $this->id())->where('sent', 0)->where('id', $parcel->id)->first();
            if (!$parcelCheck) {
                Alert::error("Bag's parcel already was sent, You cannot delete this bag!");

                return redirect()->back();
            }
        }
        foreach ($bag->packages as $package) {
            $parcel->packages()->detach($package->id);
        }
        $parcel->save();
        foreach ($bag->packages as $package) {
            $bag->packages()->detach($package->id);
        }
        $bag->save();
        //if($bag->packages_count<=0)
        //{
        $bag->delete();
        //}
        if (count($parcel->packages) <= 0) {
            $parcel->delete();
            //Parcel::destroy($parcel->id);
        }
        Alert::success('Bag deleted');
        return redirect()->route($this->route . ".index");
    }

    public function update(Request $request, $id)
    {
        if ($id == 0) {
            return redirect()->back();
        }
        if (request()->has('items') && request()->get('items')) {
            $items = request()->get('items');

            $bag = Bag::where('id', $id)->first();
            if (!$bag) {
                Alert::error('Bag not exists');
                return redirect()->back();
            }

            $parcelName = request()->get('name');
            $bagName = request()->get('bname');
            if (!$parcelName) {
                Alert::error('Parcel name is empty! ');
                return redirect()->back();
            }

            $parcel = Parcel::where('warehouse_id', $this->id())->where('id', $bag->parcel_id)->first();
            $str = "select id from parcels";
            $str .= " where id in (";
            $str .= "select pl.id from parcels pl";
            $str .= " left outer join parcel_package pp on pp.parcel_id=pl.id";
            $str .= " where pl.sent=0 and pl.warehouse_id=" . $this->id();
            if ($parcel) {
                $str .= " and pl.id !=" . $parcel->id;
                if ($parcel->custom_id == $parcelName)
                    $str .= " and created_at <='" . $parcel->created_at . "'";
            }
            $str .= " and pl.custom_id='" . $parcelName . "'";
            $str .= " group by pl.id having count(pp.package_id)>0";
            $str .= ")  order by created_at asc limit 1";
            $items = DB::select($str);
            if (count($items) >= 1) {
                $parcel = Parcel::where('id', $items[0]->id)->first();
            }

            if ($parcel) {
                $parcelCheck = Parcel::where('warehouse_id', $this->id())->where('sent', 0)->where('id', $parcel->id)->first();
                if (!$parcelCheck) {
                    Alert::error('Parcel ' . $parcelName . ' already was sent, You cannot use this parcel!');

                    return redirect()->back();
                }
            }
            $mustUpdateCustoms = false;

            if (!$parcel || ($parcel->id != $bag->parcel_id) || ($parcel->custom_id != $parcelName)) {
                if (!$parcel || (($parcel->custom_id != $parcelName) && ($parcel->id == $bag->parcel_id))) {
                    $parcel = Parcel::create([
                        'custom_id' => $parcelName,
                        'warehouse_id' => $this->id(),
                    ]);
                }
                $parcelBag = Parcel::where('warehouse_id', $this->id())->where('id', $bag->parcel_id)->first();
                foreach ($bag->packages as $package) {
                    $parcelBag->packages()->detach($package->id);
                }
                $parcelBag->save();
                //if($parcelBag->packages_count<=0)
                //	$parcelBag->delete();
                $str = "delete from parcels where id=" . $parcelBag->id;
                $str .= " and id not in (select parcel_id from parcel_package where parcel_id=" . $parcelBag->id . ")";
                $str .= " and id not in (select parcel_id from bags where parcel_id=" . $parcelBag->id . ")";
                DB::delete($str);

                $mustUpdateCustoms = true;
                foreach ($bag->packages as $package) {
                    $parcel->packages()->attach($package->id);
                }
                $parcel->save();
            }

            if (!$parcel) {
                return redirect()->back();
            }


            //$parcel->custom_id = (request()->get('name') ?: Parcel::generateCustomId());
            //$bag->custom_id = (request()->get('bname') ?: $parcel->custom_id);

            //$parcel->custom_id = $parcelName;
            //$parcel->save();
            // Alert::error('Parcel name: '.$parcel->custom_id);
            //    return redirect()->back();

            if (!$bagName) {
                $warehouse = Warehouse::find($parcel->warehouse_id);
                $countryCode = '__';
                if (isset($warehouse->country->code)) $countryCode = strtoupper($warehouse->country->code);
                $str = "select custom_id from bags";
                $str .= " where id in (";
                $str .= "select b.id from bags b";
                $str .= " left outer join bag_package bp on bp.bag_id=b.id";
                $str .= " where b.parcel_id=" . $parcel->id . " and b.custom_id like '" . 'ASE' . $countryCode . "%'";
                $str .= " group by b.id having count(bp.package_id)>0";
                $str .= ") order by custom_id desc limit 1";
                $items = DB::select($str);
                $bagsCount = 0;
                if (count($items) > 0) {
                    $custom_id = $items[0]->custom_id;
                    if (strlen($custom_id) == 9 && substr($custom_id, 0, 5) == 'ASE' . $countryCode) {
                        $lastNum = substr($custom_id, -4);
                        $bagsCount = (int)$lastNum;
                    }
                }
                $bagsCount++;
                $bagName = 'ASE' . $countryCode . sprintf('%04d', $bagsCount);
            }
            $bag->parcel_id = $parcel->id;
            $oldBagName = $bag->custom_id;
            $bag->custom_id = $bagName;
            $bag->save();
            /*if(($oldBagName != $bagName) || $mustUpdateCustoms)
            {
            $cm=new CustomsModel();
            $cmIndex=0;
            foreach($bag->packages as $package)
            {
               $packageCarrier = PackageCarrier::where('package_id',$package->id)->first();
               if($packageCarrier && $packageCarrier->is_commercial)
               {
                 $cm->trackingNumber=$packageCarrier->trackingNumber;
                 //$cm->voen=$package->user->voen;
                 $cm->airwaybill=$parcel->custom_id;
                 $cm->depesH_NUMBER=$bag->custom_id;
                 $cm->isCommercial=$packageCarrier->is_commercial;
                 $cmIndex++;
                 if($cmIndex>1) sleep(1);
                             $res=$cm->update_carriers();
                             //if(!isset($res->code))
                             //  {
                             //          echo 'Cannot update package in customs system (Empty response)';
                             //  }
                 //else if($res->code!=200)
                             //  {
                             //          echo 'Cannot update package in customs system ('.$cm->errorStr.')';
                 //}

               }
            }
            }*/

            //$parcel->packages()->sync($items);

            Alert::success(trans('saysay::crud.action_alert', [
                'name' => 'Parcel',
                'key' => 'customId',
                'value' => $parcel->custom_id,
                'action' => 'updated',
            ]));

            return redirect()->route($this->route . ".index");
        } else {
            Alert::error('Add at least one package! ');

            return redirect()->back();
        }
    }

    public function index()
    {
        $warehouse = auth()->guard('worker')->user()->warehouse;
        if (($warehouse->auto_print || $warehouse->auto_print_pp || $warehouse->auto_print_invoice || $warehouse->auto_print_pp_invoice) && (!isset($_COOKIE['label_printer']) || !isset($_COOKIE['invoice_printer']))) {

            return redirect()->route('my.edit', auth()->guard('worker')->user()->warehouse_id);
        }

        return parent::index(); // TODO: Change the autogenerated stub
    }
}
