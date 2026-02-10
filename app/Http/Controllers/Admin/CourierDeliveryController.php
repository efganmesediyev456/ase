<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Http\Requests;
use App\Jobs\SendCourierDeliveriesToAzeriexpressJob;
use App\Models\Activity;
use App\Models\CD;
use App\Models\Courier;
use App\Models\Track;
use App\Models\Transaction;
use App\Services\Integration\GfsService;
use App\Services\Package\PackageService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Validator;
use View;
use Excel;
use App\Exports\Admin\CdsExport;

class CourierDeliveryController extends Controller
{
    protected $can = [
        'export' => true,
    ];
    protected $view = [
        'name' => 'Courier Delivery',
        'formColumns' => 20,
        'sub_title' => 'Courier Delivery',
        'total_sum' => [
            [
                'key' => 'delivery_price',
                'skip' => 11,
            ],
        ],
        'search' => [
            [
                'name' => 'q',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Search...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
            [
                'name' => 'parcel',
                'type' => 'text',
                'attributes' => ['placeholder' => 'MAWB...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
            [
                'name' => 'dir',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.cd.direction3',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All Directions',
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
                'name' => 'dec',
                'type' => 'select_from_array',
                'options' => [1 => 'All with Done'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Filter',
            ],
            [
                'name' => 'status',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.cd.status2',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All Status',
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
                'name' => 'paid_by',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.transaction.paid_by',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'By',
            ],
            [
                'name' => 'delivery_price',
                'label' => 'Has Delivery Price',
                'type' => 'checkbox',
                'default' => 0,
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
            [
                'name' => 'money_received',
                'type' => 'select_from_array',
                'options' => [0 => 'No', 1 => 'Yes'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Money Received',
            ],
            [
                'type' => 'html',
                'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
            ],
            [
                'name' => 'date_by',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.cd.date_by',
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
                'name' => 'sta',
                'label' => 'Show total all',
                'type' => 'checkbox',
                'default' => 0,
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
        ],
    ];

    protected $route = 'courier_deliveries';
    protected $modelName = 'CD';
    protected $extraActions = [
        [
            'key' => 'id',
            'label' => 'Info',
            'icon' => 'windows2',
            'route' => 'courier_deliveries.info',
            'color' => 'info',
            'target' => '_blank',
        ],
        [
            'route' => 'courier_deliveries.logs',
            'key' => 'id',
            'label' => 'Logs',
            'icon' => 'list',
            'color' => 'default',
            'target' => '_blank',
        ],
    ];

    protected $list = [
        'urgent' => [
            'label' => 'Urgent',
            'type' => 'select-editable',
            'editable' => [
                'route' => 'courier_deliveries.urgent',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.package.urgentWithLabel',
            ],
        ],
        'payment_link' => [
            'label' => 'Payment Link',
            //'type' => 'select-editable',
            'type' => 'custom.curier_payment_link',
        ],
        'first_track.partner.name' => [
            'label' => 'Partner',
        ],
        'parcel_name' => [
            'label' => 'MAWB',
        ],
        'packages_with_cells_br_str' => [
            'label' => 'Packages.',
            'type' => 'raw',
        ],
        'user' => [
            'type' => 'custom.user',
            'label' => 'User',
        ],
        'status' => [
            'label' => 'Status',
            'type' => 'select-editable',
            'editable' => [
                'route' => 'courier_deliveries.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.cd.status2WithLabel',
            ],
            'order' => 'status',
        ],
        'not_delivered_status' => [
            'label' => 'Not Delivered Status',
            'type' => 'select-editable',
            'editable' => [
                'route' => 'courier_deliveries.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.cd.notDeliveredStatusCdWithLabel',
            ],
        ],
        'company_name' => [
            'label' => 'Company',
        ],
        'direction_with_label' => [
            'label' => 'Direction',
        ],
        'invoice_type_with_label' => [
            'label' => 'Invoice',
        ],
        /*    'courier.name'     => [
                 'label' => 'Courier',
         ],*/
        'courier_id' => [
            //'type'  => 'custom.user',
            'label' => 'Courier',
            'type' => 'select-editable',
            'editable' => [
                'route' => 'courier_deliveries.ajax',
                'type' => 'select',
                'source' => null,
                //'sourceFromConfig' => 'ase.attributes.package.statusWithLabel',
            ],
        ],
        'shelf' => [
            'label' => 'Shelf',
            'type' => 'text',
        ],
        'courier.location_url2' => [
            'label' => 'Courier Location',
        ],
        'courier_comment' => [
            'label' => 'Courier Comment',
            'type' => 'text',
        ],
        'photo_url2' => [
            'label' => 'Photo',
        ],
        'location_url2' => [
            'label' => 'Location',
        ],
        'name' => [
            'label' => 'Recipient',
            'order' => 'name',
        ],
        'delivery_price_with_color' => [
            'label' => 'Delivery Price',
            'type' => 'editable_raw',
            'editable' => [
                'key' => 'delivery_price',
                'route' => 'courier_deliveries.ajax',
                'type' => 'number',
            ],
        ],
        'paid' => [
            'label' => 'Paid',
            'type' => 'paid',
            'editable' => [
                'route' => 'courier_deliveries.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.package.paidWithLabel',
            ],
        ],
        'paid_by' => [
            'label' => 'By',
        ],
        'recieved' => [
            'label' => 'Money received',
            'type' => 'select-editable',
            'editable' => [
                'route' => 'courier_deliveries.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.yes_noWithLabel',
            ],
        ],
        'phone' => [
            'label' => 'Phone',
            'order' => 'phone',
        ],
        'address' => [
            'label' => 'Address',
            'order' => 'address',
        ],
        'addr_location_url2' => [
            'label' => 'Addr Location',
        ],
        'user_comment' => [
            'label' => 'User Comment',
            'type' => 'text',
        ],
        'courier_assigned_at' => [
            'label' => 'AssignedAt',
            'order' => 'courier_assigned_at',
        ],
        'courier_get_at' => [
            'label' => 'CrouerierGetAt',
            'order' => 'courier_get_at',
        ],
        'delivered_at' => [
            'label' => 'DeliveredAt',
            'order' => 'delivered_at',
        ],

        'created_at' => [
            'label' => 'CreatedAt',
        ],
    ];

    protected $fields = [
        [
            'label' => 'User',
            'type' => 'select2',
            'name' => 'user_id',
            'attribute' => 'full_name,customer_id',
            'model' => 'App\Models\User',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|integer',
            'allowNull' => true,
            'attributes' => [
                'data-validation' => 'required',
                'class' => 'select2-ajax',
                'data-url' => '/search-users',
            ],
        ],
        [
            'label' => 'Courier',
            'type' => 'select2',
            'name' => 'courier_id',
            'attribute' => 'name',
            'model' => 'App\Models\Courier',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|integer',
            'allowNull' => true,
        ],
//        [
//            'name' => 'partner_id',
//            'type' => 'select_from_array',
//            'optionsFromConfig' => 'ase.attributes.track.partner',
//            'wrapperAttributes' => [
//                'class' => 'col-lg-3',
//            ],
//            'allowNull' => 'All Partners',
//        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'name' => 'status',
            'label' => 'Status',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.cd.status2',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'not_delivered_status',
            'label' => 'Not Delivered Status',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.cd.notDeliveredStatusCd',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'label' => 'Courier Comment',
            'type' => 'textarea',
            'name' => 'courier_comment',
            'attributes' => [
                'rows' => '3',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'label' => "Company Name",
            'type' => 'text',
            'name' => 'company_name',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'direction',
            'label' => 'Direction',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.cd.direction',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'invoice_type',
            'label' => 'Invoice Type',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.cd.invoice',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        /*[
            'label'             => 'Desired time',
            'type'              => 'datetime',
            'name'              => 'desired_time',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
    ],*/
        [
            'label' => "Name",
            'type' => 'text',
            'name' => 'name',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'label' => 'Phone',
            'type' => 'text',
            'name' => 'phone',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'label' => 'Packages',
            'type' => 'text',
            'name' => 'packages_txt',
            'nodb' => true,
            'allowNull' => true,
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'label' => 'Additional Number',
            'type' => 'text',
            'name' => 'additional_number',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'label' => 'Address',
            'type' => 'textarea',
            'name' => 'address',
            'attributes' => [
                'rows' => '3',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'label' => 'User Comment',
            'type' => 'textarea',
            'name' => 'user_comment',
            'attributes' => [
                'rows' => '3',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'label' => 'Courier Comment',
            'type' => 'textarea',
            'name' => 'courier_comment',
            'attributes' => [
                'rows' => '3',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'delivery_price',
            'label' => 'Delivery Price',
            'type' => 'text',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|numeric|required',
        ],
        [
            'name' => 'recieved',
            'label' => 'Recieved Money from Courier',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'col-md-4 mt-15',
            ],
            'validation' => 'nullable|integer',
        ],

    ];

    public function __construct()
    {
        //$this->fields[3]["default"]=date('Y-m-d H:i:s');
        $this->fields[19]["default"] = 3;
        $allCouriers = [];
        $couriers = Courier::orderBy('name', 'asc')->get();
        foreach ($couriers as $courier) {
            $allCouriers[] = [
                'value' => $courier->id,
                'text' => $courier->name,
            ];
        }
        $this->list['courier_id']['editable']['source'] = \GuzzleHttp\json_encode($allCouriers, true);
        parent::__construct();
    }

    public function info($id)
    {
        $item = CD::with(['user', 'courier'])->find($id);

        if (!$item) {
            abort(404);
        }

        return view('admin.cd_info', compact('item'));
    }

    public function update(Request $request, $id)
    {

        $id = ($request->get('only_id') == null && \request()->route('id') != null) ? \request()->route('id') : $id;

        $this->validate($request, $this->generateValidation('update', $id));
        $this->setCurrentLang();

        $allRequest = $request->all();
        foreach ($request->all() as $key => $value) {
            if (empty($value) && $value !== '0') {
                $allRequest[$key] = null;
            }
        }

        $item = CD::find($id);


        $lang = $request->get('_lang');

        if ($lang) {
            $item->setDefaultLocale($lang);
        }

        $pivotFields = [];

        foreach ($this->fields as $field) {
            if (isset($field['nodb']) && $field['nodb']) continue;
            if (isset($field['pivot']) && $field['pivot']) {
                $pivotFields[] = $field;
            } else {
                if (isset($field['name']))
                    $item->{$field['name']} = $request->get($field['name']);
            }
        }
        if ($item->courier_id && $item->status < 2)
            $item->status = 2;
        if ($request->has('packages_txt')) {
            $item->packages_txt = $request->get('packages_txt');
        }
        $item->save();
        if ($request->has('packages_txt')) {
            $item->updatePackages($request->get('packages_txt'));
        }

        /* Sync n-n relations */
        if ($pivotFields) {
            foreach ($pivotFields as $pivotField) {
                $item->{$pivotField['name']}()->sync($request->get($pivotField['name']));
            }
        }
        if ($request->get('only_id') == null) {
            Alert::success(trans('saysay::crud.action_alert', [
                'name' => $this->modelName,
                'key' => clearKey($this->notificationKey),
                'value' => $item->{$this->notificationKey},
                'action' => 'updated',
            ]));

            return redirect()->route($this->route . '.index', $this->routeParams);
        }

        return $item->id;
    }

    public function store(Request $request)
    {

        if (!$this->can['create']) {
            return abort(403);
        }

        $this->validate($request, $this->generateValidation('store'));

        $user_id = 0;
        if ($request->has('user_id'))
            $user_id = $request->get('user_id');
        $item = CD::create(['user_id' => $user_id]);

        // replace empty values with NULL, so that it will work with MySQL strict mode on
        $allRequest = $request->all();

        foreach ($request->all() as $key => $value) {
            if (empty($value) && $value !== '0') {
                $allRequest[$key] = null;
            }
        }

        $pivotFields = [];

        foreach ($this->fields as $field) {
            if (isset($field['nodb']) && $field['nodb']) continue;
            if (isset($field['pivot']) && $field['pivot']) {
                $pivotFields[] = $field;
            } else {
                if (isset($field['name']))
                    $item->{$field['name']} = $request->get($field['name']);
            }
        }
        if ($this->routeParams) {
            foreach ($this->routeParams as $key => $routeParam) {
                $item->{$key} = $routeParam;
            }
        }

        if (method_exists($this, 'autoFill')) {
            foreach ($this->autoFill() as $key => $val) {
                $item->{$key} = $val;
            }
        }
//        if ($item->courier_id && (!$request->has('status') || $request->get('status') === null || $request->get('status') < 2)) {
//            if (!$request->has('status') || $request->get('status') === null) {
//                $item->status = 2;
//            }
//        }

        if($request->has('status')){
            $item->status = $request->get('status');
        }

        if ($request->has('packages_txt')) {
            $item->packages_txt = $request->get('packages_txt');
        }

        $item->save();
        if ($request->has('packages_txt')) {
            $item->updatePackages($request->get('packages_txt'));
        }
        if ($pivotFields) {
            foreach ($pivotFields as $pivotField) {
                $item->{$pivotField['name']}()->sync($request->get($pivotField['name']));
            }
        }

        if ($request->get('only_id') == null) {
            Alert::success(trans('saysay::crud.action_alert', [
                'name' => $this->modelName,
                'key' => clearKey($this->notificationKey),
                'value' => $item->{$this->notificationKey},
                'action' => 'created',
            ]));

            return $this->createRedirection ? redirect()->route($this->createRedirection, $item->id) : redirect()->route($this->route . '.index', $this->routeParams);
        }


        return $item->id;
    }

    /*public function edit($id)
    {
	echo $id;
	return;
    }*/

    public function indexObject()
    {
        $validator = Validator::make(\Request::all(), [
//            'status ' => 'integer',
            'courier_id ' => 'integer',
            'user_id ' => 'integer',
        ]);

        if ($validator->failed()) {
            Alert::error('Unexpected variables!');

            return redirect()->route("my.dashboard");
        }
        $items = CD::with(['tracks', 'packages', 'courier'])->select('courier_deliveries.*');
        if (\request()->get('sort') != null) {
            $sortKey = explode("__", \request()->get('sort'))[0];
            $sortType = explode("__", \request()->get('sort'))[1];
            $items = $items->orderBy('courier_deliveries.' . $sortKey, $sortType)->orderBy('courier_deliveries.id', 'desc');
        } else {
            $items = $items->orderBy('courier_deliveries.created_at', 'desc');
        }


        if (\Request::get('status') != null) {
            if (\Request::get('status') == 101)
                $items = $items->whereIn('courier_deliveries.status', [2, 3, 7]);
            else if (\Request::get('status') == 102)
                $items = $items->whereIn('courier_deliveries.status', [4, 6]);
            else
                $items = $items->where('courier_deliveries.status', \Request::get('status'));
        } else if (\Request::get('id') != null) {
            $items = $items->where('courier_deliveries.id', \Request::get('id'));
        } else {
            if (!\Request::get('dec')) {
                $items = $items->whereRaw('(courier_deliveries.status != 6 or (courier_deliveries.delivery_price > 0 and courier_deliveries.direction in(1,2) and courier_deliveries.recieved=0))');
            }
        }
        if (\Request::get('partner_id') != null || \Request::get('parcel') != null) {

            $items = $items->leftJoin('tracks', 'courier_deliveries.id', 'tracks.courier_delivery_id');
            if (\Request::get('parcel') != null) {
                $items = $items->leftJoin('containers', 'tracks.container_id', 'containers.id')
                    ->where('containers.name', \Request::get('parcel'));
            }
            if (\Request::get('partner_id') != null) {
                $items->where('tracks.partner_id', \Request::get('partner_id'));
            }

        }

        if (\Request::get('q') != null) {
            $items = $items->leftJoin('users', 'courier_deliveries.user_id', 'users.id');
            $q = strtolower(trim(str_replace('"', '', \Request::get('q'))));
            $items->whereRaw("(lower(packages_txt) like '%" . $q . "%' or lower(courier_deliveries.name) like '%" . $q . "%' or courier_deliveries.phone like '%" . $q . "%' or lower(user_comment) like '%" . $q . "%' or lower(courier_deliveries.address) like '%" . $q . "%' or lower(users.customer_id) like '%" . $q . "%')");
        }

        if (\Request::get('paid_by') != null) {
            $items = $items->leftJoin('transactions', function ($join) {
                //$join->on('transactions.paid_for', '=', 'COURIER_DELIVERY');
                $join->on('transactions.custom_id', '=', 'courier_deliveries.id');
            });
            $items->where('transactions.paid_by', \Request::get('paid_by'));
            $items->where('transactions.paid_for', 'COURIER_DELIVERY');
        }

        if (\Request::get('courier_id') != null) {
            $items->where('courier_id', \Request::get('courier_id'));
        }

        if (\Request::get('delivery_price')) {
            $items->where('courier_deliveries.delivery_price', '>', '0');
        }

        if (\Request::get('dir') != null) {
            if (\Request::get('dir') == 12) {
                $items->whereIn('direction', [1, 2]);
            } else {
                $items->where('direction', \Request::get('dir'));
            }
        }
        if (\Request::get('money_received') != null) {
            $items->where('courier_deliveries.recieved', \Request::get('money_received'));
        }
//        if (\Request::get('partner_id') != null) {
//            $items->where('tracks.partner_id', \Request::get('partner_id'));
//        }

//        if (\Request::get('parcel') != null) {
//            $items->where('containers.name', \Request::get('parcel'));
//        }

        if (\Request::get('user_id') != null) {
            $items->where('courier_deliveries.user_id', \Request::get('user_id'));
        }

        if (\Request::get('start_date') != null) {
            $dateField = \Request::get('date_by', 'created_at');
            $dateField = 'courier_deliveries.' . $dateField;
            $items->where($dateField, '>=', \Request::get('start_date') . " 00:00:00");
        }
        if (\Request::get('end_date') != null) {
            $dateField = \Request::get('date_by', 'created_at');
            $dateField = 'courier_deliveries.' . $dateField;
            $items->where($dateField, '<=', \Request::get('end_date') . " 23:59:59");
        }

        $items_all = null;

        if (\Request::get('search_type') == 'export' || \Request::has('export')) {
            if ($items->count()) {
                $items = $items->get();
            }
        } else {
            if (\Request::get('sta') == 1)
                $items_all = $items->get();
            $items = $items->paginate($this->limit);
        }

        View::share('items_all', $items_all);
        return $items;
    }

    public function export($items = null)
    {

        $formats = ['Xlsx' => 'Xlsx', 'Mpdf' => 'pdf'];
//        $formats = ['Xlsx' => 'Xlsx', 'pdf' => 'pdf'];
        $type = isset($formats[\request()->get('format')]) ? \request()->get('format') : 'Xlsx';
        $ext = $formats[$type];

        if ($ext == 'pdf') {
            $pdf = PDF::loadView('admin.exports.pdf_cds', compact('items'));
            return $pdf->download('packages_' . uniqid() . '.' . $ext);
        }

        return Excel::download(new CdsExport($items), 'cds_' . uniqid() . '.' . $ext, $type);
    }


    public function logs($id)
    {
        $logs = Activity::where('content_id', $id)->where('content_type', CD::class)->orderBy('id', 'desc')->get();
        if (!$logs) {
            return back();
        }

        return view('admin.widgets.logs', compact('logs', 'id'));
    }

    public function ajax(Request $request, $id)
    {
        $used = CD::find($id);

        if ($request->get('name') == 'paid') {
            if ($request->get('value') != 0) {
                $type = $request->get('value') == 1 ? 'CASH' : config('ase.attributes.package.paid')[$request->get('value')];
                $request->merge(['value' => 1]);
                //if($used->packages && count($used->packages)>0)
                //    Transaction::addCD($used->id, $type);
            } else if ($used) {
                $check = Transaction::where('custom_id', $used->id)->where('paid_for', 'COURIER_DELIVERY')->where('type', 'OUT')->first();
                if ($check && ($check->paid_by != 'PORTMANAT' || $check->type == 'ERROR')) {
                    Transaction::where('custom_id', $used->id)->where('paid_for', 'COURIER_DELIVERY')->delete();
                }
            }
        }
        if ($request->get('name') == 'courier_id') {
            if ($request->get('value') != 0) {
                if ($used && !$used->status) {
                    $used->status = 2;
                    $used->save();
                }
            }
        }

        //TODO:: KURYER COMMENT ELAVE ETMEY
        //if ($request->get('name') == 'status' && array_key_exists($request->get('value'), GfsService::COURIER_STATES)) {
        //    $tracks = explode(',', $used->packages_txt);
        //
        //    foreach ($tracks as $trackCode) {
        //        $track = Track::query()->where('tracking_code', $trackCode)->first();
        //        if ($track) {
        //            $service = new PackageService();
        //            $service->updateStatus($track, $request->get('value'));
        //
        //            $track->status = 22;
        //            $track->comment_txt = "Track status updated to: " . $request->get('value');
        //            $track->save();
        //
        //            return Response::json(['message' => "Item's " . $request->get('name') . " has been updated!!"]);
        //        }
        //    }
        //}
        return parent::ajax($request, $id);

    }


    public function updateAzeriexpressDeliveries(Request $request)
    {
        $date = $request->get('date');
        $date = Carbon::parse($date)->toDateString();
        $courierDeliveries = CD::whereDate('created_at', $date)
            ->where('courier_id', 11)
            ->get();
        foreach ($courierDeliveries as $courierDelivery) {
            SendCourierDeliveriesToAzeriexpressJob::dispatch($courierDelivery);
        }
    }


    public function payGetDebt($code)
    {

        $item = Track::where('custom_id', $code)->first();
        if (!$item) {
            abort(404, 'Track not found');
        }

        return view('front.track.pay-debt', compact('item'));

    }


    public function urgent(Request $request){
        $this->validate($request, [
            'name'  => 'required|in:urgent',
            'value' => 'required|boolean',
            'pk'    => 'required|integer|exists:courier_deliveries,id',
        ]);
        $courierDelivery=CD::find($request->pk);
        if($courierDelivery){
            $courierDelivery->urgent=$request->value;
            $courierDelivery->save();
        }
        return response()->json(['status' => 'success','message'=>'Successfully updated']);
    }

}
