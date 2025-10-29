<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Events\PackageCell;
use App\Exports\Admin\PackagesExport;
use App\Exports\Warehouse\ManifestExport;
use App\Jobs\UpdateCarrierPackagePaymentStatusJob;
use App\Models\Activity;
use App\Models\AzeriExpress\AzeriExpressOffice;
use App\Models\Azerpost\AzerpostOffice;
use App\Models\Container;
use App\Models\CourierShelfLog;
use App\Models\DeliveryPoint;
use App\Models\DepeshModel;
use App\Models\Extra\Notification;
use App\Models\Kargomat\KargomatOffice;
use App\Models\Package;
use App\Models\PackageLog;
use App\Models\Parcel;
use App\Models\Precinct\PrecinctPackage;
use App\Models\Surat\SuratOffice;
use App\Models\Track;
use App\Models\Transaction;
use App\Models\UkrExpressModel;
use App\Models\User;
use App\Exports\Admin\DebtPackage;
use App\Models\YeniPoct\YenipoctOffice;
use App\Services\Package\PackageService;
use App\Services\Precinct\PrecinctService;
use Auth;
use Carbon\Carbon;
use Excel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PDF;
use Response;
use Validator;
use View;

class PackageController extends Controller
{
    public $itemId;
    protected $can = [
        'export' => true,
    ];

    protected $view = [
        'checklist' => [['route' => 'packages.multiple', 'key' => 'status', 'value' => 3, 'label' => 'Done', 'icon' => 'check',],], 'total_sum' => [
            [
                'key' => 'weight_goods',
                'skip' => 15,
                'add' => "kg",
            ],
            [
                'key' => 'number_items_goods',
                'skip' => 1,
            ],
            [
                'key' => 'shipping_amount_usd1',
                'skip' => 0,
                'add' => "$",
            ],
            [
                'key' => 'delivery_manat_price',
                'skip' => 1,
                'add' => "₼",
            ],
            [
                'key' => 'delivery_manat_price_discount',
                'skip' => 3,
                'add' => "₼",
            ],
            [
                'key' => 'total_price',
                'skip' => 0,
                'add' => "$",
            ],
        ],
        'sum' => [
            [
                'key' => 'weight_goods',
                'skip' => 17,
                'add' => "kg",
            ],
            [
                'key' => 'number_items_goods',
                'skip' => 1,
            ],
            [
                'key' => 'shipping_amount_usd1',
                'skip' => 0,
                'add' => "$",
            ],
            [
                'key' => 'delivery_manat_price',
                'skip' => 1,
                'add' => "₼",
            ],
            [
                'key' => 'delivery_manat_price_discount',
                'skip' => 3,
                'add' => "₼",
            ],
            [
                'key' => 'total_price',
                'skip' => 0,
                'add' => "$",
            ],
        ],
        'colorCondition' => [
            'key' => 'alert',
            'value' => 1,
        ],
        'bodyClass' => 'sidebar-xs',
        'formColumns' => 10,
        'search' => [
            [
                'name' => 'parcel',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Parcel id'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
            [
                'name' => 'q',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Search...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
            ],
            [
                'name' => 'tl',
                'type' => 'textarea',
                'attributes' => ['placeholder' => 'Package # List...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
            [
                'type' => 'select2',
                'name' => 'warehouse_id',
                'attribute' => 'country.name',
                'model' => 'App\Models\Warehouse',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All warehouses',
            ],
            [
                'name' => 'status',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.package.status',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All Status',
            ],
            [
                'name' => 'incustoms',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.package.incustoms',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Customs status',
            ],
            [
                'name' => 'paid',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.package.paid2',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Paid Status',
            ],
            [
                'name' => 'discount',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.package.discount',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Discount Status',
            ],
            [
                'name' => 'dec',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.package.dec',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Filter',
            ],
            [
                'name' => 'date_by',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.package.date_by',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
            [
                'name' => 'event_date_range',
                'start_name' => 'start_date',
                'end_name' => 'end_date',
                'type' => 'date_range',

                'date_range_options' => [
                    'timePicker' => true,
                    'locale' => ['format' => 'DD/MM/YYYY'],
                ],
                'wrapperAttributes' => [
                    'class' => 'col-lg-4',
                ],
            ],
            [
                'type' => 'select2',
                'name' => 'city_id',
                'attribute' => 'name',
                'model' => 'App\Models\City',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All Cities',
            ],
            /*            [
                            'name' => 'azerpoct',
                            'type' => 'select_from_array',
                            'optionsFromConfig' => 'ase.attributes.package.inazerpoct',
                            'wrapperAttributes' => [
                                'class' => 'col-lg-2',
                            ],
                            'allowNull' => 'Azerpoct status',
                ],*/
            [
                'name' => 'sta',
                'label' => 'Show total all',
                'type' => 'checkbox',
                'default' => 0,
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
            [
                'name' => 'bp_status',
                'type' => 'select_from_array',
                'options' => [1 => "In Bag & Parcel", 2 => "Not In Bag & Parcel to Pack"],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Bag & Parcel Status',
            ],
            [
                'name' => 'ue_status',
                'type' => 'select_from_array',
                'options' => [1 => "In Ukr Express", 2 => "Ready to Pack", 7 => "Not Ready to Pack", 3 => "Packed", 4 => "Not Packed", 5 => "With Errors", 8 => "With Attention", 6 => "Not In Ukr Express"],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'UE Status',
            ],
            [
                'name' => 'paid_debt',
                'type' => 'select_from_array',
                'options' => [1 => "Borcu ödənmişlər", 2 => "Borcu ödənməmişlər"],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Paid Debt',
            ],
            [
                'name' => 'paid_broker',
                'type' => 'select_from_array',
                'options' => [1 => "Borcu ödənmişlər", 0 => "Borcu ödənməmişlər"],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Paid Broker',
            ],
            /*[
                'name'              => 'ukr_express_status',
                'type'              => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.package.ukr_express_status',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull'         => 'UE Status',
	],*/

        ],
    ];

    protected $notificationKey = 'custom_id';

    protected $extraActions = [
        [
            'key' => 'id',
            'role' => 'customs-check',
            'label' => 'Customs check',
            'icon' => 'checkmark',
            'route' => 'packages.packagedepeshcheck',
            'color' => 'info',
            'target' => '_blank',
        ],
        [
            'route' => 'packages.carrier_update',
            'key' => 'id',
            'role' => 'customs-reset',
            'label' => 'Customs reset',
            'icon' => 'spinner11',
            'color' => 'success',
            'target' => '_blank',
        ],
        [
            'route' => 'packages.packagecanceldepesh',
            'key' => 'id',
            'role' => 'customs-depesh',
            'label' => 'Cancel depesh',
            'icon' => 'undo',
            'color' => 'success',
            'target' => '_blank',
        ],
        [
            'route' => 'packages.carrier_delete',
            'key' => 'id',
            'role' => 'customs-reset',
            'label' => 'Customs delete',
            'icon' => 'bin',
            'color' => 'success',
            'target' => '_blank',
        ],
        [
            'key' => 'invoice',
            'label' => 'Invoice',
            'icon' => 'file-pdf',
            'color' => 'info',
            'target' => '_blank',
        ],
        [
            'route' => 'packages.label',
            'key' => 'id',
            'label' => 'Label',
            'icon' => 'windows2',
            'color' => 'success',
            'target' => '_blank',
        ],
        [
            'route' => 'packages.ue_info',
            'key' => 'id',
            'label' => 'UE Info',
            'icon' => 'camera',
            'color' => 'success',
            'target' => '_blank',
        ],
        [
            'route' => 'packages.logs',
            'key' => 'logs',
            'label' => 'Logs',
            'icon' => 'list',
            'color' => 'default',
            'target' => '_blank',
        ],
    ];

    protected $list = [

        'scanned_at' => [
            'label' => 'DeliveredAt',
            'type' => 'date',
            'order' => 'scanned_at',
        ],
        'parcel_name' => [
            'label' => 'Parcel',
            'type' => 'editable_parcel_link',
            'editable' => [
                'key' => 'parcel_name',
                'route' => 'packages.ajax',
                'type' => 'text',
            ],
        ],
        'bag_name' => [
            'label' => 'Bag',
            'type' => 'editable',
            'editable' => [
                'key' => 'bag_name',
                'route' => 'packages.ajax',
                'type' => 'text',
            ],
        ],
        'cell' => [
            'order' => 'cell',
        ],
        'shelf_name' => [
            'label' => 'HDC',
        ],
        'custom_id' => [
            'label' => 'CWB #',
            'order' => 'custom_id',
        ],
//        'photos' => [
//            'label' => 'Photos',
//            'type' => 'custom.ue_photos', // Xüsusi tip
//            'orderable' => false,
//        ],
        'tracking_code' => [
            'type' => 'custom.tracking_code',
            'label' => 'Track #',
            'order' => 'tracking_code',
        ],
        'ukr_express_status' => [
            'title' => 'bot_comment',
            'type' => 'ukr_express_status',
            'label' => 'UE',
        ],
        'has_battery' => [
            'type' => 'battery',
            'label' => 'BTR',
        ],
        'user' => [
            'type' => 'custom.user_link',
            'label' => 'User',
        ],

//        'id' => [
//            'type' => 'custom.ue_photos',
//            'label' => 'Photos',
//        ],

        'short_type' => [
            'title' => 'detailed_type',
            'label' => 'Type',
        ],
        'carrier' => [
            'type' => 'carrier',
            'label' => 'Customs',
            'order' => 'carrier',
        ],
        'worker_comments' => [
            'label' => 'Comments',
            'type' => 'editable',
            'editable' => [
                'route' => 'packages.ajax',
                'type' => 'text',
            ],
            'order' => 'worker_comments',
        ],
        'user.city_name' => [
            'label' => 'City',
        ],
        'warehouse.country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
        'weight_with_type' => [
            'order' => 'weight_goods',
            'label' => 'Weight',
            'type' => 'editable',
            'editable' => [
                'key' => 'weight_goods',
                'route' => 'packages.ajax',
                'type' => 'number',
            ],
        ],
        'full_size' => [
            'type' => 'size',
            'label' => 'Size(L/W/H)',
        ],
        'number_items_goods' => [
            'order' => 'number_items_goods',
            'label' => 'Items',
            'type' => 'editable',
            'editable' => [
                'route' => 'packages.ajax',
                'type' => 'number',
            ],
        ],
        'shipping_org_price' => [
            'order' => 'shipping_amount_goods',
            'label' => 'Invoice Price',
        ],
        'additional_delivery_prices' => [
            'label' => '+Delivery Price',
        ],
        'merged_delivery_price' => [
            'order' => 'delivery_price',
            'label' => 'Delivery Price',
        ],
        'discount_percent_with_label' => [
            'label' => 'Discount',
        ],
        'promo_discount_with_label' => [
            'label' => 'Promo',
        ],
        'merged_delivery_price_discount' => [
            'label' => 'Delivery Price (with discount)',
        ],
        'total_price_with_label' => [
            'label' => 'Declared Value',
        ],
        /* 'show_label_with_label'  => [
             'label'    => 'Show Label',
             'type'     => 'editable',
             'editable' => [
                 'title'            => 'Label for warehouse',
                 'key'              => 'show_label',
                 'route'            => 'packages.ajax',
                 'type'             => 'checklist',
                 'sourceFromConfig' => 'ase.attributes.package.labelWithLabel',
                 'data'             => [
                     'emptytext'   => 'Hide',
                     'showbuttons' => 'bottom',
                     'tpl'         => '<div class="checkbox"></div>',
                 ],
             ],
         ],*/
        'status' => [
            'label' => 'Status',
            //'type' => 'select-editable',
            'type' => 'package_status',
            'editable' => [
                'route' => 'packages.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.package.statusWithLabel',
            ],
        ],
        'paid' => [
            'label' => 'Paid',
            'type' => 'paid',
            'editable' => [
                'route' => 'packages.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.package.paidWithLabel',
            ],
        ],
        'paid_by' => [
            'label' => 'Paid By',
        ],
        //Debt
        'debt_price' => [
            'order' => 'debt_price',
            'label' => 'Debt Price',
        ],
        //Debt
        'paid_debt' => [
            'label' => 'Paid Debt',
            'type' => 'paid_debt',
            'editable' => [
                'route' => 'packages.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.package.paidWithLabelDebt',
            ],
        ],
        'stop_debt' => [
            'label' => 'Stop Debt',
            'type' => 'stop_debt',
            'editable' => [
                'route' => 'packages.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.package.stopDebt',
            ],
        ],
        'broker_fee_link' => [
            'label' => 'Broker Fee link',
            'type' => 'custom.send_link_package',
        ],
        'paid_broker' => [
            'label' => 'Paid broker fee',
            'type' => 'broker_paid',
            'editable' => [
                'route' => 'packages.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.package.brokerPaidWithLabel',
            ],
        ],
        'broker_paid_by' => [
            'label' => 'Broker Paid By',
        ],


//        'filial_name' => [
//            'label' => 'Filial',
//        ],

        'filial_name' => [
            'label' => 'Filial',
        ],
        'filial_name' => [
            'label' => 'Filial',
            'type' => 'filial_change',
            'editable' => [
                'route' => 'packages.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'filial.filials',
            ],
        ],

        'azeriexpressstatus_label' => [
            'label' => 'AzeriExpress Status',
        ],
        'yenipoctstatus_label' => [
            'label' => 'YeniPoct Status',
        ],
        'kargomatstatus_label' => [
            'label' => 'Kargomat Status',
        ],
        'azerpoststatus_label' => [
            'label' => 'Azerpost Status',
        ],
        'suratstatus_label' => [
            'label' => 'Surat Status',
        ],
        'precinctstatus_label' => [
            'label' => 'Precinct Status',
        ],
        /*        'azerpoct_status' => [
                    'label' => 'AzerPoct Status',
                    'type' => 'azerpoct',
            ],
                'zip_code' => [
                    'label' => 'Zip Code',
                ],
        */
        'shipping_fee' => [
            'label' => 'Shipping_fee',
            'type' => 'editable',
            'editable' => [
                'route' => 'packages.ajax',
                'type' => 'text',
                'key' => 'shipping_fee',
            ],
        ],
        'activityworker.name' => [
            //'worker'                => [
            'label' => 'Worker',
        ],
        'id',
        'created_at' => [
            'label' => 'CreatedAt',
            'type' => 'date',
            'order' => 'created_at',
        ],
    ];
    protected $return_fields = [
        [
            'name' => 'action',
            'type' => 'hidden',
            'default' => 'return',
            'short' => true,
        ],
        [
            'name' => 'address',
            'label' => 'Address',
            'type' => 'textarea',
            'prefix' => '<i class="icon-user"></i>',
        ],
        [
            'name' => 'note',
            'label' => 'Note',
            'type' => 'textarea',
            'prefix' => '<i class="icon-user"></i>',
        ],
        [
            'name' => 'file',
            'label' => 'File',
            'type' => 'file',
            'prefix' => '<i class="icon-user"></i>',
            'validation' => 'nullable|mimes:jpeg,jpg,png',
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
            'name' => 'declaration',
            'label' => 'Declared By User',
            'type' => 'checkbox',
            'default' => true,
            'wrapperAttributes' => [
                'class' => 'col-md-4 mt-15',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
        ],
        [
            'label' => 'WareHouse',
            'type' => 'select2',
            'name' => 'warehouse_id',
            'attribute' => 'company_name,country.name',
            'model' => 'App\Models\Warehouse',
            'wrapperAttributes' => [
                'class' => ' col-md-3',
            ],
            'validation' => 'nullable|integer',
            'allowNull' => true,
        ],
        [
            'label' => 'User',
            //'type'              => 'select2',
            'type' => 'select_from_array',
            'name' => 'user_id',
            'attribute' => 'full_name,customer_id',
            'model' => 'App\Models\User',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
                'id' => 'cleardiv',
            ],
            'validation' => 'required|integer',
            'allowNull' => true,
            'attributes' => [
                'data-url' => '/search-users',
                'class' => 'select2-ajax',
            ],
        ],
        [
            'name' => 'status',
            'label' => 'Status',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.package.status',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|integer',
        ],
        /*[
            'name'              => 'paid',
            'label'             => 'Paid',
            'type'              => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.package.paid',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation'        => 'nullable|integer',
        ],*/
        [
            'name' => 'show_label',
            'label' => 'Show label for warehouse',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'col-md-4 mt-15',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
        ],

        [
            'name' => 'custom_id',
            'label' => 'CWB Number',
            'type' => 'text',
            'hint' => 'Special CWB number',
            'prefix' => '<i class="icon-check"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'attributes' => [
                'disabled' => 'disabled',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'tracking_code',
            'label' => 'Tracking Code',
            'type' => 'text',
            'hint' => 'Special Tracking number',
            'prefix' => '<i class="icon-truck"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'nullable|required_without_all:website_name,custom_id|string|min:6|unique:packages,tracking_code',
        ],
        [
            'name' => 'website_name',
            'label' => 'WebSite name',
            'type' => 'text',
            'hint' => 'Also accept url',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|required_without_all:tracking_code,custom_id|string',
        ],
        [
            'name' => 'seller_name',
            'label' => 'Seller name',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12"><h3 class="text-center">Shipping</h3></div>',
        ],
        [
            'type' => 'html',
            'html' => '<div id="package_shipping">',
        ],
        [
            'name' => 'weight',
            'label' => 'Weight',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|numeric',
        ],

        [
            'name' => 'weight_type',
            'label' => 'Weight',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.weight',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'number_items',
            'label' => 'Number Items',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'shipping_amount',
            'label' => 'Invoiced price',
            'type' => 'text',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|numeric',
        ],

        [
            'name' => 'shipping_amount_cur',
            'label' => 'Currency',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.currencies',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'type' => 'html',
            'html' => '</div>',
        ],
        [
            'type' => 'html',
            'html' => '<div id="package_goods"><div class="form-group mt-10 col-lg-12"><h3 class="text-center">Package goods</h3></div>',
        ],
        [
            'type' => 'html',
            'html' => '<div class="row"><div id="type_section" class="col-lg-6">',
        ],
        [
            'type' => 'html',
            'html' => '<div class="row type_item" id="main_type_item">',
        ],
        [
            'type' => 'html',
            'html' => '<div class="col-md-6" id="ru_types_item">',
        ],
        [
            'label' => 'Type',
            'type' => 'select2',
            'name' => 'ru_types[]',
            'attribute' => 'hs_name',
            'model' => 'App\Models\RuType',
            'allowNull' => true,
            'validation' => 'nullable|integer',
            'attributes' => [
                'data-validation' => 'required',
            ],
        ],
        [
            'type' => 'html',
            'html' => '</div>',
        ],
        [
            'type' => 'html',
            'html' => '<div class="col-md-6" id="ase_types_item">',
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
        ],
        [
            'type' => 'html',
            'html' => '</div>',
        ],
        [
            'name' => 'ru_shipping_amounts[]',
            'label' => 'Price',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
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
                'class' => 'col-md-2',
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
                'class' => 'col-md-1',
            ],
            'attributes' => [
                'data-validation' => 'required number',
                'data-validation-allowing' => "range[1;10000]",
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="col-lg-1"> <span class="btn btn-danger btn-icon btn_minus" style="margin-top: 20px"><i
                                        class="icon-minus2"></i></span></div>',
        ],
        [
            'type' => 'html',
            'html' => '</div></div>'
        ],
        [
            'type' => 'html',
            'html' => '<div class="col-lg-1"> <span id="add_type" class="btn btn-primary btn-icon" style="margin-top: 20px"><i
                                        class="icon-plus2"></i></span></div>',
        ],
        [
            'type' => 'html',
            'html' => '</div></div>',
        ],
        [
            'name' => 'has_battery',
            'label' => 'Package has battery',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'col-md-3 mt-15',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'otp_code',
            'label' => 'OTP Code',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2 mt-15',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'additional_delivery_final_price',
            'label' => 'Additional Price',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2 mt-15',
            ],
            'validation' => 'nullable|numeric',
        ],
        [
            'name' => 'invoice',
            'label' => 'Invoice',
            'type' => 'file',
            'wrapperAttributes' => [
                'class' => 'col-md-3 text-center',
            ],
            'validation' => 'nullable|mimes:jpeg,jpg,png,gif,svg,pdf,doc,docx,csv,xls',
        ],
        /* [
             'type' => 'html',
             'html' => '<div class="form-group mt-10 col-lg-12"><h3 class="text-center">Attachments</h3></div>',
         ],*/

        /*[
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12"><h3 class="text-center">Comments</h3></div>',
        ],
        [
            'name'       => 'admin_comment',
            'label'      => 'Admin Comment',
            'type'       => 'textarea',
            'prefix'     => '<i class="icon-user-tie"></i>',
            'validation' => 'nullable|string',
        ],

        [
            'name'       => 'user_comment',
            'label'      => 'User Comment',
            'type'       => 'textarea',
            'prefix'     => '<i class="icon-user"></i>',
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ],

        [
            'name'       => 'warehouse_comment',
            'label'      => 'Warehouse Comment',
            'type'       => 'textarea',
            'prefix'     => '<i class="icon-office"></i>',
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ],
        [
            'name'       => 'bot_comment',
            'label'      => 'Bot Comment',
            'type'       => 'textarea',
            'prefix'     => '<i class="icon-reddit"></i>',
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]*/
    ];


    protected $with = ['type', 'warehouse', 'user', 'logs'];

    protected $listDir = 'admin.packages.list';

    public function __construct()
    {
        $this->fields[8]['default'] = Package::generateCustomId();
        if (\Request::has('action') && \Request::get('action') == 'return_form')
            $this->fields = $this->return_fields;

        parent::__construct();
    }
    public function ue_photos($id)
    {
        $item = Package::find($id);
        if ($item->warehouse_id != 11 || !$item->ukr_express_id || !$item->user || !$item->user->ukr_express_id) {
            return [];
        }

        try {
            $ue = new UkrExpressModel();
            return $ue->track_get_photos($item->ukr_express_id, $item->user->ukr_express_id);
        } catch (\Exception $e) {
            return [];
        }
    }
    public function editObject($id)
    {
        $item = Package::with(['goods'])->find($id);
        return $item;
    }

    public function bagcarrierupdate($id)
    {
        if (Auth::user()->can('customs-reset')) {
            Artisan::call('carriers:update', ['package' => 2, 'package_id' => $id, 'checkonly' => 0, 'htmlformat' => 1]);
            $out = Artisan::output();
        } else {
            $out = "No permissions";
        }
        return view('admin.widgets.carrier_update', ['out' => $out]);
    }

    public function parselcarrierupdate($id)
    {
        if (Auth::user()->can('customs-reset')) {
            Artisan::call('carriers:update', ['package' => 0, 'package_id' => $id, 'checkonly' => 0, 'htmlformat' => 1]);
            $out = Artisan::output();
        } else {
            $out = "No permissions";
        }
        return view('admin.widgets.carrier_update', ['out' => $out]);
    }

    public function carrier_delete($id)
    {
        if (Auth::user()->can('customs-reset')) {
            Artisan::call('carriers:update', ['package' => 1, 'package_id' => $id, 'checkonly' => 0, 'htmlformat' => 1, 'deleteonly' => 1]);
            $out = Artisan::output();
        } else {
            $out = "No permissions";
        }
        return view('admin.widgets.carrier_update', ['out' => $out]);
    }

    public function carrier_update($id)
    {
        if (Auth::user()->can('customs-reset')) {
            Artisan::call('carriers:update', ['package' => 1, 'package_id' => $id, 'checkonly' => 0, 'htmlformat' => 1]);
            $out = Artisan::output();
        } else {
            $out = "No permissions";
        }
        return view('admin.widgets.carrier_update', ['out' => $out]);
    }

    public function invoice($id)
    {
        $item = Package::with(['user', 'warehouse', 'country'])->find($id);
        if (!$item) {
            abort(404);
        }
        return redirect()->to($item->invoice);
    }

    public function zpl($id)
    {
        $item = Package::with(['user', 'warehouse', 'country'])->find($id);
        $shipper = $item->warehouse_id ? $item->warehouse : ($item->country ? $item->country->warehouse : null);

        if ($shipper && !$shipper->country) {
            die("Warehouse doesn't have country.");
        }

        if (!$item) {
            abort(404);
        }
        $item->updateCarrier();


        header("Content-type: x-application/zpl");
        header("Content-Disposition: attachment; filename=" . $id . ".zpl");
        return view('admin.zpl', compact('item', 'shipper'))->render();
    }



    public function ue_info($id)
    {

        $item = Package::find($id);
        if (!$item) {
            abort(404);
        }
//        $ue = new UkrExpressModel();
//        $track = $ue->track_get($item->ukr_express_id, $item->user->ukr_express_id);
//        return  $ue->track_get_photos($track->id, $track->customer_id);
        $new_weight = NULL;
        $new_length = NULL;
        $new_width = NULL;
        $new_height = NULL;
        $photos = [];
        $track = [];
        $user = NULL;
        $message = '';
        $request = \request();

        $action = NULL;
        if ($request->has('action')) {
            $action = $request->get('action');
        }

        if ($item->warehouse_id == 11 && $item->ukr_express_id && $item->user && $item->user->ukr_express_id) {
            $ue = new UkrExpressModel();
            $ue->doLog = false;
            $track = $ue->track_get($item->ukr_express_id, $item->user->ukr_express_id);
            if ($action && $action == 'allow_sending' /*&& !$track->is_sending_allowed*/) {
                $ue->change_sending_permission($item, true);
                $track = $ue->track_get($item->ukr_express_id, $item->user->ukr_express_id);
            } else if ($action && $action == 'not_allow_sending' /*&& $track->is_sending_allowed*/) {
                $ue->change_sending_permission($item, false);
                $track = $ue->track_get($item->ukr_express_id, $item->user->ukr_express_id);
            } else if ($action && $action == 'return_form') {
            } else if ($action && $action == 'return' && $request->isMethod('post')) {
                $res = $ue->return($item, false, $request->get('address'), $request->get('note'), $request->file('file'));
                if (!$res) $message = "Cannot return: " . $ue->message;
                $track = $ue->track_get($item->ukr_express_id, $item->user->ukr_express_id);
            } else if ($action && $action == 'return_cancel' && $track->return_info->requested) {
                $res = $ue->return($item, true);
                if (!$res) $message = "Cannot cancel return: " . $ue->message;
                $track = $ue->track_get($item->ukr_express_id, $item->user->ukr_express_id);
            } else if ($action && $action == 'additional_price' && $request->isMethod('post') && $request->has('aditional_delivery_final_price')) {
                $aditional_delivery_final_price = $request->get('aditional_delivery_final_price');
                $item->additional_delivery_final_price = $aditional_delivery_final_price;
                $item->additional_delivery_price = round($aditional_delivery_final_price / 1.2, 4);
                $item->delivery_price = NULL;
                $item->delivery_price_azn = NULL;
                $item->delivery_price_usd = NULL;
                $item->save();
            }

            if (!$track) $message = "Cannot get track " . $ue->message;
            else {
                if ($track->customer_id != $item->user->ukr_express_id) {
                    if ($ue->change_customer($item)) {
                        sleep(2);
                        $track = $ue->track_get($item->ukr_express_id, $item->user->ukr_express_id);
                        if (!$track) $message = "Cannot get track " . $ue->message;
                    } else {
                        $message = "Cannot change user: " . $ue->message;
                    }
                } else {
                }
                if ($track) {
                    if (isset($track->photos_info) && $track->photos_info->has_any_photos > 0) {
                        //$photos=$ue->track_get_photos($item->ukr_express_id,$item->user->ukr_express_id);
                        $photos = $ue->track_get_photos($track->id, $track->customer_id);
                        if (!$photos) $message = "Cannot get track photo " . $ue->message;
                    }
                    if (isset($track->customer_id))
                        $user = User::where('ukr_express_id', $track->customer_id)->first();

                    if ($track->weight_in_grams)
                        $new_weight = number_format(0 + round($track->weight_in_grams / 1000, 2), 2, ".", "");
                    if (isset($track->dimensions)) {
                        if (isset($track->dimensions->length_mm) && $track->dimensions->length_mm) {
                            $new_length = number_format(0 + round($track->dimensions->length_mm / 10, 2), 2, ".", "");
                        }
                        if (isset($track->dimensions->width_mm) && $track->dimensions->width_mm) {
                            $new_width = number_format(0 + round($track->dimensions->width_mm / 10, 2), 2, ".", "");
                        }
                        if (isset($track->dimensions->height_mm) && $track->dimensions->height_mm) {
                            $new_height = number_format(0 + round($track->dimensions->height_mm / 10, 2), 2, ".", "");
                        }
                    }
                    $parcel = NULL;
                    if (isset($track->customer_id) && isset($track->parcel_id) && $track->customer_id && $track->parcel_id) {
                        $parcel = $ue->parcel_get($track->customer_id, $track->parcel_id);
                    }
                    if ($parcel) {
                        if ($parcel->weight_in_grams)
                            $new_weight = number_format(0 + round($parcel->weight_in_grams / 1000, 2), 2, ".", "");
                        if (isset($parcel->dimensions)) {
                            if (isset($parcel->dimensions->length_mm) && $parcel->dimensions->length_mm) {
                                $new_length = number_format(0 + round($parcel->dimensions->length_mm / 10, 2), 2, ".", "");
                            }
                            if (isset($parcel->dimensions->width_mm) && $parcel->dimensions->width_mm) {
                                $new_width = number_format(0 + round($parcel->dimensions->width_mm / 10, 2), 2, ".", "");
                            }
                            if (isset($parcel->dimensions->height_mm) && $parcel->dimensions->height_mm) {
                                $new_height = number_format(0 + round($parcel->dimensions->height_mm / 10, 2), 2, ".", "");
                            }
                        }
                    }


                }
            }
        }

        return view('admin.widgets.photos', compact('item', 'photos', 'track', 'user', 'message', 'action', 'new_weight', 'new_length', 'new_width', 'new_height'));
    }

    public function label($id)
    {
        $item = Package::with(['user', 'warehouse', 'country'])->find($id);
        $shipper = $item->warehouse_id ? $item->warehouse : ($item->country ? $item->country->warehouse : null);

        if ($shipper && !$shipper->country) {
            die("Warehouse doesn't have country.");
        }

        if (!$item) {
            abort(404);
        }
        $item->updateCarrier();

        return view('admin.widgets.label', compact('item', 'shipper'));
    }

    public function PDFInvoice($id)
    {
        $item = Package::with(['user', 'warehouse', 'country'])->find($id);
        $shipper = $item->warehouse_id ? $item->warehouse : ($item->country ? $item->country->warehouse : null);

        if (!$item) {
            return abort(404);
        }
        if (request()->has('html'))
            return view('front.widgets.invoice', compact('item', 'shipper'));

        $pdf = PDF::loadView('front.widgets.invoice', compact('item', 'shipper'));

        return $pdf->setPaper('a4')->setWarnings(false)->stream($id . '_invoice.pdf');
    }

    public function logs($id)
    {
        $logs = Activity::where('content_id', $id)->where('content_type', Package::class)->orderBy('id', 'desc')->get();
        if (!$logs) {
            return back();
        }

        $packageLogs = PackageLog::query()->where('package_id', $id)->get();
        $CourierShelfLog = CourierShelfLog::where('type','packages')->where('custom_id',$id)->get();
        return view('admin.widgets.logs', compact('logs', 'id', 'packageLogs','CourierShelfLog'));
    }

    /**
     * @return LengthAwarePaginator
     */
    public function indexObject()
    {
        $validator = Validator::make(\Request::all(), [
            'q' => 'string',
            'status' => 'integer',
            'warehouse_id ' => 'integer',
            'start_date' => 'date',
            'start_end' => 'date',
        ]);

        if ($validator->failed()) {
            Alert::error('Unexpected variables!');

            return redirect()->route("my.dashboard");
        }

        //$items = Package::with(['parcel', 'logs', 'transaction', 'portmanat', 'warehouse.country', 'manager', 'user']);

        $items = Package::with(['parcel', 'bag', 'logs', 'transaction', 'portmanat', 'warehouse.country', 'activityworker', 'user']);

        $items = $items->leftJoin('package_carriers', 'packages.id', 'package_carriers.package_id')->leftJoin('parcel_package', 'packages.id', 'parcel_package.package_id')->select('packages.*', 'package_carriers.inserT_DATE', 'parcel_package.parcel_id');

        if (\request()->get('sort') != null) {
            $sortKey = explode("__", \request()->get('sort'))[0];
            $sortType = explode("__", \request()->get('sort'))[1];
            if ($sortKey == 'carrier') {
                $items = $items->orderBy('package_carriers.depesH_DATE', $sortType)->orderBy('package_carriers.ecoM_REGNUMBER', $sortType)->orderBy('package_carriers.inserT_DATE', $sortType)->orderBy('id', 'desc');
                //$items = $items->orderBy('package_carriers.inserT_DATE', $sortType)->orderBy('package_carriers.ecoM_REGNUMBER', $sortType)->orderBy('package_carriers.depesH_DATE', $sortType)->orderBy('id', 'desc');
            } else {
                $items = $items->orderBy($sortKey, $sortType)->orderBy('packages.id', 'desc');
            }
        } else {
            $items = $items->orderBy('packages.created_at', 'desc')->orderBy('packages.id', 'desc');
        }

        if (\Request::get('tl') != null) {
            $tracking_codes = preg_split("/[;:,\s]+/", trim(\Request::get('tl')));
            $items = $items->whereIn('packages.tracking_code', $tracking_codes)->orWhereIn('packages.custom_id', $tracking_codes);
        }

        if (\Request::get('incustoms') != null) {
            $items->incustoms(\Request::get('incustoms'));
        }

        if (\Request::get('dec') != 3) {
            $items = $items->where('packages.status', '!=', 3);
        }

        /* Filter cities */
        $cities = auth()->guard('admin')->user()->cities->pluck('id')->all();
        if ($cities) {
            $items->whereHas('user', function (
                $query
            ) use ($cities) {
                $query->whereIn('city_id', $cities)->orWhere('city_id', null);
            });
        } else {
            if (request()->get('city_id') != null) {
                $items->whereHas('user', function (
                    $query
                ) {
                    $query->where('city_id', request()->get('city_id'));
                });
            }
        }

        if (\Request::get('q') != null) {
            $q = str_replace('"', '', \Request::get('q'));
            $items->where(function ($query) use ($q) {
                $query->orWhere("tracking_code", "LIKE", "%" . $q . "%")->orWhereRaw(\DB::raw('concat("E", (6005710000 + packages.id)) = "' . $q . '"'))->orWhere("website_name", "LIKE", "%" . $q . "%")->orWhere("packages.custom_id", "LIKE", "%" . $q . "%")->orWhereHas('user', function (
                    $query
                ) use ($q) {
                    $query->where('customer_id', 'LIKE', '%' . $q . '%')->orWhere('passport', 'LIKE', '%' . $q . '%')->orWhere('fin', 'LIKE', '%' . $q . '%')->orWhere('phone', 'LIKE', '%' . $q . '%')->orWhere('email', 'LIKE', '%' . $q . '%')->orWhere(DB::raw('concat(trim(name)," ",trim(surname))'), 'LIKE', "%" . $q . "%")->orWhereHas('dealer', function (
                        $query
                    ) use ($q) {
                        $query->where('customer_id', 'LIKE', '%' . $q . '%')->orWhere('passport', 'LIKE', '%' . $q . '%')->orWhere('fin', 'LIKE', '%' . $q . '%')->orWhere('phone', 'LIKE', '%' . $q . '%')->orWhere('email', 'LIKE', '%' . $q . '%')->orWhere(DB::raw('concat(trim(name)," ",trim(surname))'), 'LIKE', "%" . $q . "%");
                    });
                });
                $query->orWhere("detailed_type", "LIKE", "%" . $q . "%");
            });
        }
        if (\Request::get('parcel') != null) {
            if (\Request::get('parcel') == 'NO') {
                $items->doesntHave('parcel');
            } else {
                $items->whereHas('parcel', function ($query) {
                    $customIds = explode(',', \Request::get('parcel'));
                    $parcelIds = Parcel::whereIn('custom_id', $customIds)->pluck('id')->all();

                    $query->whereIn('parcel_id', $parcelIds ?: [0]);
                });
            }
        }

        if (\Request::get('dec') == 2) {
            $items->ready();
        }
        if (\Request::get('dec') == 1) {
            $items->whereNull('shipping_amount_goods')->whereNotNull('tracking_code');
        }

        if (\Request::get('status') != null) {
            $items->where('packages.status', \Request::get('status'));
        }

        /*$ukr_express_status=\Request::get('ukr_express_status');
            if ($ukr_express_status != null) {
            if($ukr_express_status == 200)
                    $items->whereRaw('(((packages.ukr_express_id is not null) and (packages.ukr_express_id>0)) and (packages.ukr_express_parcel_id is null) or (packages.ukr_express_parcel_id<=0))');
            else if ($ukr_express_status == 201)
                    $items->whereNotNull('packages.ukr_express_error_at');
            else
                    $items->where('packages.ukr_express_status', $ukr_express_status);
        }*/
        $bp_status = \Request::get('bp_status');
        if ($bp_status != null) {
            switch ($bp_status) {
                case 1:
                    $items->whereRaw('(parcel_package.parcel_id is not null)');
                    break;
                case 2:
                    $items->whereRaw('(parcel_package.parcel_id is null)');
            }
        }

        $paid_debt = \Request::get('paid_debt');

        if($paid_debt != null){

            if($paid_debt == 1){
                $items->whereIn('paid_debt',[1,3])->where('debt_price','>',0);
            }elseif($paid_debt == 2){
                $items->where('paid_debt',0)->where('debt_price','>',0);
            }

        }
        $paid_broker = \Request::get('paid_broker');

        if($paid_broker != null){

            if($paid_broker){
                $items->where('paid_broker', 1);
            }else{
                $items->where('paid_broker',0);
            }

        }

        $ue_status = \Request::get('ue_status');
        if ($ue_status != null) {
            switch ($ue_status) {
                case 1:
                    $items->whereRaw('(packages.ukr_express_id is not null)');
                    break;
                case 2:
                    $items->whereRaw('((packages.ukr_express_pd >= 1) and (packages.ukr_express_dec >= 1) and ((packages.ukr_express_parcel_id is null) or (packages.ukr_express_parcel_id <= 0)))');
                    break;
                case 3:
                    $items->whereRaw('((packages.ukr_express_parcel_id is not null) and (packages.ukr_express_parcel_id > 0))');
                    break;
                case 4:
                    $items->whereRaw('((packages.ukr_express_id is not null) and ((packages.ukr_express_parcel_id is null) or (packages.ukr_express_parcel_id <= 0)))');
                    break;
                case 7:
                    $items->whereRaw('((packages.ukr_express_id is not null) and (packages.ukr_express_dec = 0))');
                    break;
                case 5:
                    $items->whereRaw("(packages.ukr_express_error_at is not null and packages.bot_comment != 'tracking-attention')");
                    break;
                case 6:
                    $items->whereRaw('(packages.ukr_express_id is null)');
                    break;
                case 8:
                    $items->whereRaw("(packages.ukr_express_error_at is not null and packages.bot_comment = 'tracking-attention')");
                    break;

            }
        }


        if (\Request::get('discount') != null) {
            $discount = \Request::get('discount');
            switch ($discount) {
                case 0:
                    $items->whereNull('discount_percent')->whereNull('ulduzum_discount_percent')->whereNull('promo_id');
                    break;
                case 1:
                    $items->where(function ($query) {
                        $query->whereNotNull('discount_percent')->orwhereNotNull('ulduzum_discount_percent')->orwhereNotNull('promo_id');
                    });
                    break;
                case 2:
                    $items->whereNotNull('discount_percent');
                    break;
                case 3:
                    $items->whereNotNull('ulduzum_discount_percent');
                    break;
                case 4:
                    $items->whereNotNull('promo_id');
                    break;
            }
        }

        if (\Request::get('paid') != null) {
            $paid = \Request::get('paid');
            $items->where('paid', boolval($paid));
            if ($paid >= 1) {
                $items->where('paid', 1);
                $paidStr = config('ase.attributes.package.paid2')[$paid];
                $items = $items->leftJoin('transactions', 'packages.id', 'transactions.custom_id');
                if ($paid >= 2)
                    $items->paidstr($paidStr);
                $items->whereNotIn('transactions.type', ['ERROR','PENDING']);
            }
        }

        /*  if (\Request::get('azerpoct') != null) {
              $azerpoct = \Request::get('azerpoct');
              if ($azerpoct >= 1) {
                  if ($azerpoct == 1) // not in Azerpoct
                      $items->where('azerpoct_send', 0);
                  else // Azerpoct send
                      $items->where('azerpoct_send', 1);
                  if ($azerpoct == 3) // Azerpoct paid
                      $items->where('azerpoct_paid', 1);
              }
      }*/

        if (\Request::get('warehouse_id') != null) {
            $items->where('warehouse_id', \Request::get('warehouse_id'));
        }

//        if (\Request::get('start_date') != null) {
//            $dateField = \Request::get('date_by', 'created_at');
//            $dateField = 'packages.' . $dateField;
//            $items->where($dateField, '>=', \Request::get('start_date') . " 00:00:00");
//        }
//        if (\Request::get('end_date') != null) {
//            $dateField = \Request::get('date_by', 'created_at');
//            $dateField = 'packages.' . $dateField;
//            $items->where($dateField, '<=', \Request::get('end_date') . " 23:59:59");
//        }
        $dateField = \Request::get('date_by', 'created_at');

        if (auth('admin')->user()->id == 101) {
            $dateField = 'scanned_at';
        }

        $dateField = 'packages.' . $dateField;

        if (\Request::get('start_date') != null) {
            $items->where($dateField, '>=', \Request::get('start_date') . " 00:00:00");
        }

        if (\Request::get('end_date') != null) {
            $items->where($dateField, '<=', \Request::get('end_date') . " 23:59:59");
        }

        $items_all = null;

        if (\request()->get('search_type') == 'export' || \request()->has('export')) {
            if ($items->count()) {
                $items = $items->get();
            } else {
                if (\Request::get('sta') == 1)
                    $items_all = $items->get();
                $items = $items->paginate($this->limit);
            }
        } else {
            if (\Request::get('sta') == 1)
                $items_all = $items->get();
            $items = $items->paginate($this->limit);
        }

        if(!Auth::guard('admin')->user()->can('in-customs-debt-read')) {
            //eger in-customs-debt-read rolu icazesi yoxdursa $list aşağıda ki elemntleri gizledirik
            unset($this->list['stop_debt']);
            unset($this->list['paid_debt']);
            unset($this->list['debt_price']);
        }

        View::share('items_all', $items_all);
        return $items;

    }

    public function bagdepesh($id = null)
    {
        if (Auth::user()->can('customs-depesh')) {
            Artisan::call('depesh', ['package' => 2, 'parcel_id' => $id, 'checkonly' => 0, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function bagdepeshcheck($id = null)
    {
        if (Auth::user()->can('customs-check')) {
            Artisan::call('depesh', ['package' => 2, 'parcel_id' => $id, 'checkonly' => 1, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function packagecanceldepesh($id = null)
    {
        if (Auth::user()->can('customs-depesh')) {
            Artisan::call('canceldepesh', ['package' => 1, 'parcel_id' => $id, 'checkonly' => 0, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function parselcanceldepesh($id = null)
    {
        if (Auth::user()->can('customs-depesh')) {
            Artisan::call('canceldepesh', ['package' => 0, 'parcel_id' => $id, 'checkonly' => 0, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }


    public function packagedepesh($id = null)
    {
        if (Auth::user()->can('customs-depesh')) {
            Artisan::call('depesh', ['package' => 1, 'parcel_id' => $id, 'checkonly' => 0, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function packagedepeshcheck($id = null)
    {
        if (Auth::user()->can('customs-check')) {
            Artisan::call('depesh', ['package' => 1, 'parcel_id' => $id, 'checkonly' => 1, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();

        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function parseldepesh($id = null)
    {
        if (Auth::user()->can('customs-depesh')) {
            Artisan::call('depesh', ['package' => 0, 'parcel_id' => $id, 'checkonly' => 0, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function parselukrexpress($id = null)
    {
        $d_out = '';
        if (Auth::user()->can('customs-depesh')) {
            $parcel = Parcel::find($id);
            if (!$parcel) {
                $d_out = "Parcel does not exists";
            } else if ($parcel->warehouse_id != 11) {
                $d_out = "Parcel is not USA warehouse";
            } else {
                Artisan::call('ukraine:express', ['--w_id' => 11, '--type' => 'sync', '--sync_parcel' => $parcel->custom_id]);
                $d_out = Artisan::output();
            }
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function parseldepeshcheck($id = null)
    {
        if (Auth::user()->can('customs-check')) {
            Artisan::call('depesh', ['package' => 0, 'parcel_id' => $id, 'checkonly' => 1, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function export($items = null)
    {
        if (request()->has('hidden_items')) {
            $items = explode(",", request()->get('hidden_items'));
        }
        if (is_string($items)) {
            $parcel = Parcel::find($items);

            if ($parcel) {
                $items = $parcel->packages;
            } else {
                $items = [];
            }
        }

        $formats = ['Xlsx' => 'Xlsx', 'Mpdf' => 'pdf'];
        $type = isset($formats[\request()->get('format')]) ? \request()->get('format') : 'Xlsx';
        $ext = $formats[$type];

        if ($ext == 'pdf') {
            $pdf = PDF::loadView('admin.exports.pdf_packages', compact('items'));
            return $pdf->download('packages_' . uniqid() . '.' . $ext);
        }

        return Excel::download(new PackagesExport($items), 'packages_' . uniqid() . '.' . $ext, $type);
    }

    public function manifest($items = null)
    {
        $warehouse = null;
        if (request()->has('hidden_items')) {
            $items = explode(",", request()->get('hidden_items'));
        }
        if (is_string($items)) {
            $parcel = Parcel::find($items);
            $warehouse = $parcel->warehouse;

            if ($parcel) {
                $items = $parcel->packages;
            } else {
                $items = [];
            }
        }

        $formats = ['Xlsx' => 'xlsx', 'Mpdf' => 'pdf'];
        if ($warehouse->allow_make_fake_invoice) {
            $type = 'Xlsx';
            $ext = 'xlsx';
        } else {
            $type = isset($formats[\request()->get('format')]) ? \request()->get('format') : 'Xlsx';
            $ext = $formats[$type];
        }

        return Excel::download(new ManifestExport($items, $warehouse, $type), 'manifest_' . uniqid() . '.' . $ext, $type);
    }

    public function store(Request $request)
    {
        if ($request->get('warehouse_id') == 12) {
            $this->fields[7]['validation'] = 'nullable|required_without_all:website_name,custom_id|string|min:5|unique:packages,tracking_code';
        }
        $res = parent::store($request);
        $id = $this->itemId;
        if (!empty($id)) {
            $package = Package::find($id);
            if ($package) $package->saveGoodsFromRequest($request);
        }
        return $res;
    }

    public function update(Request $request, $id)
    {
        $package       = Package::findOrFail($id);
        $oldUserId     = $package->user_id;
        $newUserId     = $request->input('user_id', $oldUserId);


        if ($oldUserId != $newUserId) {
            Artisan::call('carriers:update', [
                'package'    => 1,
                'package_id' => $id,
                'checkonly'  => 0,
                'htmlformat' => 0,
                'deleteonly' => 1,
            ]);
        }

        if ($request->filled('additional_delivery_final_price') && $request->get('additional_delivery_final_price') > 0) {

            $finalPrice = $request->get('additional_delivery_final_price');

            $package->delivery_price_azn += $finalPrice;
            $package->delivery_price_usd += $finalPrice / 1.7;
            $package->additional_delivery_final_price = $finalPrice;
            $package->save();

        } elseif ((!$request->filled('additional_delivery_final_price') || $request->get('additional_delivery_final_price') == 0)
            && !is_null($package->additional_delivery_final_price)) {

            $finalPrice = $package->additional_delivery_final_price;

            $package->delivery_price_azn -= $finalPrice;
            $package->delivery_price_usd -= $finalPrice / 1.7;
            $package->additional_delivery_final_price = null;
            $package->save();
        }



        $res = parent::update($request, $id);


        $package->refresh();
        $reallyNewUserId = $package->user_id;

        if ($oldUserId != $reallyNewUserId) {
            Artisan::call('carriers:update', [
                'package'    => 1,
                'package_id' => $id,
                'checkonly'  => 0,
                'htmlformat' => 0,
            ]);
        }


        if (trim($package->status) !== trim($request->get('status'))) {
            PackageLog::create([
                'data'      => json_encode([
                    'status' => [
                        'before' => trim($package->getOriginal('status')),
                        'after'  => trim($request->get('status')),
                    ],
                ]),
                'admin_id'  => Auth::guard('admin')->id(),
                'package_id'=> $id,
            ]);
            Notification::sendPackage($id, trim($request->get('status')));
        }

        $package->saveGoodsFromRequest($request);

        return $res;
    }


    public function ajax(Request $request, $id)
    {

        $used = Package::find($id);
        if ($request->get('name') == 'parcel_name') {
            $data = [];
            $new_parcel_name = trim($request->get('value'));
            if (strtoupper($new_parcel_name) != 'NO' && trim($used->parcel_name) != $new_parcel_name) {
                $str = "select pl.id,count(pp.package_id) as cnt from parcels pl left outer join parcel_package pp on pp.parcel_id=pl.id";
                $str .= " where upper(pl.custom_id)='" . strtoupper($new_parcel_name) . "' and pl.warehouse_id=" . $used->warehouse_id;
                $str .= "	group by pl.id";
                $pls = DB::select($str);
                if ($pls && count($pls) > 0 && $pls[0]->id && $pls[0]->cnt > 0) {
                    $pl = $pls[0];
                    $str = "select bg.id,count(bp.package_id) as cnt from bags bg left outer join bag_package bp on bp.bag_id=bg.id";
                    $str .= " where bg.parcel_id='" . $pl->id . "'";
                    $str .= " group by bg.id";
                    $str .= " having count(bp.package_id)>0";
                    $str .= " order by bg.id desc limit 1";
                    $bgs = DB::select($str);
                    if ($bgs && count($bgs) > 0 && $bgs[0]->id && $bgs[0]->cnt > 0) {
                        $bg = $bgs[0];
                        $data['parcel'] = [
                            'from parcel' => trim($used->parcel_name),
                            'to parcel' => trim($request->get('value')),
                        ];
                        DB::delete("delete from parcel_package where package_id=?", [$id]);
                        DB::delete("delete from bag_package where package_id=?", [$id]);
                        DB::insert("insert into parcel_package (parcel_id,package_id) values (?,?)", [$pl->id, $id]);
                        DB::insert("insert into bag_package (bag_id,package_id) values (?,?)", [$bg->id, $id]);
                        $activity = Activity::create([
                            'admin_id' => auth()->guard('admin')->check() ? auth()->guard('admin')->user()->id : null,
                            'content_id' => $id,
                            'content_type' => 'App\Models\Package',
                            'action' => 'update',
                            'description' => 'Updated Package Parcel',
                            'details' => json_encode($data),
                            'user_agent' => (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : null,
                        ]);
                        if (!empty($data)) {
                            $log = new PackageLog();
                            $log->data = json_encode($data);
                            $log->admin_id = Auth::guard('admin')->user()->id;
                            $log->package_id = $id;
                            $log->save();
                        }
                        return Response::json(['success' => true]);
                    }
                }
            } else {
                if (strtoupper($new_parcel_name) == 'NO' || empty($new_parcel_name)) {
                    $data['parcel'] = [
                        'from parcel' => trim($used->parcel_name),
                        'to parcel' => trim($request->get('value')),
                    ];
                    DB::delete("delete from parcel_package where package_id=?", [$id]);
                    DB::delete("delete from bag_package where package_id=?", [$id]);
                    $activity = Activity::create([
                        'admin_id' => auth()->guard('admin')->check() ? auth()->guard('admin')->user()->id : null,
                        'content_id' => $id,
                        'content_type' => 'App\Models\Package',
                        'action' => 'update',
                        'description' => 'Updated Package Parcel & Bag',
                        'details' => json_encode($data),
                        'user_agent' => (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : null,
                    ]);

                    if (!empty($data)) {
                        $log = new PackageLog();
                        $log->data = json_encode($data);
                        $log->admin_id = Auth::guard('admin')->user()->id;
                        $log->package_id = $id;
                        $log->save();
                    }
                    return Response::json(['success' => true]);
                }
            }
            $request->merge(['value' => $used->parcel_name]);
            return Response::json(['success' => false]);
        }
        if ($request->get('name') == 'bag_name') {
            $data = [];
            $new_bag_name = trim($request->get('value'));
            if ($new_bag_name && $used->parcel_id && strtoupper($new_bag_name) != 'NO' && trim($used->bag_name) != $new_bag_name) {
                $str = "select bg.id,count(bp.package_id) as cnt from bags bg left outer join bag_package bp on bp.bag_id=bg.id";
                $str .= " where bg.parcel_id='" . $used->parcel_id . "' and upper(bg.custom_id)='" . strtoupper($new_bag_name) . "'";
                $str .= "	group by bg.id";
                $str .= " having count(bp.package_id)>0";
                $str .= "	order by bg.id desc limit 1";
                $bgs = DB::select($str);
                if ($bgs && count($bgs) > 0 && $bgs[0]->id && $bgs[0]->cnt > 0) {
                    $bg = $bgs[0];
                    $data['bag'] = [
                        'from bag' => trim($used->bag_name),
                        'to bag' => trim($request->get('value')),
                    ];
                    DB::delete("delete from bag_package where package_id=?", [$id]);
                    DB::insert("insert into bag_package (bag_id,package_id) values (?,?)", [$bg->id, $id]);
                    $activity = Activity::create([
                        'admin_id' => auth()->guard('admin')->check() ? auth()->guard('admin')->user()->id : null,
                        'content_id' => $id,
                        'content_type' => 'App\Models\Package',
                        'action' => 'update',
                        'description' => 'Updated Package Bag',
                        'details' => json_encode($data),
                        'user_agent' => (isset($_SERVER['HTTP_USER_AGENT'])) ? $_SERVER['HTTP_USER_AGENT'] : null,
                    ]);
                    if (!empty($data)) {
                        $log = new PackageLog();
                        $log->data = json_encode($data);
                        $log->admin_id = Auth::guard('admin')->user()->id;
                        $log->package_id = $id;
                        $log->save();
                    }
                    return Response::json(['success' => true]);
                }
            }
            $request->merge(['value' => $used->bag_name]);
            return Response::json(['success' => false]);
        }

        if ($request->get('name') == 'status') {

            $data = [];

            if (trim($used->status) != trim($request->get('value'))) {
                $data['status'] = [
                    'before' => trim($used->status),
                    'after' => trim($request->get('value')),
                ];

                /* Send Notification */
                Notification::sendPackage($id, trim($request->get('value')));
            }

            if (trim($request->get('value')) == 3) {

                if( ($used->debt_price > 0 && $used->paid_debt == 0) || $used->paid == 0 ){
                    return \Response::json(['message' => ' Items have debt price!'],400);
                }

                event(new PackageCell('done', $used->id));
            }
            if (!empty($data)) {
                $log = new PackageLog();
                $log->data = json_encode($data);
                $log->admin_id = Auth::guard('admin')->user()->id;
                $log->package_id = $id;
                $log->save();
            }
        }

        if($request->get('name') == 'filial_name'){
            $value = $request->get('value');
            $parts = explode(' - ', $value);

            $fid = $parts[0];
            $typeIdParts = $parts[1];
            $typeId = explode('-',$typeIdParts)[0];
            if($typeId == 'ASE'){
                $filial = DeliveryPoint::find($fid);
                $used->store_status = $filial->id;
                $used->azeri_express_office_id = null;
                $used->azerpost_office_id = null;
                $used->surat_office_id = null;
                $used->yenipoct_office_id = null;
                $used->kargomat_office_id = null;
                $used->save();

            }elseif($typeId == 'AZEXP'){

                $filial = AzeriExpressOffice::find($fid);
                $used->store_status = null;
                $used->azeri_express_office_id = $filial->id;
                $used->azerpost_office_id = null;
                $used->surat_office_id = null;
                $used->yenipoct_office_id = null;
                $used->kargomat_office_id = null;
                $used->save();

            }elseif($typeId == 'AZPOST'){

                $filial = AzerpostOffice::find($fid);
                $used->store_status = null;
                $used->azeri_express_office_id = null;
                $used->azerpost_office_id = $filial->id;
                $used->surat_office_id = null;
                $used->yenipoct_office_id = null;
                $used->kargomat_office_id = null;
                $used->save();

            }elseif($typeId == 'SURAT'){

                $filial = SuratOffice::find($fid);
                $used->store_status = null;
                $used->azeri_express_office_id = null;
                $used->azerpost_office_id = null;
                $used->kargomat_office_id = null;
                $used->surat_office_id = $filial->id;
                $used->yenipoct_office_id = null;
                $used->save();

            }elseif ($typeId == 'YP'){

                $filial = YenipoctOffice::find($fid);
                $used->store_status = null;
                $used->azeri_express_office_id = null;
                $used->azerpost_office_id = null;
                $used->surat_office_id = null;
                $used->kargomat_office_id = null;
                $used->yenipoct_office_id = $filial->id;
                $used->save();

            }elseif ($typeId == 'KARGOMAT'){

                $filial = KargomatOffice::find($fid);
                $used->store_status = null;
                $used->azeri_express_office_id = null;
                $used->azerpost_office_id = null;
                $used->surat_office_id = null;
                $used->yenipoct_office_id = null;
                $used->kargomat_office_id = $filial->id;
                $used->save();

            }else{
                echo 'Type id not found';
            }

            return response()->json(['message' => 'Success'], 200);

        }

        if ($request->get('name') == 'paid') {

//            if($request->get('value') >= 1 && $used->debt_price > 0 && $used->paid_debt == 0){
//                return \Response::json(['message' => ' Items have debt price!'],400);
//                exit();
//            }

            if ($request->get('value') != 0) {
                $type = $request->get('value') == 1 ? 'CASH' : config('ase.attributes.package.paid')[$request->get('value')];
                $request->merge(['value' => 1]);
                Transaction::addPackage($used->id, $type);

                dispatch(new UpdateCarrierPackagePaymentStatusJob($used->custom_id))->onQueue('default');
            } else {
                $check = Transaction::where('custom_id', $used->id)->where('paid_for', 'PACKAGE')->where('type', 'OUT')->first();
                if ($check && $check->paid_by != 'PORTMANAT') {
                    Transaction::where('custom_id', $used->id)->where('paid_for', 'PACKAGE')->delete();
                }
            }
            if ($request->get('value') != 0 && ($used->status == 2 || $used->status == 8)) {
                $used->requested_at = Carbon::now();
                $used->save();

                dispatch(new UpdateCarrierPackagePaymentStatusJob($used->custom_id))->onQueue('default');

                event(new PackageCell('find', $used->id));
            }
            if ($request->get('value') == 0) {
                $used->requested_at = null;
                $used->save();
            }
        }



        if ($request->get('name') == 'paid_debt') {
            $admin = Auth::user();

            if ($request->get('value') == 1) {
                return response()->json(['message' => 'Yes, it cannot be selected.!'], 400);
            }

            if ($request->get('value') == 2) {
                if (!$admin->hasRole('super_admin')) {
                    return response()->json(['message' => 'Only Super Admin can select this option!'], 403);
                }

            }

            if ($request->get('value') == 3) {
                Transaction::create([
                    'user_id'   => $used->user_id,
                    'custom_id' => $used->id,
                    'paid_by'   => 'KAPITAL',
                    'amount'    => $used->debt_price,
                    'source_id' => null,
                    'type'      => 'OUT',
                    'paid_for'  => 'PACKAGE_DEBT',
                    'debt'      => 1,
                ]);

            }
        }if ($request->get('name') == 'broker_paid') {
            $admin = Auth::user();

            if ($request->get('value') == 1) {
                return response()->json(['message' => 'Yes, it cannot be selected.!'], 400);
            }

            if ($request->get('value') == 2) {
                if (!$admin->hasRole('super_admin')) {
                    return response()->json(['message' => 'Only Super Admin can select this option!'], 403);
                }
                $used->broker_price = (empty($used->user->voen)) ? 15 : 50;
                $used->save();
            }

            if ($request->get('value') == 3) {
                Transaction::create([
                    'user_id'   => $used->user_id,
                    'custom_id' => $used->id,
                    'paid_by'   => 'KAPITAL',
                    'amount'    => (empty($used->user->voen)) ? 15 : 50,
                    'source_id' => null,
                    'type'      => 'OUT',
                    'paid_for'  => 'PACKAGE_BROKER',
                ]);
                $used->broker_price = (empty($used->user->voen)) ? 15 : 50;
                $used->save();
            }
        }


        if ($request->get('name') == 'shipping_fee') {
            $shipping_fee = Transaction::addPackageFee($used->id, $request->get('value'));
            $request->merge(['value' => $shipping_fee]);
        }

        if ($request->get('name') == 'worker_comments') {
            $used->worker_comments = $request->get('value');
            $used->save();
            return;
        }

        return parent::ajax($request, $id);
    }

    public function request($id)
    {
        $used = Package::find($id);
        if ($used) {

            $used->requested_at = $used->requested_at ? null : Carbon::now();
            $used->save();

            event(new PackageCell('find', $used->id));

            return "1";
        }

        return "0";
    }

//    public function barcodeScan($code = null)
//    {
//        if (!$code) {
//            return response()->json([
//                'error' => 'Empty barcode. Please scan a package!',
//            ]);
//        }
//
//        if ($code == 'courier-page') {
//            return response()->json([
//                'redirect' => route('courier.shelf.add.product'),
//            ]);
//        }
//
//        $scanPath = '';
//        if (\Request::has('path'))
//            $scanPath = \Request::get('path');
//        // Check barcode
//        $cell = findCell($code);
//        if (!empty($cell)) {
//            return response()->json([
//                'cell' => $cell
//            ]);
//        }
//
//        $admin = Auth::user();
//
//        $track = Track::query()->where('tracking_code', $code)->first();
//        $package = null;
//        if (!$track) {
//            $package = Package::whereTrackingCode($code)->orWhere('custom_id', $code)->first();
//        }
//
////        if (isset($track) && in_array($track->status, [19, 27])) {
////            $track->scanned_at = Carbon::now();
////            $track->save();
////            //(new PackageService())->updateStatus($track, 19);
////            return response()->json([
////                'error' => 'Rejected statusunda olan bağlama',
////            ]);
////        }
//
//        if (isset($track) && in_array($track->status, [45])) {
//            $track->scanned_at = Carbon::now();
//            $track->save();
//            (new PackageService())->updateStatus($track, 44);
//            return response()->json([
//                'error' => 'Saxlanc statusunda olan bağlama',
//            ]);
//        }
//
//        if ($package) {
//
//            if($admin->check_declaration){
//
//                $package->bot_comment = "Saxlanc hesabı tərəfindən scan edildi.";
//                $package->save();
//                if(!$package->is_in_customs){
//                    return response()->json([
//                        'error' => 'NO DECLARATION IN CUSTOMS '. $package->custom_id,
//                    ]);
//
//                }else{
//
//                    return response()->json([
//                        'success' => 'DECLARED IN CUSTOMS '. $package->custom_id,
//                    ]);
//                }
//
//            }
//
//            $user = $package->user;
//            if (!app('laratrust')->can('update-cells') && app('laratrust')->can('update-paids')) {
//                return response()->json([
//                    'redirect' => route('paids.index', ['cwb' => $package->custom_id]),
//                ]);
//            }
////            if ($admin->scan_check_only && !$admin->scan_no_alerts) {
////                $message = '';
////                if($package->debt_price && $package->paid_debt == 0){
////                    $message = "Baglamanin saxlanc odenisi var($package->debt_price) ve ÖDƏNİlMƏYİB!";
////                }else{
////                    $message = "Bağlamanın saxlanc ödənişi var($package->debt_price) ve ödənilib!";
////                }
////
////                return response()->json([
////                    'error' => $message,
////                ]);
////            }
//            $status = $package->status;
//
////            if (!in_array($package->store_status, [1,3,4,7,8]) && $package->paid == 0){
////                $message = 'PACKAGE NOT PAID !';
////                return response()->json([
////                    'error' => $message,
////                ]);
////            }
//            $notification = false;
//            /* Send Notification */
//            if ($admin->store_status == 2) { //In Kobia
//                if ($status != 8) {
//                    $package->status = 8;
//                    $package->save();
//                    $notification = true;
//                    //Send notification only if user selected kobia filial
//                    if ($user && !$user->real_azeri_express_use && !$user->real_azerpoct_send && !$user->real_yenipoct_use && !$user->real_kargomat_use && ($user->real_store_status == $admin->store_status) && $user->delivery_point) {
//                        Notification::sendPackage($package->id, 8);
//                    }
//                }
//            } else { // In Baku
//                if ($status != 2) {
//                    if (($package->store_status && $package->delivery_point)
//                        || ($package->azeri_express_office_id && $package->azeri_express_office)
//                        || ($package->surat_office_id && $package->surat_office)
//                        || ($package->yenipoct_office_id && $package->yenipoct_office)
//                        || ($package->kargomat_office_id && $package->kargomat_office)
//                        || ($package->azerpost_office_id && $package->azerpost_office)) {
//                        if (($package->store_status && $package->delivery_point) && ($package->store_status != $admin->store_status)) {
//                            $message = ' WRONG PACKAGE FILIAL ! ' . $package->delivery_point->description . ' Send to Kobia';
//                            return response()->json([
//                                'error' => $message,
//                            ]);
//                        }
//                        if ($package->azeri_express_office_id && $package->azeri_express_office) {
//                            $message = ' WRONG PACKAGE AZERI EXPRESS ! ' . $package->azeri_express_office->description . ' Send to Kobia';
//                            return response()->json([
//                                'error' => $message,
//                            ]);
//                        }
//                        if ($package->yenipoct_office_id && $package->yenipoct_office) {
//                            $message = ' WRONG PACKAGE YENI POCT ! ' . $package->yenipoct_office->description . ' Send to Kobia';
//                            return response()->json([
//                                'error' => $message,
//                            ]);
//                        }
//                        if ($package->kargomat_office_id && $package->kargomat_office) {
//                            $message = ' WRONG PACKAGE Kargomat ! ' . $package->kargomat_office->description . ' Send to Kobia';
//                            return response()->json([
//                                'error' => $message,
//                            ]);
//                        }
//                        if ($package->surat_office_id && $package->surat_office) {
//                            $message = ' WRONG PACKAGE SURAT CARGO ! ' . $package->surat_office->description . ' Send to Kobia';
//                            return response()->json([
//                                'error' => $message,
//                            ]);
//                        }
//                        if ($package->azerpost_office_id && $package->azerpost_office) {
//                            $message = ' WRONG USER AZERPOST ! ' . strtoupper($package->azerpost_office->name) . ' Send to Kobia';
//                            return response()->json([
//                                'error' => $message,
//                            ]);
//                        }
//                    } else {
//                        if ($user && !$user->real_azeri_express_use && !$user->real_azerpoct_send && ($user->real_store_status != $admin->store_status) && $user->delivery_point) {
//                            $message = ' WRONG UER FILIAL ! ' . $user->delivery_point->description . ' Send to Kobia';
//                            return response()->json([
//                                'error' => $message,
//                            ]);
//                        }
//                        if ($user && $user->real_azeri_express_use && $user->real_azeri_express_office_id && $user->azeri_express_office) {
//                            $message = ' WRONG USER AZERI EXPRESS ! ' . $user->azeri_express_office->description . ' Send to Kobia';
//                            return response()->json([
//                                'error' => $message,
//                            ]);
//                        }
//                        if ($user && $user->real_surat_use && $user->real_surat_office_id && $user->surat_office) {
//                            $message = ' WRONG USER SURAT CARGO ! ' . $user->surat_office->description . ' Send to Kobia';
//                            return response()->json([
//                                'error' => $message,
//                            ]);
//                        }
//                        if ($user && $user->real_yenipoct_use && $user->real_yenipoct_office_id && $user->yenipoct_office) {
//                            $message = ' WRONG USER YENIPOCT CARGO ! ' . $user->yenipoct_office->description . ' Send to Kobia';
//                            return response()->json([
//                                'error' => $message,
//                            ]);
//                        }
//                        if ($user && $user->real_kargomat_use && $user->real_kargomat_office_id && $user->kargomat_office) {
//                            $message = ' WRONG USER Kargomat CARGO ! ' . $user->kargomat_office->description . ' Send to Kobia';
//                            return response()->json([
//                                'error' => $message,
//                            ]);
//                        }
//                        if ($user && $user->real_azerpoct_send && $user->real_zip_code && $user->azerpost_office) {
//                            $message = ' WRONG USER AZERPOST ! ' . strtoupper($user->azerpost_office->name) . ' Send to Kobia';
//                            return response()->json([
//                                'error' => $message,
//                            ]);
//                        }
//                    }
//                    $package->status = 2;
//                    $package->save();
//                    $notification = true;
//                    Notification::sendPackage($package->id, 2);
//                }
//            }
//            if (!$package->scanned_at) {
//                $notification = true;
//                $package->scanned_at = Carbon::now();
//                $package->save();
//                if ($package->parcel && $package->parcel->count()) {
//                    $parcel = $package->parcel->first();
//                    if (!$parcel->first_scanned_at)
//                        $parcel->first_scanned_at = $package->scanned_at;
//                    $parcel->scanned_cnt++;
//                    $parcel->save();
//                }
//                if ($package->bag && $package->bag->count()) {
//                    $bag = $package->bag->first();
//                    if (!$bag->first_scanned_at)
//                        $bag->first_scanned_at = $package->scanned_at;
//                    $bag->scanned_cnt++;
//                    $bag->save();
//                }
//            }
//            if (app('laratrust')->can('update-cells') /*&& !$package->cell*/) {
//                //Percint filial (store_status) is equal to admin's filial then it arrived filial and must be accepted
//                if ($package->store_status && $package->store_status == $admin->store_status) {
//                    $precintContainerCheck = PrecinctPackage::where('barcode',$package->custom_id)->first();
//                    //if it is not sended by kobia workers can not scan
//                    if ($precintContainerCheck && $precintContainerCheck->status == PrecinctPackage::STATUSES['NOT_SENT']){
//                        return response()->json([
//                            'error' => 'Baglama gonderilib statusunda deyil',
//                        ]);
//                    }
//                    if ($admin->role_id != 1) {
//                        $pService = new PrecinctService();
//                        $pService->acceptPackage($package->custom_id);
//                        if ($package->delivery_point) {
//                            $package->bot_comment = "Received at " . $package->delivery_point->description;
//                            $package->save();
//                        }
//                    }
//                }
//                //---------
//                $message = NULL;
//                $cd = $package->courier_delivery;
//                if ($cd && $cd->courier && $admin->store_status == 2) {
//                    $message = ' Package KURYER: ' . $cd->courier->name;
//                    return response()->json([
//                        'success' => $message,
//                    ]);
//                } else {
//                    return response()->json([
//                        'redirect' => route('cells.edit', $package->id),
//                    ]);
//                }
//            } else {
//                /*if ($package->azerpoct_send) {
//                    return response()->json([
//                        'success' => "This package has to be send to Azerpost. City: " . $user->city_name . ", Postal: " . strtoupper($user->zip_code),
//                    ]);
//	            }*/
//
//                return response()->json([
//                    'redirect' => route('packages.index', ['q' => $package->custom_id]),
//                ]);
//                //return response()->json([
//                //    'success' => $notification ? 'Notification was sent' : 'You have already scanned this package :-)',
//                //]);
//            }
//        }
//
//        if ($track) {
//            if($admin->check_declaration){
//                $track->bot_comment = "Saxlanc hesabı tərəfindən scan edildi.";
//                $track->save();
//                if($track->carrier && !$track->carrier->status && !$track->carrier->depesH_NUMBER){
//                    return response()->json([
//                        'error' => 'NO DECLARATION IN CUSTOMS '. $track->tracking_code,
//                    ]);
//
//                }else{
//
//                    return response()->json([
//                        'success' => 'DECLARED IN CUSTOMS '. $track->tracking_code,
//                    ]);
//                }
//
//            }
//
////            if($track->tracking_code = 'TEST1232142'){
////                $track->status = 20;
////                $track->save();
////            };
////            if (!in_array($track->store_status, [1,3,4,7,8]) && $track->paid == 0){
////                $message = 'PACKAGE NOT PAID !';
////                return response()->json([
////                    'error' => $message,
////                ]);
////            }
//
//            $add_message = "";
//            if ($track->container_id)
//                $add_message .= " \nMAWB: " . $track->container_name;
//            if ($scanPath == 'tracks') {
//                return response()->json([
//                    'redirect' => route('tracks.index', ['q' => $track->tracking_code]),
//                ]);
//            }
//            if (!app('laratrust')->can('update-cells') && app('laratrust')->can('update-paids')) {
//                return response()->json([
//                    'redirect' => route('paids.index', ['cwb' => $track->tracking_code]),
//                ]);
//            }
//            if (!$track->scanned_at) {
//                $track->scanned_at = Carbon::now();
//                $track->save();
//                if ($track->container_id) {
//                    $container = Container::find($track->container_id);
//                    if ($container && $container->first_scanned_at == null){
//                        $container->first_scanned_at = $track->scanned_at;
//                        $container->status = 16;
//                    }
//                    $container->scanned_cnt++;
//                    $container->save();
//                }
//
//            }
//
//            if ($admin->store_status == 2 && in_array($track->partner_id, [3, 8, 9]) && !$track->scan_no_check) { //InKOBIA admin GFS & Ozon check
//                if ($track->in_customs_status) {
//                    if ($track->status != 18 && (!$admin->scan_check_only || !$admin->scan_no_alerts)) {
//                        $track->status = 18;
//                        $track->bot_comment = "Scanned but Different price";
//                        $track->save();
//                        (new PackageService())->updateStatus($track, 18);
//                        Notification::sendTrack($track->id, 'track_scan_diff_price');
//                    }
//                    if (!$admin->scan_no_alerts) {
//                        $message = "DIFFERENT PRICE" . $add_message;
//                        return response()->json([
//                            'error' => $message,
//                        ]);
//                    }
//                }
//                if (!$track->carrier) {
//                    if ($track->status != 18 && (!$admin->scan_check_only || !$admin->scan_no_alerts)) {
//                        $track->status = 18;
//                        $track->bot_comment = "Scanned but not IN  Customs";
//                        $track->save();
//                        (new PackageService())->updateStatus($track, 18);
//                        Notification::sendTrack($track->id, $track->status);
//                    }
//
//                    if (!$admin->scan_no_alerts) {
//                        $message = "NOT IN CUSTOMS" . $add_message;
//                        return response()->json([
//                            'error' => $message,
//                        ]);
//                    }
//                }
//                if ($track->carrier && !$track->carrier->status && !$track->carrier->depesH_NUMBER) {
//                    if ($track->status != 18 && (!$admin->scan_check_only || !$admin->scan_no_alerts)) {
//                        $track->status = 18;
//                        $track->bot_comment = "Scanned but no declaration in Customs";
//                        $track->save();
//                        (new PackageService())->updateStatus($track, 18);
//                        Notification::sendTrack($track->id, 'track_scan_no_dec');
//                    }
//
//                    if (!$admin->scan_no_alerts) {
//                        $message = "NO DECLARATION IN CUSTOMS" . $add_message;
//                        return response()->json([
//                            'error' => $message,
//                        ]);
//                    }
//                }
//                if ($track->carrier && !$track->carrier->depesH_NUMBER) {
//                    if ($track->status != 18 && (!$admin->scan_check_only || !$admin->scan_no_alerts)) {
//                        $track->status = 18;
//                        $track->bot_comment = "Scanned but no Depesh in Customs";
//                        $track->save();
//                        (new PackageService())->updateStatus($track, 18);
//                        //Notification::sendTrack($track->id, $track->status);
//                    }
//
//                    if (!$admin->scan_no_alerts) {
//                        $message = "NO DEPESH IN CUSTOMS" . $add_message;
//                        return response()->json([
//                            'warning' => $message,
//                        ]);
//                    }
//                }
//                /*if(!$track->carrier->depesH_NUMBER) {
//                    $message="NO DEPESH IN CUSTOMS";
//                            return response()->json([
//                                'error' => $message,
//                            ]);
//                }*/
//            }
//            if ($admin->scan_check_only && !$admin->scan_no_alerts) {
//                $message = $track->partner_with_label . ' Track ' . $track->worker_comments . $add_message;
//                $cd = $track->courier_delivery;
//                if ($cd && $cd->courier /*&& $admin->store_status == 2*/) {
//                    $message .= ' KURYER: ' . $cd->courier->name . $add_message;
//                    $message .= ' ....';
//                    //$cd->status = 2;
//                    //$cd->save();
//                }
//                return response()->json([
//                    'success' => $message,
//                ]);
//            }
//
//            $notification = false;
//            $status = $track->status;
//            if ($admin->store_status == 2 && !($track->store_status == 2 && in_array($track->partner_id, [3, 8, 9]))) { //In Kobia
//                if (!($track->partner_id == 3 && in_array($status, [ 28]))) {
//                    if ($status <= 16 || in_array($status, [18, 21, 22, 23, 25, 44])) {
//                        $track->status = 20;
//                        $notification = true;
//                    }
//                }
//            } else { // In Baku
//                //check for wrong filial
//                if (($track->store_status && $track->delivery_point) && ($track->store_status != $admin->store_status)) {
//                    $message = ' WRONG TRACK FILIAL ! ' . $track->delivery_point->description . ' Send to Kobia';
//                    return response()->json([
//                        'error' => $message,
//                    ]);
//                }
//                if ($track->azeri_express_office_id && $track->azeri_express_office) {
//                    $message = ' WRONG TRACK AZERI EXPRESS ! ' . $track->azeri_express_office->description . ' Send to Kobia';
//                    return response()->json([
//                        'error' => $message,
//                    ]);
//                }
//                if ($track->surat_office_id && $track->surat_office) {
//                    $message = ' WRONG TRACK SURAT CARGO ! ' . $track->surat_office->description . ' Send to Kobia';
//                    return response()->json([
//                        'error' => $message,
//                    ]);
//                }
//                if ($track->yenipoct_office_id && $track->yenipoct_office) {
//                    $message = ' WRONG TRACK YENIPOCT CARGO ! ' . $track->yenipoct_office->description . ' Send to Kobia';
//                    return response()->json([
//                        'error' => $message,
//                    ]);
//                }
//                if ($track->kargomat_office_id && $track->kargomat_office) {
//                    $message = ' WRONG TRACK KARGOMAT CARGO ! ' . $track->kargomat_office->description . ' Send to Kobia';
//                    return response()->json([
//                        'error' => $message,
//                    ]);
//                }
//                if ($track->azerpost_office_id && $track->azerpost_office) {
//                    $message = ' WRONG TRACK AZERPOST ! ' . strtoupper($track->azerpost_office->name) . ' Send to Kobia';
//                    return response()->json([
//                        'error' => $message,
//                    ]);
//                }
//                //-----------
//                if (!($track->partner_id == 3 && in_array($status, [28]))) {
//                    if ($status < 16 || in_array($status, [18, 20, 21, 22, 23, 25, 44])) {
//                        $track->status = 16;
//                        $notification = true;
//                    }
//                }
//            }
//            $track->comment_txt = $track->comment_txt . '|' . "Scanned: " . now() . ', ' . $status . '-' . $track->status;
//            $track->save();
//            if ($status != $track->status) {
//                (new PackageService())->updateStatus($track, $track->status);
//            }
//            if ($admin->store_status == 2 && $track->status == 20 && in_array($track->partner_id, [9]) && !$track->paid) { //If IN Kobia and TAOBAO and not PAID
//                Notification::sendTrack($track->id, $track->status);
//                $message = " TAOBAO NOT PAID." . $add_message;
//                $track->bot_comment = "Scanned In Kobia but TAOBAO Not Paid " . now();
//                $track->save();
//                return response()->json([
//                    'redirect' => route('cells.edit', ['id' => $track->id, 'track' => 1]),
////                    'warning' => $message,
//                ]);
//            }
//            if (app('laratrust')->can('update-cells') /*&& !$track->cell*/) {
//                if ($track->status == 16 || $track->status == 20) { //In Store or In Baku
//                    $wcomm = $track->worker_comments;
//                    $message = NULL;
//                    $cd = null;
//                    if ((
//                            ($track->courier_delivery && !isOfficeWord($wcomm))
//                            || ($wcomm && !empty($wcomm) && !isOfficeWord($wcomm) && !in_array($track->partner_id, [8]))
//                        ) && $admin->store_status == 2) {
//                        $message = $track->partner_with_label . ' Track ' . $track->worker_comments . $add_message;
//                        $cd = $track->courier_delivery;
//                        if ($cd && $cd->courier && $admin->store_status == 2) {
//                            $message .= ' KURYER: ' . $cd->courier->name . $add_message;
//                            //if ($cd->courier->name == 'Azeriexpress')
//                            //    $cd->status = 3;
//                            //else
//                            $cd->status = 2;
//                            $cd->save();
//
//                        }
//                    }
//                    if (!$cd || !$cd->courier) { // If no courier assigned send notification
//                        if (!$track->notification_inbaku_at) {
//                            $track->notification_inbaku_at = Carbon::now();
//                            $track->save();
//                        }
//                        if ($notification) {
//                            if ($track->partner_id != 5 && $track->partner_id != 6 /*&& $track->city_id != 3 && $track->city_id != 6*/) {
//                                if ($track->status == 16) { // In Baku
//                                    $isPudo = false;
//                                    //if($track->delivery_type != 'HD' && ($track->store_status || $track->azeriexpress_office_id || $track->azerpost_office_id || $track->surat_office_id))
//                                    if ($track->store_status || $track->azeriexpress_office_id || $track->azerpost_office_id || $track->surat_office_id)
//                                        $isPudo = true;
//                                    if (!in_array($track->partner_id, [8]) || $isPudo) { //If GFS then must be PUDO
//                                        Notification::sendTrack($track->id, $track->status);
//                                    }
//                                } else { //In Kobia
//                                    if (!in_array($track->partner_id, [9]) || !$track->paid) { //If TAOBAO then must not be PAID
//                                        Notification::sendTrack($track->id, $track->status);
//                                    }
//                                }
//                            }
//                        }
//                    } //-----
//                    //Percint filial (store_status) is equal to admin's filial then it arrived filial and must be accepted
//                    if ($track->store_status && $track->store_status == $admin->store_status) {
//                        $precintContainerCheck = PrecinctPackage::where('barcode',$track->tracking_code)->first();
//                        //if it is not sended by kobia workers can not scan
//                        if ($precintContainerCheck && $precintContainerCheck->status == PrecinctPackage::STATUSES['NOT_SENT']){
//                            return response()->json([
//                                'error' => 'Baglama gonderilib statusunda deyil',
//                            ]);
//                        }
//                        if ($admin->role_id != 1) {
//                            $pService = new PrecinctService();
//                            $pService->acceptPackage($track->tracking_code);
//                            if ($track->delivery_point) {
//                                $track->bot_comment = "Received at " . $track->delivery_point->description;
//                                $track->save();
//                            }
//                        }
//                    }
//                    //--------
//                    if ($message) { //If message assigned just show message
//                        return response()->json([
//                            'success' => $message,
//                        ]);
//                    } else { // if message is not assigned go to edit cell
//                        return response()->json([
//                            'redirect' => route('cells.edit', ['id' => $track->id, 'track' => 1]),
//                        ]);
//                    }//-----
//                } else {
//                    return response()->json([
//                        'success' => $track->partner_with_label . ' Track status: ' . config('ase.attributes.track.statusShort')[$track->status] . $add_message.' DeliveryAT: '.$track->scanned_at,
//                    ]);
//                }
//            } else {
//                return response()->json([
//                    'redirect' => route('tracks.index', ['q' => $track->tracking_code]),
//                ]);
//                //return response()->json([
//                //    'success' => $notification ? 'Notification was sent' : 'You have already scanned this track :-)',
//                //]);
//            }
//        }
//
//        return response()->json([
//            'error' => 'Package does not exist!',
//        ]);
//    }





    public function barcodeScan($code = null)
    {

        if (!$code) {
            return response()->json([
                'error' => 'Empty barcode. Please scan a package!',
            ]);
        }

        if ($code == 'courier-page') {
            return response()->json([
                'redirect' => route('courier.shelf.add.product'),
            ]);
        }

        $scanPath = '';
        if (\Request::has('path'))
            $scanPath = \Request::get('path');
        // Check barcode
        $cell = findCell($code);
        if (!empty($cell)) {
            return response()->json([
                'cell' => $cell
            ]);
        }

        $admin = Auth::user();

        $track = Track::query()->where('tracking_code', $code)->first();
        $package = null;
        if (!$track) {
            $package = Package::whereTrackingCode($code)->orWhere('custom_id', $code)->first();
        }

//        if (isset($track) && in_array($track->status, [19, 27])) {
//            $track->scanned_at = Carbon::now();
//            $track->save();
//            //(new PackageService())->updateStatus($track, 19);
//            return response()->json([
//                'error' => 'Rejected statusunda olan bağlama',
//            ]);
//        }

        if (isset($track) && in_array($track->status, [45])) {
            $track->scanned_at = Carbon::now();
            $track->save();
            (new PackageService())->updateStatus($track, 44);
            return response()->json([
                'error' => 'Saxlanc statusunda olan bağlama',
            ]);
        }

        if ($package) {

            if($admin->check_declaration){

                $package->bot_comment = "Saxlanc hesabı tərəfindən scan edildi.";
                $package->save();
                if(!$package->is_in_customs){
                    return response()->json([
                        'error' => 'NO DECLARATION IN CUSTOMS '. $package->custom_id,
                    ]);

                }else{

                    return response()->json([
                        'success' => 'DECLARED IN CUSTOMS '. $package->custom_id,
                    ]);
                }

            }

            $user = $package->user;
            if (!app('laratrust')->can('update-cells') && app('laratrust')->can('update-paids')) {
                return response()->json([
                    'redirect' => route('paids.index', ['cwb' => $package->custom_id]),
                ]);
            }
//            if ($admin->scan_check_only && !$admin->scan_no_alerts) {
//                $message = '';
//                if($package->debt_price && $package->paid_debt == 0){
//                    $message = "Baglamanin saxlanc odenisi var($package->debt_price) ve ÖDƏNİlMƏYİB!";
//                }else{
//                    $message = "Bağlamanın saxlanc ödənişi var($package->debt_price) ve ödənilib!";
//                }
//
//                return response()->json([
//                    'error' => $message,
//                ]);
//            }
            $status = $package->status;

//            if (!in_array($package->store_status, [1,3,4,7,8]) && $package->paid == 0){
//                $message = 'PACKAGE NOT PAID !';
//                return response()->json([
//                    'error' => $message,
//                ]);
//            }
            $notification = false;
            /* Send Notification */
            if ($admin->store_status == 2) { //In Kobia
                if ($status != 8) {
                    $package->status = 8;
                    $package->save();
                    $notification = true;
                    //Send notification only if user selected kobia filial
                    if ($user && !$user->real_azeri_express_use && !$user->real_azerpoct_send && !$user->real_yenipoct_use && !$user->real_kargomat_use && ($user->real_store_status == $admin->store_status) && $user->delivery_point) {
                        Notification::sendPackage($package->id, 8);
                    }
                }
            } else { // In Baku
                if ($status != 2) {
                    if (($package->store_status && $package->delivery_point)
                        || ($package->azeri_express_office_id && $package->azeri_express_office)
                        || ($package->surat_office_id && $package->surat_office)
                        || ($package->yenipoct_office_id && $package->yenipoct_office)
                        || ($package->kargomat_office_id && $package->kargomat_office)
                        || ($package->azerpost_office_id && $package->azerpost_office)) {
                        if (($package->store_status && $package->delivery_point) && ($package->store_status != $admin->store_status)) {
                            $message = ' WRONG PACKAGE FILIAL ! ' . $package->delivery_point->description . ' Send to Kobia';
                            return response()->json([
                                'error' => $message,
                            ]);
                        }
                        if ($package->azeri_express_office_id && $package->azeri_express_office) {
                            $message = ' WRONG PACKAGE AZERI EXPRESS ! ' . $package->azeri_express_office->description . ' Send to Kobia';
                            return response()->json([
                                'error' => $message,
                            ]);
                        }
                        if ($package->yenipoct_office_id && $package->yenipoct_office) {
                            $message = ' WRONG PACKAGE YENI POCT ! ' . $package->yenipoct_office->description . ' Send to Kobia';
                            return response()->json([
                                'error' => $message,
                            ]);
                        }
                        if ($package->kargomat_office_id && $package->kargomat_office) {
                            $message = ' WRONG PACKAGE Kargomat ! ' . $package->kargomat_office->description . ' Send to Kobia';
                            return response()->json([
                                'error' => $message,
                            ]);
                        }
                        if ($package->surat_office_id && $package->surat_office) {
                            $message = ' WRONG PACKAGE SURAT CARGO ! ' . $package->surat_office->description . ' Send to Kobia';
                            return response()->json([
                                'error' => $message,
                            ]);
                        }
                        if ($package->azerpost_office_id && $package->azerpost_office) {
                            $message = ' WRONG USER AZERPOST ! ' . strtoupper($package->azerpost_office->name) . ' Send to Kobia';
                            return response()->json([
                                'error' => $message,
                            ]);
                        }
                    } else {
                        if ($user && !$user->real_azeri_express_use && !$user->real_azerpoct_send && ($user->real_store_status != $admin->store_status) && $user->delivery_point) {
                            $message = ' WRONG UER FILIAL ! ' . $user->delivery_point->description . ' Send to Kobia';
                            return response()->json([
                                'error' => $message,
                            ]);
                        }
                        if ($user && $user->real_azeri_express_use && $user->real_azeri_express_office_id && $user->azeri_express_office) {
                            $message = ' WRONG USER AZERI EXPRESS ! ' . $user->azeri_express_office->description . ' Send to Kobia';
                            return response()->json([
                                'error' => $message,
                            ]);
                        }
                        if ($user && $user->real_surat_use && $user->real_surat_office_id && $user->surat_office) {
                            $message = ' WRONG USER SURAT CARGO ! ' . $user->surat_office->description . ' Send to Kobia';
                            return response()->json([
                                'error' => $message,
                            ]);
                        }
                        if ($user && $user->real_yenipoct_use && $user->real_yenipoct_office_id && $user->yenipoct_office) {
                            $message = ' WRONG USER YENIPOCT CARGO ! ' . $user->yenipoct_office->description . ' Send to Kobia';
                            return response()->json([
                                'error' => $message,
                            ]);
                        }
                        if ($user && $user->real_kargomat_use && $user->real_kargomat_office_id && $user->kargomat_office) {
                            $message = ' WRONG USER Kargomat CARGO ! ' . $user->kargomat_office->description . ' Send to Kobia';
                            return response()->json([
                                'error' => $message,
                            ]);
                        }
                        if ($user && $user->real_azerpoct_send && $user->real_zip_code && $user->azerpost_office) {
                            $message = ' WRONG USER AZERPOST ! ' . strtoupper($user->azerpost_office->name) . ' Send to Kobia';
                            return response()->json([
                                'error' => $message,
                            ]);
                        }
                    }
                    $package->status = 2;
                    $package->save();
                    $notification = true;
                    Notification::sendPackage($package->id, 2);
                }
            }
            if (!$package->scanned_at) {
                $notification = true;
                $package->scanned_at = Carbon::now();
                $package->save();
                if ($package->parcel && $package->parcel->count()) {
                    $parcel = $package->parcel->first();
                    if (!$parcel->first_scanned_at)
                        $parcel->first_scanned_at = $package->scanned_at;
                    $parcel->scanned_cnt++;
                    $parcel->save();
                }
                if ($package->bag && $package->bag->count()) {
                    $bag = $package->bag->first();
                    if (!$bag->first_scanned_at)
                        $bag->first_scanned_at = $package->scanned_at;
                    $bag->scanned_cnt++;
                    $bag->save();
                }
            }
            if (app('laratrust')->can('update-cells') /*&& !$package->cell*/) {
                //Percint filial (store_status) is equal to admin's filial then it arrived filial and must be accepted
                if ($package->store_status && $package->store_status == $admin->store_status) {
                    $precintContainerCheck = PrecinctPackage::where('barcode',$package->custom_id)->first();
                    //if it is not sended by kobia workers can not scan
                    if ($precintContainerCheck && $precintContainerCheck->status == PrecinctPackage::STATUSES['NOT_SENT']){
                        return response()->json([
                            'error' => 'Baglama gonderilib statusunda deyil',
                        ]);
                    }
                    if ($admin->role_id != 1) {
                        $pService = new PrecinctService();
                        $pService->acceptPackage($package->custom_id);
                        if ($package->delivery_point) {
                            $package->bot_comment = "Received at " . $package->delivery_point->description;
                            $package->save();
                        }
                    }
                }
                //---------
                $message = NULL;
                $cd = $package->courier_delivery;
                if ($cd && $cd->courier && $admin->store_status == 2) {
                    $message = ' Package KURYER: ' . $cd->courier->name;
                    return response()->json([
                        'success' => $message,
                    ]);
                } else {
                    return response()->json([
                        'redirect' => route('cells.edit', $package->id),
                    ]);
                }
            } else {
                /*if ($package->azerpoct_send) {
                    return response()->json([
                        'success' => "This package has to be send to Azerpost. City: " . $user->city_name . ", Postal: " . strtoupper($user->zip_code),
                    ]);
	            }*/

                return response()->json([
                    'redirect' => route('packages.index', ['q' => $package->custom_id]),
                ]);
                //return response()->json([
                //    'success' => $notification ? 'Notification was sent' : 'You have already scanned this package :-)',
                //]);
            }
        }

        if ($track) {
            if($admin->check_declaration){
                $track->bot_comment = "Saxlanc hesabı tərəfindən scan edildi.";
                $track->save();
                if($track->carrier && !$track->carrier->status && !$track->carrier->depesH_NUMBER){
                    return response()->json([
                        'error' => 'NO DECLARATION IN CUSTOMS.. '. $track->tracking_code,
                    ]);

                }else{

                    return response()->json([
                        'success' => 'DECLARED IN CUSTOMS '. $track->tracking_code.' MAWB : '.$track->container_name,
                    ]);
                }

            }

//            if($track->tracking_code = 'TEST1232142'){
//                $track->status = 20;
//                $track->save();
//            };
//            if (!in_array($track->store_status, [1,3,4,7,8]) && $track->paid == 0){
//                $message = 'PACKAGE NOT PAID !';
//                return response()->json([
//                    'error' => $message,
//                ]);
//            }

            $add_message = "";
            if ($track->container_id)
                $add_message .= " \nMAWB: " . $track->container_name;
            if ($scanPath == 'tracks') {
                return response()->json([
                    'redirect' => route('tracks.index', ['q' => $track->tracking_code]),
                ]);
            }
            if (!app('laratrust')->can('update-cells') && app('laratrust')->can('update-paids')) {
                return response()->json([
                    'redirect' => route('paids.index', ['cwb' => $track->tracking_code]),
                ]);
            }
            if (!$track->scanned_at) {
                $track->scanned_at = Carbon::now();
                $track->save();
                if ($track->container_id) {
                    $container = Container::find($track->container_id);
                    if ($container && $container->first_scanned_at == null){
                        $container->first_scanned_at = $track->scanned_at;
                        $container->status = 16;
                    }
                    $container->scanned_cnt++;
                    $container->save();
                }

            }

            if ($admin->store_status == 2 && in_array($track->partner_id, [3, 8, 9]) && !$track->scan_no_check) { //InKOBIA admin GFS & Ozon check
                if ($track->in_customs_status) {
                    if ($track->status != 18 && (!$admin->scan_check_only || !$admin->scan_no_alerts)) {
                        $track->status = 18;
                        $track->bot_comment = "Scanned but Different price";
                        $track->save();
                        (new PackageService())->updateStatus($track, 18);
                        Notification::sendTrack($track->id, 'track_scan_diff_price');
                    }
                    if (!$admin->scan_no_alerts) {
                        $message = "DIFFERENT PRICE" . $add_message;
                        return response()->json([
                            'error' => $message,
                        ]);
                    }
                }
                if (!$track->carrier) {
                    if ($track->status != 18 && (!$admin->scan_check_only || !$admin->scan_no_alerts)) {
                        $track->status = 18;
                        $track->bot_comment = "Scanned but not IN  Customs";
                        $track->save();
                        (new PackageService())->updateStatus($track, 18);
                        Notification::sendTrack($track->id, $track->status);
                    }

                    if (!$admin->scan_no_alerts) {
                        $message = "NOT IN CUSTOMS" . $add_message;
                        return response()->json([
                            'error' => $message,
                        ]);
                    }
                }
                if ($track->carrier && !$track->carrier->status && !$track->carrier->depesH_NUMBER) {
                    if ($track->status != 18 && (!$admin->scan_check_only || !$admin->scan_no_alerts)) {
                        $track->status = 18;
                        $track->bot_comment = "Scanned but no declaration in Customs";
                        $track->save();
                        (new PackageService())->updateStatus($track, 18);
                        Notification::sendTrack($track->id, 'track_scan_no_dec');
                    }

                    if (!$admin->scan_no_alerts) {
                        $message = "NO DECLARATION IN CUSTOMS!" . $add_message;
                        return response()->json([
                            'error' => $message,
                        ]);
                    }
                }
                if ($track->carrier && !$track->carrier->depesH_NUMBER) {
                    if ($track->status != 18 && (!$admin->scan_check_only || !$admin->scan_no_alerts)) {
                        $track->status = 18;
                        $track->bot_comment = "Scanned but no Depesh in Customs";
                        $track->save();
                        (new PackageService())->updateStatus($track, 18);
                        //Notification::sendTrack($track->id, $track->status);
                    }

                    if (!$admin->scan_no_alerts) {
                        $message = "NO DEPESH IN CUSTOMS" . $add_message;
                        return response()->json([
                            'warning' => $message,
                        ]);
                    }
                }
                /*if(!$track->carrier->depesH_NUMBER) {
                    $message="NO DEPESH IN CUSTOMS";
                            return response()->json([
                                'error' => $message,
                            ]);
                }*/
            }
            if ($admin->scan_check_only && !$admin->scan_no_alerts) {
                $message = $track->partner_with_label . ' Track ' . $track->worker_comments . $add_message;
                $cd = $track->courier_delivery;
                if ($cd && $cd->courier /*&& $admin->store_status == 2*/) {
                    $message .= ' KURYER: ' . $cd->courier->name . $add_message;
                    $message .= ' ....';
                    //$cd->status = 2;
                    //$cd->save();
                }
                return response()->json([
                    'success' => $message,
                ]);
            }

            $notification = false;
            $status = $track->status;
            if ($admin->store_status == 2 && !($track->store_status == 2 && in_array($track->partner_id, [3, 8, 9]))) { //In Kobia
                if (!($track->partner_id == 3 && in_array($status, [28]))) {
                    if ($status <= 16 || in_array($status, [18, 21, 22, 23, 25, 44])) {
                        $track->status = 20;
                        $notification = true;
                    }
                }
            } else { // In Baku
                //check for wrong filial
                if (($track->store_status && $track->delivery_point) && ($track->store_status != $admin->store_status)) {
                    $message = ' WRONG TRACK FILIAL ! ' . $track->delivery_point->description . ' Send to Kobia';
                    return response()->json([
                        'error' => $message,
                    ]);
                }
                if ($track->azeri_express_office_id && $track->azeri_express_office) {
                    $message = ' WRONG TRACK AZERI EXPRESS ! ' . $track->azeri_express_office->description . ' Send to Kobia';
                    return response()->json([
                        'error' => $message,
                    ]);
                }
                if ($track->surat_office_id && $track->surat_office) {
                    $message = ' WRONG TRACK SURAT CARGO ! ' . $track->surat_office->description . ' Send to Kobia';
                    return response()->json([
                        'error' => $message,
                    ]);
                }
                if ($track->yenipoct_office_id && $track->yenipoct_office) {
                    $message = ' WRONG TRACK YENIPOCT CARGO ! ' . $track->yenipoct_office->description . ' Send to Kobia';
                    return response()->json([
                        'error' => $message,
                    ]);
                }
                if ($track->kargomat_office_id && $track->kargomat_office) {
                    $message = ' WRONG TRACK KARGOMAT CARGO ! ' . $track->kargomat_office->description . ' Send to Kobia';
                    return response()->json([
                        'error' => $message,
                    ]);
                }
                if ($track->azerpost_office_id && $track->azerpost_office) {
                    $message = ' WRONG TRACK AZERPOST ! ' . strtoupper($track->azerpost_office->name) . ' Send to Kobia';
                    return response()->json([
                        'error' => $message,
                    ]);
                }
                //-----------
                if (!($track->partner_id == 3 && in_array($status, [28]))) {
                    if ($status < 16 || in_array($status, [18, 20, 21, 22, 23, 25, 44])) {
                        $track->status = 16;
                        $notification = true;
                    }
                }
            }
            $track->comment_txt = $track->comment_txt . '|' . "Scanned: " . now() . ', ' . $status . '-' . $track->status;
            $track->save();
            if ($status != $track->status) {
                (new PackageService())->updateStatus($track, $track->status);
            }
            if ($admin->store_status == 2 && $track->status == 20 && in_array($track->partner_id, [9]) && !$track->paid) { //If IN Kobia and TAOBAO and not PAID
                Notification::sendTrack($track->id, $track->status);
                $message = " TAOBAO NOT PAID." . $add_message;
                $track->bot_comment = "Scanned In Kobia but TAOBAO Not Paid " . now();
                $track->save();
                return response()->json([
                    'redirect' => route('cells.edit', ['id' => $track->id, 'track' => 1]),
//                    'warning' => $message,
                ]);
            }
            if (app('laratrust')->can('update-cells') /*&& !$track->cell*/) {
                if ($track->status == 16 || $track->status == 20 || $track->status == 19 || $track->status == 27) { //In Store or In Baku
                    $wcomm = $track->worker_comments;
                    $message = NULL;
                    $cd = null;
                    if ((
                            ($track->courier_delivery && !isOfficeWord($wcomm))
                            || ($wcomm && !empty($wcomm) && !isOfficeWord($wcomm) && !in_array($track->partner_id, [8]))
                        ) && $admin->store_status == 2) {
                        $message = $track->partner_with_label . ' Track ' . $track->worker_comments . $add_message;
                        $cd = $track->courier_delivery;
                        if ($cd && $cd->courier && $admin->store_status == 2) {
                            $message .= ' KURYER: ' . $cd->courier->name . $add_message;
                            //if ($cd->courier->name == 'Azeriexpress')
                            //    $cd->status = 3;
                            //else
                            $cd->status = 2;
                            $cd->save();

                        }
                    }
                    if (!$cd || !$cd->courier) { // If no courier assigned send notification
                        if (!$track->notification_inbaku_at) {
                            $track->notification_inbaku_at = Carbon::now();
                            $track->save();
                        }
                        if ($notification) {
                            if ($track->partner_id != 5 && $track->partner_id != 6 /*&& $track->city_id != 3 && $track->city_id != 6*/) {
                                if ($track->status == 16) { // In Baku
                                    $isPudo = false;
                                    //if($track->delivery_type != 'HD' && ($track->store_status || $track->azeriexpress_office_id || $track->azerpost_office_id || $track->surat_office_id))
                                    if ($track->store_status || $track->azeriexpress_office_id || $track->azerpost_office_id || $track->surat_office_id)
                                        $isPudo = true;
                                    if (!in_array($track->partner_id, [8]) || $isPudo) { //If GFS then must be PUDO
                                        Notification::sendTrack($track->id, $track->status);
                                    }
                                } else { //In Kobia
                                    if (!in_array($track->partner_id, [9]) || !$track->paid) { //If TAOBAO then must not be PAID
                                        Notification::sendTrack($track->id, $track->status);
                                    }
                                }
                            }
                        }
                    } //-----
                    //Percint filial (store_status) is equal to admin's filial then it arrived filial and must be accepted
                    if ($track->store_status && $track->store_status == $admin->store_status) {
                        $precintContainerCheck = PrecinctPackage::where('barcode',$track->tracking_code)->first();
                        //if it is not sended by kobia workers can not scan
                        if ($precintContainerCheck && $precintContainerCheck->status == PrecinctPackage::STATUSES['NOT_SENT']){
                            return response()->json([
                                'error' => 'Baglama gonderilib statusunda deyil',
                            ]);
                        }
                        if ($admin->role_id != 1) {
                            $pService = new PrecinctService();
                            $pService->acceptPackage($track->tracking_code);
                            if ($track->delivery_point) {
                                $track->bot_comment = "Received at " . $track->delivery_point->description;
                                $track->save();
                            }
                        }
                    }
                    //--------
                    if ($message) { //If message assigned just show message
                        return response()->json([
                            'success' => $message,
                        ]);
                    } else { // if message is not assigned go to edit cell
                        return response()->json([
                            'redirect' => route('cells.edit', ['id' => $track->id, 'track' => 1]),
                        ]);
                    }//-----
                } else {
                    return response()->json([
                        'success' => $track->partner_with_label . ' Track status: ' . config('ase.attributes.track.statusShort')[$track->status] . $add_message.' DeliveryAT: '.$track->scanned_at,
                    ]);
                }
            } else {
                return response()->json([
                    'redirect' => route('tracks.index', ['q' => $track->tracking_code]),
                ]);
                //return response()->json([
                //    'success' => $notification ? 'Notification was sent' : 'You have already scanned this track :-)',
                //]);
            }
        }

        return response()->json([
            'error' => 'Package does not exist!',
        ]);
    }

    public function multiUpdate(Request $request)
    {
        //->whereNotNull('requested_at')
        $items = Package::whereIn('id', $request->get('ids'))->where($request->get('key'), "!=", $request->get('value'))->get();
        $count = $items->count();
        if ($count) {
            $key = $request->get('key');
            foreach ($items as $item) {
                if( ($item->debt_price > 0 && $item->paid_debt == 0) || $item->paid == 0 ){
                    return \Response::json(['message' => ' Items have debt price!'],400);
                }
                $item->{$key} = $request->get('value');
                $item->save();
            }
            return \Response::json(['message' => $count . ' items has been updated!']);
        } else {
            return \Response::json(['message' => "There isn't any data to update!"], 400);
        }
    }

    public function changeStatusAllDone(Request $request)
    {
        dd($request->all());
    }


    public function debtPackageIndex(Request $request)
    {
        $query = Package::query();

        if($request->status){
            $query->where('status',$request->status);
        }

        $query->where('debt_price','>',0);


        if($request->start_date != null && $request->end_date != null){
            $query->whereHas('transactionDebt',function($q) use($request){
               $q->whereBetween('created_at',[$request->start_date . " 00:00:00",$request->end_date . " 23:59:59"]);
            });
        }

        $paid_debt = $request->paid_debt;

        if($paid_debt != null){
            if($paid_debt == 1){
                $query->whereIn('paid_debt',[1,3]);
            }elseif($paid_debt == 2){
                $query->whereIn('paid_debt',[0,2]);
            }
        }

        if ($request->tl != null) {
            $tracking_codes = preg_split("/[;:,\s]+/", trim($request->tl));
            $query->where(function ($q) use ($tracking_codes) {
                $q->whereIn('packages.tracking_code', $tracking_codes)
                    ->orWhereIn('packages.custom_id', $tracking_codes);
            });
        }

        if ($request->q != null) {
            $q = str_replace('"', '', $request->q);
            $query->where(function ($query) use ($q) {
                $query->orWhere("tracking_code", "LIKE", "%" . $q . "%")->orWhereRaw(\DB::raw('concat("E", (6005710000 + packages.id)) = "' . $q . '"'))->orWhere("website_name", "LIKE", "%" . $q . "%")->orWhere("packages.custom_id", "LIKE", "%" . $q . "%")->orWhereHas('user', function (
                    $query
                ) use ($q) {
                    $query->where('customer_id', 'LIKE', '%' . $q . '%')->orWhere('passport', 'LIKE', '%' . $q . '%')->orWhere('fin', 'LIKE', '%' . $q . '%')->orWhere('phone', 'LIKE', '%' . $q . '%')->orWhere('email', 'LIKE', '%' . $q . '%')->orWhere(DB::raw('concat(trim(name)," ",trim(surname))'), 'LIKE', "%" . $q . "%")->orWhereHas('dealer', function (
                        $query
                    ) use ($q) {
                        $query->where('customer_id', 'LIKE', '%' . $q . '%')->orWhere('passport', 'LIKE', '%' . $q . '%')->orWhere('fin', 'LIKE', '%' . $q . '%')->orWhere('phone', 'LIKE', '%' . $q . '%')->orWhere('email', 'LIKE', '%' . $q . '%')->orWhere(DB::raw('concat(trim(name)," ",trim(surname))'), 'LIKE', "%" . $q . "%");
                    });
                });
                $query->orWhere("detailed_type", "LIKE", "%" . $q . "%");
            });
        }

        if ($request->parcel != null) {
            if ($request->parcel == 'NO') {
                $query->doesntHave('parcel');
            } else {
                $query->whereHas('parcel', function ($query) {
                    $customIds = explode(',', \Request::get('parcel'));
                    $parcelIds = Parcel::whereIn('custom_id', $customIds)->pluck('id')->all();

                    $query->whereIn('parcel_id', $parcelIds ?: [0]);
                });
            }
        }

        $results = $query->get();

        $statusLabelsJson = config('ase.attributes.package.statusWithLabel');
        $fixedJson = preg_replace('/([{,]\s*)(\w+)\s*:/', '$1"$2":', $statusLabelsJson);
        $statusLabels = json_decode($fixedJson, true);

        return view('admin.debt.package')->with([
            'results' => $results,
            'request' => $request,
            'statusLabels' => $statusLabels
        ]);
    }

    public function exportDebtPackage(Request $request)
    {
        $data = explode(',', $request->get('items'));

        $items = Package::whereIn('id', $data)->get();

        return Excel::download(new DebtPackage($items), 'debt_packages_' . uniqid() . '.xlsx');
    }


//    public function panelView($blade)
//    {
//        return 'admin.packages.list';
//    }
}
