<?php

namespace App\Http\Controllers\Admin;

use App\Events\TrackCell;
use App\Exports\Admin\DebtTrack;
use App\Exports\Admin\TracksExport;
use App\Http\Requests;
use App\Models\Activity;
use App\Models\Airbox;
use App\Models\AzeriExpress\AzeriExpressOffice;
use App\Models\Azerpost\AzerpostOffice;
use App\Models\CD;
use App\Models\Courier;
use App\Models\CourierShelfLog;
use App\Models\DeliveryPoint;
use App\Models\Extra\Notification;
use App\Models\Filial;
use App\Models\Kargomat\KargomatOffice;
use App\Models\Package;
use App\Models\Surat\SuratOffice;
use App\Models\Track;
use App\Models\Transaction;
use App\Models\UnknownOffice;
use App\Models\YeniPoct\YenipoctOffice;
use App\Services\Package\PackageService;
use Auth;
use Carbon\Carbon;
use Doctrine\DBAL\Driver\Exception;
use Excel;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use View;
use Illuminate\Http\Request;
class TrackController extends Controller
{

    protected $can = [
        'export' => true,
    ];

    protected $view = [
        'name' => "Track",
        'total_sum' => [
            [
                'key' => 'weight',
                'skip' => 26,
                'add' => "kg",
            ],
            [
                'key' => 'delivery_price_gfs',
                'skip' => 0,
                'add' => "USD",
            ],
            [
                'key' => 'delivery_price_ozon',
                'skip' => 0,
                'add' => "USD",
            ],
        ],
        'sum' => [
            [
                'key' => 'weight',
                'skip' => 29,
                'add' => "kg",
            ],
            [
                'key' => 'delivery_price_gfs',
                'skip' => 0,
                'add' => "USD",
            ],
            [
                'key' => 'delivery_price_ozon',
                'skip' => 0,
                'add' => "USD",
            ],
        ],
        'search' => [
            [
                'name' => 'parcel',
                'type' => 'textarea',
                'attributes' => ['placeholder' => 'Parcel name'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
            [
                'name' => 'bag',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Bag name'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-1',
                ],
            ],
            [
                'name' => 'q',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Search...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
            [
                'name' => 'tl',
                'type' => 'textarea',
                'attributes' => ['placeholder' => 'Tracking # List...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
            [
                'name' => 'status',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.track.statusShort',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All Status',
            ],
            [
                'name' => 'dec',
                'type' => 'select_from_array',
                'options' => [1 => 'All with Done', 2 => 'Not scanned'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Filter',
            ],
            [
                'type' => 'html',
                'html' => '<div class="form-group col-lg-12 mt-10"></div>',
            ],
            [
                'name' => 'delivery_type',
                'type' => 'select_from_array',
                'options' => ['HD' => 'HD', 'PUDO' => 'PUDO'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Delivery Type',
            ],
            [
                'name' => 'nof',
                'type' => 'select_from_array',
                'options' => [1 => 'No Filial', 2 => 'Have Filial'],
                'allowNull' => 'Filial',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
            [
                'type' => 'select2_with_groups',
                'name' => 'filial_id',
                'attribute' => 'name',
                'model' => 'App\Models\Filial',
                'wrapperAttributes' => [
                    'class' => 'col-lg-4',
                ],
                'allowNull' => 'All Filials',
                'group_by' => 'type',
            ],


            [
                'name' => 'noc',
                'label' => 'No Courier',
                'type' => 'checkbox',
                'default' => 0,
                'wrapperAttributes' => [
                    'class' => 'col-lg-1',
                ],
            ],
            [
                'type' => 'select2',
                'name' => 'courier_id',
                'attribute' => 'name',
                'model' => 'App\Models\Courier',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'All couriers',
            ],
            [
                'name' => 'cd_status',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.cd.status',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All CD Status',
            ],
            [
                'name' => 'city',
                'type' => 'text',
                'attributes' => ['placeholder' => 'City'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-1',
                ],
            ],
            [
                'name' => 'paid',
                'type' => 'select_from_array',
                'options' => [1 => 'Paid', 2 => 'Not Paid',4 => 'Bonus'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-1',
                ],
                'allowNull' => 'Payment',
            ],
            [
                'type' => 'html',
                'html' => '<div class="form-group col-lg-12 mt-10"></div>',
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
                    'class' => 'col-lg-5',
                ],
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
                'name' => 'partner_id',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.track.partner',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All Partners',
            ],
            [
                'name' => 'from_country',
                'type' => 'select_from_array',
                'options' => ['TR' => 'TR', 'RU' => 'RU','CN' => 'CN'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-1',
                ],
                'allowNull' => 'All',
            ],
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
                'name' => 'tnum',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Temp num'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-1',
                ],
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
        ],

    ];

    protected $list = [
        /*'id'          => [
            'label' => '#',
        ],*/
        'partnerWithLabel' => [
            'label' => 'Partner',
        ],
        'from_country' => [
            'label' => 'From country',
        ],
        'cell' => [
            'order' => 'cell',
        ],
        'shelf_name' => [
            'label' => 'HDC',
        ],
        'container.name' => [
            'label' => 'Parcel',
        ],
        'airbox.name' => [
            'label' => 'Bag',
            'type' => 'editable',
            'editable' => [
                'route' => 'tracks.ajax',
                'type' => 'text',
            ],
            'order' => 'airboxes.name',
        ],
        'scanned_at' => [
            'label' => 'DeliveredAt',
            'order' => 'scanned_at',
        ],
        'tracking_code' => [
            'label' => 'Tracking #',
        ],
        'internal_tracking_number' => [
            'label' => 'İnternal tracking #',
        ],
        'customer' => [
            'type' => 'custom.customer_link',
            'label' => 'Customer',
        ],
        'custom_id' => [
            'label' => 'Custom ID',
            'type' => 'custom.pay_link',
        ],
        'customer.fin' => [
            'label' => 'Fin',
        ],
        'phone' => [
            'label' => 'Phone',
        ],
        'status' => [
            'label' => 'Status',
            //'type' => 'select-editable',
            'type' => 'custom.send_link',
            'editable' => [
                'route' => 'tracks.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.track.statusShortWithLabel',
            ],
            'order' => 'tracks.status',
        ],
        'paid' => [
            'label' => 'Paid',
            'type' => 'select-editable',
            'editable' => [
                'route' => 'tracks.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.track.paidWithLabel',
            ],
        ],
        'paid_by' => [
            'label' => 'Paid By',
        ],
        'paid_debt' => [
            'label' => 'Paid Debt',
            'type' => 'paid_debt',
            'editable' => [
                'route' => 'tracks.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.package.paidWithLabelDebt',
            ],
        ],
        'stop_debt' => [
            'label' => 'Stop Debt',
            'type' => 'stop_debt',
            'editable' => [
                'route' => 'tracks.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.package.stopDebt',
            ],
        ],
        'debt_price' => [
            'label' => 'Debt Price',
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
        'carrier_status_label' => [
            'label' => 'Pudo Status',
        ],
//        'azeriexpressstatus_label' => [
//            'label' => 'AzeriExpress Status',
//        ],
//        'azerpoststatus_label' => [
//            'label' => 'Azerpost Status',
//        ],
//        'precinctstatus_label' => [
//            'label' => 'Precinct Status',
//        ],
//        'suratstatus_label' => [
//            'label' => 'Surat Status',
//        ],
        'carrier' => [
            'type' => 'carrier',
            'label' => 'Customs.',
            'order' => 'package_carriers.status',
        ],
        'worker_comments' => [
            'label' => 'Comments',
            'type' => 'editable',
            'editable' => [
                'route' => 'tracks.ajax',
                'type' => 'text',
            ],
            'order' => 'worker_comments',
        ],
        'worker2_comments' => [
            'label' => 'Comments 2',
            'type' => 'editable',
            'editable' => [
                'route' => 'tracks.ajax',
                'type' => 'text',
            ],
            'order' => 'worker2_comments',
        ],
        'courier_delivery.courier_id' => [
            'label' => 'Courier',
            'type' => 'track_auto_courier',
            'editable' => [
                'route' => 'tracks.ajax',
                'type' => 'select2',
                'source' => null,
            ],
        ],
        'courier_delivery' => [
            'type' => 'cd_status',
            'label' => 'CD Status',
        ],
        'city_name' => [
            'label' => 'City',
        ],
        'region_name' => [
            'label' => 'Region',
        ],
        'address' => [
            'label' => 'Address',
            'type' => 'editable',
            'editable' => [
                'route' => 'tracks.ajax',
                'type' => 'text',
            ],
        ],
        /*        'zip_code'          => [
                    'label' => 'Zip code',
            ],*/
        /*'city.name' => [
            'label' => 'City',
    ],*/
        'delivery_type' => [
            'label' => 'Delivery Type',
        ],

        'filial_hd_name' => [
            'label' => 'HD Filial',
            'type' => 'track_auto_filial',
            'editable' => [
                'route' => 'tracks.track_filial',
                'type' => 'select2',
                'source' => null,
            ],
        ],
//        'filial.type_id_name' => [
//            'label' => 'Filial',
//        ],

        'filial_name' => [
            'label' => 'Filial',
            'type' => 'filial_change',
            'editable' => [
                'route' => 'tracks.ajax',
                'type' => 'select2',
                'sourceFromConfig' => 'filial.filials',
            ],
        ],
        'rq' => [
            'label' => 'Request',
            'type' => 'track_request',
        ],
        'courier_delivery_price' => [
            'label' => 'Courier D/Price',
            'type' => 'editable_raw',
            'editable' => [
                'key' => 'courier_delivery_price',
                'route' => 'tracks.ajax',
                'type' => 'number',
            ],
        ],
        'type' => [
            'label' => 'Order Type',
        ],
        'weight' => [
            'label' => 'Weight',
            'order' => 'tracks.weight',
        ],
        'delivery_price_gfs_with_label' => [
            'label' => 'D Price GFS',
        ],
        'delivery_price_ozon_with_label' => [
            'label' => 'D Price OZON',
        ],
        'currency' => [
            'label' => 'Currency',
        ],
        'shipping_amount_kzt_and_usd' => [
            'label' => 'Invoice price',
        ],
        'delivery_amount_kzt_and_usd' => [
            'label' => 'Delivery price',
        ],
//        'delivery_amount_usd' => [
//            'label' => 'Delivery price usd',
//        ],
        //Debt

        //Debt
        'detailed_type' => [
            'label' => 'Items Description',
        ],
        'number_items' => [
            'label' => 'Items Quantity',
        ],
        'created_at' => [
            'label' => 'CreatedAt',
            'order' => 'created_at',
        ],
    ];

    protected $fields = [
        [
            'name' => 'tracking_code',
            'label' => 'Tracking #',
            'type' => 'text',
            'validation' => 'required|string',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
        ],
        [
            'label' => 'Partner',
            'type' => 'select2',
            'name' => 'partner_id',
            'attribute' => 'name',
            'model' => 'App\Models\Partner',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|integer',
            //'allowNull'         => true,
        ],
        [
            'name' => 'website',
            'label' => 'Website',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-2',
            ],
            'validation' => 'nullable|string',
        ],

        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'name' => 'fin',
            'label' => 'FIN',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-2',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'name' => 'fullname',
            'label' => 'Full name',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-3',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'phone',
            'label' => 'Phone',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-2',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'email',
            'label' => 'Email',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-2',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'name' => 'city_name',
            'label' => 'City name',
            'type' => 'text',
            'validation' => 'nullable|string',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
        ],
        [
            'name' => 'region_name',
            'label' => 'Region name',
            'type' => 'text',
            'validation' => 'nullable|string',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],
        /*[
            'label' => 'City',
            'type' => 'select2',
            'name' => 'city_id',
            'attribute' => 'name',
            'model' => 'App\Models\City',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|integer',
            //'allowNull'         => true,
    ],*/
        [
            'name' => 'address',
            'label' => 'Address',
            'type' => 'textarea',
            'validation' => 'required|string',
            'attributes' => [
                'rows' => 3,
            ],
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
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
            'name' => 'number_items',
            'label' => 'Number Items',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|numeric',
        ],
        [
            'name' => 'shipping_amount',
            'label' => 'Invoice price',
            'type' => 'text',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|numeric',
        ],
        [
            'name' => 'delivery_price',
            'label' => 'Delivery price',
            'type' => 'text',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|numeric',
        ],

        [
            'name' => 'debt_price',
            'label' => 'Debt price',
            'type' => 'text',
            'prefix' => 'AZN',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|numeric',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'name' => 'scan_no_check',
            'label' => "Don't check customs when scaning",
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'col-md-4 mt-15',
            ],
            'validation' => 'nullable|integer',
        ],


        [
            'name' => 'detailed_type',
            'label' => 'Detailed Type',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'form-group col-md-6',
            ],
            'validation' => 'nullable|string',
        ],

        [
            'name' => 'ignore_done',
            'label' => "Ignore done",
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'col-md-12 mt-15',
            ],
            'validation' => 'nullable|integer',
        ],
    ];

    protected $notificationKey = 'tracking_code';

    protected $extraActions = [
        [
            'key' => 'id',
            'role' => 'customs-check',
            'label' => 'Customs check',
            'icon' => 'checkmark',
            'route' => 'tracks.depesh_check',
            'color' => 'info',
            'target' => '_blank',
        ],
        [
            'route' => 'tracks.carrier_update',
            'key' => 'id',
            'role' => 'customs-reset',
            'label' => 'Customs reset',
            'icon' => 'spinner11',
            'color' => 'success',
            'target' => '_blank',
        ],
        [
            'route' => 'tracks.carrier_delete',
            'key' => 'id',
            'role' => 'customs-reset',
            'label' => 'Customs delete',
            'icon' => 'bin',
            'color' => 'success',
            'target' => '_blank',
        ],
        [
            'route' => 'tracks.label',
            'key' => 'id',
            'label' => 'Label',
            'icon' => 'windows2',
            'color' => 'success',
            'target' => '_blank',
        ],
        [
            'route' => 'tracks.logs',
            'key' => 'id',
            'label' => 'Logs',
            'icon' => 'list',
            'color' => 'default',
            'target' => '_blank',
        ],
        [
            'route' => 'tracks.resend_status',
            'key' => 'id',
            'label' => 'Resend Status',
            'icon' => 'spinner11',
            'color' => 'default',
            'target' => '_blank',
        ],
    ];

    public function __construct()
    {
        $couriers = Courier::orderBy('name', 'asc')->get();
        $allCouriers = [];
        $allCouriers[] = [
            'value' => 0,
            'text' => "No"
        ];
        foreach ($couriers as $courier) {
            $allCouriers[] = [
                'value' => $courier->id,
                'text' => $courier->name,
            ];
        }

        $filials = Filial::whereIn('city_id', [1, 10])->orderBy('type', 'asc')->orderBy('name', 'asc')->get();
        $allFilials = [];
        $allFilials[] = [
            'value' => 0,
            'text' => "No"
        ];
        foreach ($filials as $filial) {
            $allFilials[] = [
                'value' => $filial->type_id,
                'text' => $filial->type_id_name . ' (' . $filial->address . ')',
            ];
        }

        $this->middleware(function ($request, $next) {
            if (optional(auth()->user()->role)->id == 10 or optional(auth()->user()->role)->id == 26 ) {
                $this->fields = array_filter($this->fields, function ($field) {
                    return !isset($field['name']) || $field['name'] !== 'debt_price';
                });
            }
            parent::__construct();
            return $next($request);
        });

        $this->list['courier_delivery.courier_id']['editable']['source'] = \GuzzleHttp\json_encode($allCouriers, true);
        $this->list['filial_hd_name']['editable']['source'] = \GuzzleHttp\json_encode($allFilials, true);
        parent::__construct();
    }

    public function importExcel()
    {
    }

    public function auto_courier($id)
    {
        $track = Track::find($id);
        if ($track) {
            if (Track::auto_courier($track)) {
                $track->save();
                return "1";
            }
        }
        return "0";
    }

    public function auto_filial($id)
    {
        $track = Track::find($id);
        if ($track) {
            $filial = Filial::getMach($track->address, $track->city_name, $track->region_name);
            if ($filial) {
                Filial::setTrackFilial($track, $filial);
                $track->save();
                return "1";
            }
        }
        return "0";
    }

    public function request($id)
    {
        $used = Track::find($id);

        if($used->debt_price > 0 && $used->paid_debt == 0){
            return "0";
        }

        if ($used) {

            $used->requested_at = Carbon::now();
            $used->save();

            event(new TrackCell('find', $used->id));

            return "1";
        }

        return "0";
    }

    public function depesh_check($id = null)
    {
        $track = Track::find($id);
        if (!$track) {
            $d_out = 'Track not found';
            return view('admin.depesh', ['d_out' => $d_out]);
        }
        if (Auth::user()->can('customs-check')) {
            Artisan::call('depesh', ['package' => 5, 'parcel_id' => $id, 'checkonly' => 1, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function carrier_delete($id)
    {
        $track = Track::find($id);
        if (!$track) {
            $out = 'Track not found';
            return view('admin.widgets.carrier_update', ['out' => $out]);
        }
        if (Auth::user()->can('customs-reset')) {
            Artisan::call('carriers_track:update', ['package' => 1, 'track_id' => $id, 'checkonly' => 0, 'htmlformat' => 1, 'deleteonly' => 1]);
            $out = Artisan::output();
        } else {
            $out = "No permissions";
        }
        return view('admin.widgets.carrier_update', ['out' => $out]);
    }

    public function resend_status($id){

        $track = Track::find($id);
        if (!$track) {
            $out = 'Track not found';
            return \Response::json(['message' => $out]);
        }
        $response = (new PackageService())->updateStatus($track, $track->status);

        return \Response::json(['message' => "Status sended successfully"]);
    }

    public function carrier_update($id)
    {
        $track = Track::find($id);
        if (!$track) {
            $out = 'Track not found';
            return view('admin.widgets.carrier_update', ['out' => $out]);
        }
        if (Auth::user()->can('customs-reset')) {
            Artisan::call('carriers_track:update', ['package' => 1, 'track_id' => $id, 'checkonly' => 0, 'htmlformat' => 1]);
            $out = Artisan::output();
        } else {
            $out = "No permissions";
        }
        return view('admin.widgets.carrier_update', ['out' => $out]);
    }

    public function indexObject()
    {
        $items = Track::with(['carrier', 'partner', 'container', 'airbox', 'city', 'courier_delivery']);//::paginate($this->limit);
        if (\request()->get('sort') != null) {
            $sortKey = explode("__", \request()->get('sort'))[0];
            $sortType = explode("__", \request()->get('sort'))[1];
            $items = $items->orderBy($sortKey, $sortType)->orderBy('tracks.id', 'desc');
        } else {
            $items = $items->orderBy('tracks.created_at', 'desc')->orderBy('tracks.id', 'desc');
        }
        $items = $items->leftJoin('containers', 'tracks.container_id', 'containers.id');
        $items = $items->leftJoin('airboxes', 'tracks.airbox_id', 'airboxes.id');
        $items = $items->leftJoin('courier_deliveries', 'tracks.courier_delivery_id', 'courier_deliveries.id');
        $items = $items->leftJoin('package_carriers', 'tracks.id', 'package_carriers.track_id');
        $items = $items->select('tracks.*');
        if (\Request::get('tnum') != null) {
            $items = $items->leftJoin('track_temp_list', 'tracks.tracking_code', 'track_temp_list.tracking_code');
            $items->where('track_temp_list.num', \Request::get('tnum'));
        }

        if (\Request::get('q') != null) {
            $q = str_replace('"', '', \Request::get('q'));
            $items->where(function ($query) use ($q) {
                $query->orWhere("tracks.tracking_code", "LIKE", "%" . $q . "%")
                    ->orWhere("tracks.fin", "LIKE", "%" . $q . "%")
                    ->orWhere("tracks.internal_tracking_number", "LIKE", "%" . $q . "%")
                    ->orWhere("tracks.fullname", "LIKE", "%" . $q . "%")
                    ->orWhere("tracks.address", "LIKE", "%" . $q . "%")
                    ->orWhere("tracks.phone", "LIKE", "%" . $q . "%")
                    ->orWhere("tracks.email", "LIKE", "%" . $q . "%")
                    ->orWhere("tracks.detailed_type", "LIKE", "%" . $q . "%");
            });
        }
        if (\Request::get('tl') != null) {
            $tracking_codes = preg_split("/[;:,\s]+/", trim(\Request::get('tl')));
            $items = $items->where(function ($query) use ($tracking_codes) {
                $query->whereIn('tracks.tracking_code', $tracking_codes)
                    ->orWhereIn('tracks.internal_tracking_number', $tracking_codes);
            });
        }

        if (\Request::get('from_country') != null) {
                $items = $items->where('tracks.from_country', \Request::get('from_country'));
        }

        if (\Request::get('noc')) {
            $items->whereRaw('(courier_deliveries.courier_id is NULL or courier_deliveries.deleted_at is not NULL)');
        } else if (\Request::get('courier_id') != null) {
            $items->whereNull('courier_deliveries.deleted_at')->where('courier_deliveries.courier_id', \Request::get('courier_id'));
        }
        if ($paid = \Request::get('paid')) {
            if ($paid == 1) {
                $items->where('tracks.paid', 1);
            } elseif ($paid == 2) {
                $items->where('tracks.paid', 0);
            } elseif ($paid == 4) {

                $items = $items->leftJoin('transactions', 'tracks.id', '=', 'transactions.custom_id')
                    ->where('tracks.paid', 1)
                    ->where('transactions.paid_for', 'TRACK_DELIVERY')
                    ->where('transactions.paid_by', 'BONUS')
                    ->whereNotIn('transactions.type', ['ERROR', 'PENDING']);

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
        if (\Request::get('nof')) {
            if (\Request::get('nof') == 1) {
                $items->whereRaw('(tracks.store_status is NULL or tracks.store_status<=0)');
                $items->whereRaw('(tracks.azeriexpress_office_id is NULL or tracks.azeriexpress_office_id<=0)');
                $items->whereRaw('(tracks.azerpost_office_id is NULL or tracks.azerpost_office_id<=0)');
                $items->whereRaw('(tracks.surat_office_id is NULL or tracks.surat_office_id<=0)');
                $items->whereRaw('(tracks.yenipoct_office_id is NULL or tracks.yenipoct_office_id<=0)');
                $items->whereRaw('(tracks.kargomat_office_id is NULL or tracks.kargomat_office_id<=0)');
            }
            if (\Request::get('nof') == 2) {
                $qStr = '(';
                $qStr .= '(tracks.store_status is not NULL and tracks.store_status>0)';
                $qStr .= ' or (tracks.azeriexpress_office_id is not NULL and tracks.azeriexpress_office_id>0)';
                $qStr .= ' or (tracks.azerpost_office_id is not NULL and tracks.azerpost_office_id>0)';
                $qStr .= ' or (tracks.surat_office_id is not NULL and tracks.surat_office_id>0)';
                $qStr .= ' or (tracks.yenipoct_office_id is not NULL and tracks.yenipoct_office_id>0)';
                $qStr .= ' or (tracks.kargomat_office_id is not NULL and tracks.kargomat_office_id>0)';
                $qStr .= ')';
                $items->whereRaw($qStr);
            }
        } else if (\Request::get('filial_id') != null) {
            $f_group = explode('group_', \Request::get('filial_id'));

            if(count($f_group) == 2){
                $f_type = $f_group[1];

                $filials = DB::table('filials_v')
                    ->where('type', $f_type)
                    ->pluck('id')
                    ->toArray();

                switch ($f_type) {
                    case 'ASE':
                        $items->whereIn('tracks.store_status', $filials);
                        break;
                    case 'AZEXP':

                        $items->where('tracks.azeriexpress_office_id', $filials);
                        break;
                    case 'AZPOST':
                        $items->whereIn('tracks.azerpost_office_id', $filials);
                        break;
                    case 'SURAT':
                        $items->whereIn('tracks.surat_office_id', $filials);
                        break;
                    case 'YP':
                        $items->whereIn('tracks.yenipoct_office_id', $filials);
                        break;
                    case 'KARGOMAT':
                        $items->whereIn('tracks.kargomat_office_id', $filials);
                        break;
                    case 'UNKNOWN':
                        $items->whereIn('tracks.unknown_office_id', $filials);
                        break;
                }
            }else{
                list($f_type, $f_id) = explode('-', \Request::get('filial_id'));
                switch ($f_type) {
                    case 'ASE':
                        $items->where('tracks.store_status', $f_id);
                        break;
                    case 'AZEXP':
                        $items->where('tracks.azeriexpress_office_id', $f_id);
                        break;
                    case 'AZPOST':
                        $items->where('tracks.azerpost_office_id', $f_id);
                        break;
                    case 'SURAT':
                        $items->where('tracks.surat_office_id', $f_id);
                        break;
                    case 'YP':
                        $items->where('tracks.yenipoct_office_id', $f_id);
                        break;
                    case 'KARGOMAT':
                        $items->where('tracks.kargomat_office_id', $f_id);
                        break;
                    case 'UNKNOWN':
                        $items->where('tracks.unknown_office_id', $f_id);
                        break;
                }
            }



        }

        if (\Request::get('incustoms') != null) {
            $items->incustoms(\Request::get('incustoms'));
        }


        if (\Request::get('parcel') != null) {
            $parcel_name = preg_split("/[;:,\s]+/", trim(\Request::get('parcel')));
            $items->whereIn('containers.name', $parcel_name);
        }
        if (\Request::get('bag') != null) {
            $items->where('airboxes.name', \Request::get('bag'));
        }

        if (\Request::get('start_date') != null) {
            $dateField = \Request::get('date_by', 'created_at');
            $dateField = 'tracks.' . $dateField;
            $items->where($dateField, '>=', \Request::get('start_date') . " 00:00:00");
        }
        if (\Request::get('end_date') != null) {
            $dateField = \Request::get('date_by', 'created_at');
            $dateField = 'tracks.' . $dateField;
            $items->where($dateField, '<=', \Request::get('end_date') . " 23:59:59");
        }

        if (\Request::get('status') != null) {
            if (\Request::get('status') == 'ok') {
            } else {
                $items->where('tracks.status', \Request::get('status'));
            }
        } else {
            if (\Request::get('dec') == null || !\Request::get('dec')) {
                $items->where('tracks.status', '!=', 17);
            }
            if (\Request::get('dec') != null && \Request::get('dec') == 2) {
                //$items->whereRaw('(tracks.status not in (16,17,20,21,22,23,24)  and tracks.status > 12)');
                //$items->whereRaw('(tracks.status not in (16,17,20,21,22,23,24))');
                $items->whereNull('tracks.scanned_at')->whereNotIn('tracks.status', [16, 17, 18, 19, 20, 21, 22, 23, 24]);
            }
        }

        if (\Request::get('cd_status') != null) {
            $items->where('courier_deliveries.status', \Request::get('cd_status'));
        }

        if (\Request::get('delivery_type') != null) {
            $items->where('tracks.delivery_type', \Request::get('delivery_type'));
        }

        if (\Request::get('city') != null) {
            $items->whereRaw("tracks.city_name like '%" . \Request::get('city') . "%'");
        }

        if (\Request::get('partner_id') != null) {
            $items->where('tracks.partner_id', \Request::get('partner_id'));
        }

        $items_all = null;
        if (\Request::get('search_type') == 'export' || \Request::has('export')) {
            if ($items->count()) {
                $items = $items->get();
            } else {
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

    public function export($items = null)
    {

        $formats = ['Xlsx' => 'Xlsx', 'Mpdf' => 'pdf'];
        $type = isset($formats[\request()->get('format')]) ? \request()->get('format') : 'Xlsx';
        $ext = $formats[$type];

        if ($ext == 'pdf') {
            $pdf = PDF::loadView('admin.exports.pdf_tracks', compact('items'));
            return $pdf->download('packages_' . uniqid() . '.' . $ext);
        }

        return Excel::download(new TracksExport($items), 'tracks_' . uniqid() . '.' . $ext, $type);

    }

    public function logs($id)
    {
        try {
            $logs = Activity::where('content_id', $id)->where('content_type', Track::class)->orderBy('id', 'desc')->get();
            if (!$logs) {
                return back();
            }

            $CourierShelfLog = CourierShelfLog::where('type','tracks')->where('custom_id',$id)->get();
            return view('admin.widgets.logs', compact('logs', 'id','CourierShelfLog'));
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }

    function log($message)
    {
        file_put_contents("/var/log/track_status_change.log",$message."\n",FILE_APPEND);
    }
    public function ajax(Request $request, $id)
    {
        $track = Track::find($id);
        if ($request->get('name') == 'status') {

            $requestAll = json_encode($request->all());
            $this->log(now()->format('Y-m-d H:i:s'). " before id-{$track->id} status-{$track->status}  paid-{$track->paid} paid_debt-{$track->paid_debt} debt_price-{$track->debt_price} partner_id-{$track->partner_id} request-{$requestAll} ");;

            $data = [];
            $status = trim($request->get('value'));

            if ($track->status != $status) {
                /*$data['status'] = [
                    'before' => $track->status,
                    'after' => $status,
	    ];*/

                /* Send Notification */
                //if ($status == 16 && !$track->notification_inbaku_at) {
                if ($track->status != $status) {

                    if ($status == 16 || $status == 20) {
                        $track->notification_inbaku_at = Carbon::now();
                        $track->save();
                    }
                    if ($track->partner_id != 5 && $track->partner_id != 6) {
                        if ($status == 16) { // In Baku
                            if (!in_array($track->partner_id, [8]) || $track->delivery_type == 'PUDO') {  //If GFS then delivery_type = PUDO
                                Notification::sendTrack($id, $status);
                            }
                        } else {
                            Notification::sendTrack($id, $status);
                        }
                    }
                }
                //} else if ($status != 16) {
                //    Notification::sendTrack($id, $status);
                //}
            }

            if ($status == 17) { // done

                $isPartnerException = ($track->partner_id == 1);

                $canBeDone = false;

                // Əgər partner 1-dirsə (istisna hal)
                if ($isPartnerException) {
                    if (
                        ($track->debt_price > 0 && $track->paid_debt > 0) || // borcu var və ödənib
                        ($track->debt_price == 0)                            // borcu yoxdur
                    ) {
                        $canBeDone = true;
                    }
                }
                // Normal hallar (digər partnerlər)
                else {
                    if (
                        $track->paid == 1 && ( // ödəniş mütləq olmalı
                            ($track->debt_price > 0 && $track->paid_debt > 0) || // borcu var və ödənib
                            ($track->debt_price == 0)                            // borcu yoxdur
                        )
                    ) {
                        $canBeDone = true;
                    }
                }

                if ($canBeDone) {
                    // ✅ done ola bilər
                    $cd = $track->courier_delivery;
                    if ($cd && $cd->status != 6) {
                        $cd = CD::removeTrack($cd, $track);
                    }
                    event(new TrackCell('done', $track->id));
                } else {
                    // ❌ şərtlər ödənməyib – səhv mesajları
                    if ($track->debt_price > 0 && $track->paid_debt == 0) {
                        return response()->json('Saxlanc borcu var, amma odenilmeyib.', 400);
                    }

                    if (!$isPartnerException && $track->paid == 0) {
                        return response()->json('Baglama odenisi odenilmeyib.', 400);
                    }

                    return response()->json('Items have debt price!', 400);
                }
            }


            if ($status == 18) { //if in customs
                $cd = $track->courier_delivery;
                if ($cd && ($cd->status != 6)) { // delete courier delivery if not done
                    $cd = CD::popTrack($cd, $track);
                }
                Notification::sendTrack($id, $status);
            }

            if ($status == 45) {
                //send notification here for new status
                Notification::sendTrack($id, $status);
            }

            if($status == 16){ //In Baku statusuna keciren zaman pudo statusunu da menteqeye catib et
                if($track->azeriexpresspackage()->exists()){
                    $track->azeriexpresspackage()->update([
                        "status" => 8
                    ]);
                }elseif($track->precinctpackage()->exists()){
                    $track->precinctpackage()->update([
                        "status" => 8
                    ]);
                }elseif($track->azerpostpackage()->exists()){
                    $track->azerpostpackage()->update([
                        "status" => 8
                    ]);
                }elseif($track->suratpackage()->exists()){
                    $track->suratpackage()->update([
                        "status" => 8
                    ]);
                }elseif($track->yenipoctpackage()->exists()){
                    $track->yenipoctpackage()->update([
                        "status" => 8
                    ]);
                }elseif($track->kargomatpackage()->exists()){
                    $track->kargomatpackage()->update([
                        "status" => 8
                    ]);
                }
            }

            if($status==28){
                $response = (new PackageService())->returnDelivery($track, $status);
                if($response['success']==false){
                    return response()->json([
                        'message' => $response['message']
                    ], 400);
                }
            }else{
                (new PackageService())->updateStatus($track, $status);
            }


            $nowTrack = Track::find($id);

            $this->log(now()->format('Y-m-d H:i:s') . " after id-{$nowTrack->id} status-{$nowTrack->status}  paid-{$nowTrack->paid} paid_debt-{$nowTrack->paid_debt} partner_id-{$track->partner_id} debt_price-{$nowTrack->debt_price}  ");;

        }



        if ($request->get('name') === 'paid') {
            $value = $request->get('value');

            if ($value != 0 && $track->status == 16) {
                $track->requested_at = Carbon::now();
                $track->save();

                event(new TrackCell('find', $track->id));

                if ($value == 4) {
                    if ($track->partner_id != 9) {
                        return response()->json([
                            'status'  => false,
                            'message' => 'This is not taobao',
                        ], 422);

                    }

                    $type = config('ase.attributes.package.paid')[$value] ?? null;

                    if ($type) {
                        Transaction::create([
                            'custom_id'  => $track->id,
                            'paid_by'    => $type,
                            'amount'     => $track->delivery_price,
                            'source_id'  => null,
                            'type'       => 'OUT',
                            'paid_for'   => 'TRACK_DELIVERY',
                        ]);

                        $request->merge(['value' => 1]);
                    }
                } else {
                    $check = Transaction::where('custom_id', $track->id)
                        ->where('paid_for', 'TRACK_DELIVERY')
                        ->where('type', 'OUT')
                        ->first();

                    if ($check && $check->paid_by != 'PORTMANAT') {
                        Transaction::where('custom_id', $track->id)
                            ->where('paid_for', 'TRACK_DELIVERY')
                            ->delete();
                    }
                }
            }
            if ($value == 0) {
                $track->requested_at = null;
                $check = Transaction::where('custom_id', $track->id)->where('paid_for', 'TRACK_DELIVERY')->where('type', 'OUT')->first();
                if ($check && $check->paid_by != 'PORTMANAT') {
                    Transaction::where('custom_id', $track->id)->where('paid_for', 'TRACK_DELIVERY')->delete();
                }
                $track->save();
            }
        }



        if($request->get('name') == 'paid_debt'){
            $admin = Auth::user();
            if($request->get('value') == 1){
                return \Response::json(['message' => ' Yes, it cannot be selected.!'],400);
                exit();
            }

            if ($request->get('value') == 2) {
                if (!$admin->hasRole('super_admin')) {
                    return response()->json(['message' => 'Only Super Admin can select this option!'], 403);
                }

            }

            if($request->get('value') == 3){
                Transaction::create([
                    'custom_id' => $track->id,
                    'paid_by' => 'KAPITAL',
                    'amount' => $track->debt_price,
                    'source_id' => null,
                    'type' => 'OUT',
                    'paid_for' => 'TRACK_DEBT',
                    'debt' => 1,
                ]);
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
                $track->store_status = $filial->id;
                $track->azeriexpress_office_id = null;
                $track->azerpost_office_id = null;
                $track->surat_office_id = null;
                $track->yenipoct_office_id = null;
                $track->kargomat_office_id = null;
                $track->save();

            }elseif($typeId == 'AZEXP'){

                $filial = AzeriExpressOffice::find($fid);
                $track->store_status = null;
                $track->azeriexpress_office_id = $filial->id;
                $track->azerpost_office_id = null;
                $track->surat_office_id = null;
                $track->kargomat_office_id = null;
                $track->yenipoct_office_id = null;
                $track->save();

            }elseif($typeId == 'AZPOST'){

                $filial = AzerpostOffice::find($fid);
                $track->store_status = null;
                $track->azeriexpress_office_id = null;
                $track->azerpost_office_id = $filial->id;
                $track->surat_office_id = null;
                $track->kargomat_office_id = null;
                $track->yenipoct_office_id = null;
                $track->save();

            }elseif($typeId == 'SURAT'){

                $filial = SuratOffice::find($fid);
                $track->store_status = null;
                $track->azeriexpress_office_id = null;
                $track->azerpost_office_id = null;
                $track->kargomat_office_id = null;
                $track->surat_office_id = $filial->id;
                $track->yenipoct_office_id = null;
                $track->save();

            }elseif ($typeId == 'YP'){

                $filial = \App\Models\YeniPoct\YenipoctOffice::find($fid);

                $track->store_status = null;
                $track->azeriexpress_office_id = null;
                $track->azerpost_office_id = null;
                $track->surat_office_id = null;
                $track->kargomat_office_id = null;
                $track->yenipoct_office_id = $filial->id;
                $track->save();
            }elseif ($typeId == 'KARGOMAT'){

                $filial = \App\Models\Kargomat\KargomatOffice::find($fid);
                $track->store_status = null;
                $track->azeriexpress_office_id = null;
                $track->azerpost_office_id = null;
                $track->surat_office_id = null;
                $track->yenipoct_office_id = null;
                $track->kargomat_office_id = $filial->id;
                $track->save();
            }else{
                echo 'Type id not found';
            }

            return response()->json(['message' => 'Success'], 200);

        }

        if ($request->get('name') == 'worker_comments') {
            $str = $request->get('value');
            if (isOfficeWord($str)) {
                $cd = $track->courier_delivery;
                CD::removeTrack($cd, $track);
            }
        }
        if ($request->get('name') == 'airbox.name') {
            $a_name = $request->get('value');
            $airbox = $track->airbox;
            if ($airbox && empty(trim($a_name))) {
                $track->airbox_id = NULL;
                $track->save();
                return;
            }
            $container = $track->container;
            if ($container && (!$airbox || ($airbox->name != $a_name))) {
                $airbox = Airbox::where('partner_id', $track->partner_id)->where('container_id', $container->id)->where('name', $a_name)->first();
                if (!$airbox) {
                    $airbox = new Airbox();
                    $airbox->container_id = $container->id;
                    $airbox->partner_id = $track->partner_id;
                    $airbox->name = $a_name;
                    $airbox->save();
                }
                $track->airbox_id = $airbox->id;
                $track->save();
            }
            return;
        }
        if ($request->get('name') == 'courier_delivery.courier_id') {

            try {
                if (!$track) {
                    return response()->json([
                        'message' => 'Track not found!'
                    ], 404);
                }

                if ($track->debt_price > 0 && $track->paid_debt == 0) {
                    return response()->json([
                        'message' => 'Items have debt price!'
                    ], 400);
                }

                $courier_id = $request->get('value');
                $cd_status = 1; // accepted

                $cd = $track->courier_delivery;

                if ($cd) {
                    $cd_status = $cd->status;
                }

                $str = $track->worker_comments;

                if (function_exists('isOfficeWord') && (!$courier_id || isOfficeWord($str))) {
                    if ($cd) {
                        CD::removeTrack($cd, $track);
                    }
                    return response()->json([
                        'message' => 'Courier removed from track.'
                    ], 200);
                }

                if ($cd && (($cd->courier_id != $courier_id) || ($cd->address != $track->address))) {
                    $cd = CD::updateTrack($cd, $track, $courier_id);
                }

                $new_cd = false;

                if (!$cd) {
                    $new_cd = true;
                    $cd = CD::newCD($track, $courier_id, $cd_status);

                    if (!$cd) {
                        return response()->json([
                            'message' => 'Failed to create courier delivery.'
                        ], 500);
                    }
                }

                $cd->save();

                $track->courier_delivery_id = $cd->id ?? null;
                $track->save();

                return response()->json([
                    'message' => 'Courier updated successfully.',
                    'new_cd'  => $new_cd
                ], 200);

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Server error: ' . $e->getMessage()
                ], 500);
            }
        }

        return parent::ajax($request, $id);
    }

    public function bagdepesh($id = null)
    {
        if (Auth::user()->can('customs-depesh')) {
            Artisan::call('depesh', ['package' => 7, 'parcel_id' => $id, 'checkonly' => 0, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function bagdepeshcheck($id = null)
    {
        if (Auth::user()->can('customs-check')) {
            Artisan::call('depesh', ['package' => 7, 'parcel_id' => $id, 'checkonly' => 1, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function packagecanceldepesh($id = null)
    {
        if (Auth::user()->can('customs-depesh')) {
            Artisan::call('canceldepesh', ['package' => 5, 'parcel_id' => $id, 'checkonly' => 0, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function packagedepesh($id = null)
    {
        if (Auth::user()->can('customs-depesh')) {
            Artisan::call('depesh', ['package' => 5, 'parcel_id' => $id, 'checkonly' => 0, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function packagedepeshcheck($id = null)
    {
        if (Auth::user()->can('customs-check')) {
            Artisan::call('depesh', ['package' => 5, 'parcel_id' => $id, 'checkonly' => 1, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function parseldepesh($id = null)
    {
        if (Auth::user()->can('customs-depesh')) {
            Artisan::call('depesh', ['package' => 6, 'parcel_id' => $id, 'checkonly' => 0, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function parseldepeshcheck($id = null)
    {
        if (Auth::user()->can('customs-check')) {
            Artisan::call('depesh', ['package' => 6, 'parcel_id' => $id, 'checkonly' => 1, 'htmlformat' => 1, 'user_id' => auth()->guard('admin')->user()->id]);
            $d_out = Artisan::output();
        } else {
            $d_out = "No permissions";
        }
        return view('admin.depesh', ['d_out' => $d_out]);
    }

    public function bagcarrierupdate($id)
    {
        if (Auth::user()->can('customs-reset')) {
            Artisan::call('carriers_track:update', ['package' => 2, 'track_id' => $id, 'checkonly' => 0, 'htmlformat' => 1]);
            $out = Artisan::output();
        } else {
            $out = "No permissions";
        }
        return view('admin.widgets.carrier_update', ['out' => $out]);
    }

    public function parselcarrierupdate($id)
    {
        if (Auth::user()->can('customs-reset')) {
            Artisan::call('carriers_track:update', ['package' => 0, 'track_id' => $id, 'checkonly' => 0, 'htmlformat' => 1]);
            $out = Artisan::output();
        } else {
            $out = "No permissions";
        }
        return view('admin.widgets.carrier_update', ['out' => $out]);
    }

    public function label($id)
    {
        $track = Track::with(['customer', 'warehouse', 'country'])->find($id);

        if (!$track) {
            abort(404, 'Track not found');
        }

        $shipper = $track->warehouse_id ? $track->warehouse : ($track->country ? $track->country->warehouse : null);

        if ($shipper && !$shipper->country) {
            abort(400, "Warehouse doesn't have a country.");
        }

        return view('admin.widgets.track-label', compact('track', 'shipper'));
    }

    public function updateIntegrationStatuses(Request $request)
    {
        $limit = $request->get('limit', 10);
        $type = $request->get('type', 'track');

        $tracks = DB::table('integration_statuses')
            ->where('type', $type)
            ->whereNull('processed_at')
            ->limit($limit)
            ->get();

        if ($tracks->isEmpty()) {
            return response()->json(['message' => 'No Track Found'], 404);
        }

        $trackingCodes = $tracks->pluck('barcode')->map(function ($code) {
            return trim($code);
        });
        $trackModels = Track::query()
            ->whereIn('tracking_code', $trackingCodes)
            ->get();

        if ($trackModels->isEmpty()) {
            return response()->json(['message' => 'No Track Found'], 404);
        }

        DB::table('integration_statuses')
            ->whereIn('barcode', $trackingCodes)
            ->update(['processed_at' => now()]);

        $packageService = new PackageService();
        foreach ($trackModels as $track) {
            $integrationStatus = $tracks->first(function ($value) use ($track) {
                return $value->barcode == $track->tracking_code;
            });
            dump($track->tracking_code, $integrationStatus->status ?? '-');
            if ($integrationStatus) {
                $packageService->updateStatus($track, $integrationStatus->status);//17 should be $track's ->status column
            } else {
                dump("not found: " . $track->tracking_code);
            }
        }


        return response()->json(['message' => $trackModels->count() . "'s status changed"], 200);
    }

    public function update(Request $request, $id)
    {
        $res = parent::update($request, $id);
        $track = Track::find($id);
        if ($track) {
            $customer_id = $track->customer_id;
            $track->assignCustomer();
            if ($track->customer_id != $customer_id)
                $track->save();
        }
        return $res;
    }

    public function track_filial(Request $request, $id = null)
    {
        $track = Track::findOrFail($id);
        if ($track->delivery_type == 'PUDO') {
            return response()->json(
                'Baghlama PUDO oldugu ucun filiali deyishe bilmezsiniz.'
                , 400);
        }

        if (!$request->input('value') || empty($request->input('value'))) {
            $track->azeriexpress_office_id = null;
            $track->azerpost_office_id = null;
            $track->surat_office_id = null;
            $track->yenipoct_office_id = null;
            $track->kargomat_office_id = null;
            $track->store_status = null;
            $track->save();
            return response()->json([
                'status' => true,
                'message' => 'Track filiali ugurla deyishdi',
            ]);
        }
        $value = explode('-', $request->input('value'));

        $partners = [
            'SURAT' => SuratOffice::class,
            'YP' => YenipoctOffice::class,
            'KARGOMAT' => KargomatOffice::class,
            'ASE' => DeliveryPoint::class,
            'AZEXP' => AzeriExpressOffice::class,
            'AZPOST' => AzerpostOffice::class,
            'UNKNOWN' => UnknownOffice::class
        ];

        $filial = $partners[$value[0]]::findOrFail($value[1]);
//echo $filial->id . ' -> '. $filial->name;return;

        $track->azerpost_office_id = null;
        $track->surat_office_id = null;
        $track->yenipoct_office_id = null;
        $track->kargomat_office_id = null;
        $track->store_status = null;
        $track->azeriexpress_office_id = null;
        $track->unknown_office_id = null;
        switch ($value[0]) {
            case 'AZEXP':
                $track->azeriexpress_office_id = $filial->id;
                break;
            case 'AZPOST':
                $track->azerpost_office_id = $filial->id;
                break;
            case 'SURAT':
                $track->surat_office_id = $filial->id;
                break;
            case 'YP':
                $track->yenipoct_office_id = $filial->id;
                break;
            case 'KARGOMAT':
                $track->kargomat_office_id = $filial->id;
                break;
            case 'ASE':
                $track->store_status = $filial->id;
                break;
            case 'UNKNOWN':
                $track->unknown_office_id = $filial->id;
                break;
            default:
                return response()->json(['error' => 'Invalid prefix'], 400);
        }

        $track->save();
        Notification::sendTrack($track->id, 'transit_filial_added');

        return response()->json([
            'status' => true,
            'message' => 'Track filiali ugurla deyishdi',
        ]);
    }


    public function debtTrackIndex(Request $request)
    {
        $query = Track::query();

        if ($request->status) {
            $query->whereIn('tracks.status', [$request->status]);
        }

        $query->where('tracks.debt_price', '>', 0);

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

        if ($request->q != null) {
            $q = str_replace('"', '', $request->q);
            $query->where(function ($query) use ($q) {
                $query->orWhere("tracks.tracking_code", "LIKE", "%" . $q . "%")->orWhere("tracks.fin", "LIKE", "%" . $q . "%")->orWhere("tracks.fullname", "LIKE", "%" . $q . "%")->orWhere("tracks.address", "LIKE", "%" . $q . "%")->orWhere("tracks.phone", "LIKE", "%" . $q . "%")->orWhere("tracks.email", "LIKE", "%" . $q . "%")->orWhere("tracks.detailed_type", "LIKE", "%" . $q . "%");
            });
        }
        if ($request->tl != null) {
            $tracking_codes = preg_split("/[;:,\s]+/", trim($request->tl));
            $query = $query->whereIn('tracks.tracking_code', $tracking_codes);
        }

        if ($request->parcel != null) {
            $parcel_name = preg_split("/[;:,\s]+/", trim($request->parcel));
            $query->join('containers', 'containers.id', '=', 'tracks.container_id')
                ->whereIn('containers.name', $parcel_name);
        }


        $results = $query->get();

        $statusLabelsJson = config('ase.attributes.track.statusShortWithLabel');
        $fixedJson = preg_replace('/([{,]\s*)(\w+)\s*:/', '$1"$2":', $statusLabelsJson);
        $statusLabels = json_decode($fixedJson, true);

        return view('admin.debt.track')->with([
            'results' => $results,
            'request' => $request,
            'statusLabels' => $statusLabels
        ]);
    }

    public function exportDebtTrack(Request $request)
    {
        $data = explode(',', $request->get('items'));

        $items = Track::whereIn('id', $data)->get();

        return Excel::download(new DebtTrack($items), 'debt_tracks_' . uniqid() . '.xlsx');
    }


}
