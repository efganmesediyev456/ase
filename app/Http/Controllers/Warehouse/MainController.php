<?php

namespace App\Http\Controllers\Warehouse;

use Alert;
use App\Exports\Warehouse\ManifestExport;
use App\Exports\Warehouse\PackagesExport;
use App\Http\Controllers\Admin\Controller;
use App\Models\Bag;
use App\Models\CustomsModel;
use App\Models\Extra\Notification;
use App\Models\Package;
use App\Models\PackageCarrier;
use App\Models\PackageLog;
use App\Models\PackageType;
use App\Models\Parcel;
use App\Models\RuType;
use App\Models\UniTradeModel;
use App\Models\User;
use Bugsnag;
use Excel;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use Validator;
use View;
use function auth;

class MainController extends Controller
{
    public $itemId;
    protected $modelName = 'Package';

    protected $view = [
        'formColumns' => 10,
	'name' => 'All package',
        'search' => [
            [
                'name' => 'q',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Search...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3 col-lg-offset-0',
                ],
            ],
            [
                'name' => 'status',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.package.status_for_warehouse',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All',
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
                'name' => 'event_date_range',
                'start_name' => 'start_date',
                'end_name' => 'end_date',
                'type' => 'date_range',

                'date_range_options' => [
                    'timePicker' => true,
                    'locale' => ['format' => 'DD/MM/YYYY'],
                ],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
            ],
            [

                'name' => 'dec',
                'type' => 'select_from_array',
                'options' => [
                    2 => 'Not Declared',
                    1 => 'Ready',

                ],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Filter',
            ],
        ],
    ];

    protected $can = ['export' => true];

    protected $route = 'w-packages';

    protected $notificationKey = 'custom_id';

    protected $extraActions = [
        [
            'key' => 'invoice',
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

    protected $extraButtons = [
        [
            'key' => 'scan',
            'route' => 'w-parcels.create',
            'label' => 'Use Parcelling',
            'icon' => 'file-download',
            'color' => 'success',
            'target' => '_blank',
            'condition' => 'parcelling',
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
            'type' => 'editable',
            'editable' => [
                'route' => 'w-packages.ajax',
                'type' => 'text',
            ],
        ],
        'user' => [
            'type' => 'custom.user',
            'label' => 'User',
        ],
        'has_battery' => [
            'type' => 'battery',
            'label' => 'BTR',
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
            'label' => 'At',
            'type' => 'date',
        ],
    ];

    protected $list = [
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
            'type' => 'editable',
            'editable' => [
                'route' => 'w-packages.ajax',
                'type' => 'text',
            ],
        ],
        'user' => [
            'type' => 'custom.user',
            'label' => 'User',
        ],
        'has_battery' => [
            'type' => 'battery',
            'label' => 'BTR',
        ],
        'carrier' => [
            'type' => 'carrier',
            'label' => 'Customs',
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
            'label' => 'At',
            'type' => 'date',
        ],
    ];

    protected $fields = [
        [
            'name' => 'show_label',
            'type' => 'hidden',
            'default' => 1,
        ],
        [
            'name' => 'pkg_goods',
            'type' => 'hidden',
            'default' => '0',
        ],
        [
            'name' => 'detailed_type',
            'type' => 'hidden',
            'default' => null,
        ],
        [
            'name' => 'width',
            'type' => 'hidden',
            'default' => null,
        ],
        [
            'name' => 'height',
            'type' => 'hidden',
            'default' => null,
        ],
        [
            'name' => 'length',
            'type' => 'hidden',
            'default' => null,
        ],
        [
            'name' => 'length_type',
            'type' => 'hidden',
            'default' => 0,
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
            ],
            'validation' => 'nullable|string|min:5|unique:packages,tracking_code',
        ],
        [
            'label' => 'User',
            'type' => 'select_from_array',
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
                'data-url' => '/users',
            ],
        ],
        [
            'name' => 'website_name',
            'label' => 'WebSite name',
            'type' => 'text',
            'hint' => 'Also accept url',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'prefix' => '<i class="icon-link"></i>',
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'seller_name',
            'label' => 'Seller name',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'prefix' => '<i class="icon-user-tie"></i>',
            'validation' => 'nullable|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12"><h3 class="text-center">Shipping</h3></div>',
        ],
        [
            'name' => 'weight',
            'label' => 'Weight',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'short' => true,
            'validation' => 'nullable|numeric',
            'prefix' => '<i class="icon-meter2"></i>',
        ],
        [
            'name' => 'weight_type',
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'short' => true,
            'optionsFromConfig' => 'ase.attributes.weight',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'required|integer',
        ],
        [
            'name' => 'number_items',
            'label' => 'Items',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
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
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.currencies',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'default' => 0,
            'default_by_relation' => 'country.currency',
            'validation' => 'nullable|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12 hidden_for_user"><h3 class="text-center">Package Goods</h3></div>',
        ],
        [
            'type' => 'html',
            'html' => '<div id="type_section" class="col-lg-5 hidden_for_user"><div class="row type_item" id="main_type_item">',
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
                'class' => 'col-md-10',
            ],
            'validation' => 'nullable|string',
            'prefix' => '<i class="icon-clipboard2"></i>',
        ],
    ];

    protected $ru_fields = [
        [
            'name' => 'show_label',
            'type' => 'hidden',
            'default' => 1,
        ],
        [
            'name' => 'pkg_goods',
            'type' => 'hidden',
            'default' => '0',
        ],
        [
            'name' => 'detailed_type',
            'type' => 'hidden',
            'default' => null,
        ],
        [
            'name' => 'width',
            'type' => 'hidden',
            'default' => null,
        ],
        [
            'name' => 'height',
            'type' => 'hidden',
            'default' => null,
        ],
        [
            'name' => 'length',
            'type' => 'hidden',
            'default' => null,
        ],
        [
            'name' => 'length_type',
            'type' => 'hidden',
            'default' => 0,
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
            ],
            'validation' => 'nullable|string|min:5|unique:packages,tracking_code',
        ],
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
                //'class'           => 'select2-ajax',
                'data-url' => '/search-users',
            ],
        ],
        [
            'name' => 'website_name',
            'label' => 'WebSite name',
            'type' => 'text',
            'hint' => 'Also accept url',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'prefix' => '<i class="icon-link"></i>',
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'seller_name',
            'label' => 'Seller name',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'prefix' => '<i class="icon-user-tie"></i>',
            'validation' => 'nullable|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12"><h3 class="text-center">Shipping</h3></div>',
        ],
        [
            'name' => 'weight',
            'label' => 'Weight',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'short' => true,
            'validation' => 'nullable|numeric',
            'prefix' => '<i class="icon-meter2"></i>',
        ],
        [
            'name' => 'weight_type',
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'short' => true,
            'optionsFromConfig' => 'ase.attributes.weight',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'validation' => 'required|integer',
        ],
        [
            'name' => 'number_items',
            'label' => 'Items',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
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
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.currencies',
            'wrapperAttributes' => [
                'class' => 'col-md-1',
            ],
            'default' => 0,
            'default_by_relation' => 'country.currency',
            'validation' => 'nullable|integer',
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
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|string',
            'prefix' => '<i class="icon-clipboard2"></i>',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        View::share('bodyClass', 'sidebar-xs');
    }

    public function getRuTypeNameAutocomplete(Request $request)
    {

        $search = $request->search;
        $by = $request->by;
        $id = $request->id;

        if ($search == '') {
            if ($by == 'name') {
                $autocomplate = RuType::orderby('name_ru', 'asc')->select('id', 'hs_code', 'name_ru')->limit(30)->get();
            }
            if ($by == 'hs_code') {
                $autocomplate = RuType::orderby('hs_code', 'asc')->select('id', 'hs_code', 'name_ru')->limit(30)->get();
            }
        } else {
            if ($by == 'name') {
                $autocomplate = RuType::orderby('name_ru', 'asc')->select('id', 'hs_code', 'name_ru')->where('name_ru', 'like', '%' . $search . '%')->limit(30)->get();
            }
            if ($by == 'hs_code') {
                $autocomplate = RuType::orderby('hs_code', 'asc')->select('id', 'hs_code', 'name_ru')->where('hs_code', 'like', '%' . $search . '%')->limit(30)->get();
            }
        }

        $response = array();
        foreach ($autocomplate as $autocomplate) {
            $response[] = array("value" => $autocomplate->id,
                "label" => $autocomplate->hs_code . ' - ' . $autocomplate->name_ru,
                "hs_code" => $autocomplate->hs_code,
                "name" => $autocomplate->name_ru,
                "id" => $id);
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Label for package
     *
     * @param $id
     * @return Factory|\Illuminate\View\View
     */

    public function PDFInvoice($id)
    {
        $item = Package::with(['user', 'warehouse', 'country'])->whereWarehouseId($this->id())->find($id);
        $shipper = $item->warehouse_id ? $item->warehouse : ($item->country ? $item->country->warehouse : null);

        if (!$item) {
            return abort(404);
        }
        if (request()->has('html'))
            return view('front.widgets.invoice', compact('item', 'shipper'));

        $pdf = PDF::loadView('front.widgets.invoice', compact('item', 'shipper'));

        return $pdf->setPaper('a4')->setWarnings(false)->stream($id . '_invoice.pdf');
    }

    public function label($id)
    {
        $item = Package::with(['user', 'warehouse', 'country'])->whereWarehouseId($this->id())->find($id);
        $shipper = $item->warehouse_id ? $item->warehouse : ($item->country ? $item->country->warehouse : null);

        if (!$item) {
            Alert::warning('Package not found');

            return redirect()->route('w-packages.index');
        }
        $item->updateCarrier();

        return view('admin.widgets.label', compact('item', 'shipper'));
    }

    /**
     * PDF Label for package
     *
     * @param $id
     * @return Factory|\Illuminate\View\View
     */
    public function PDFLabel($id)
    {
        $checkDraftLabel = 0;
        if (\request()->has('cdfl') && \request()->get('cdfl') == '1')
            $checkDraftLabel = 1;
        $item = Package::with(['user', 'warehouse', 'country'])->find($id);
        $shipper = $item->warehouse_id ? $item->warehouse : ($item->country ? $item->country->warehouse : null);

        if (!$item) {
            Alert::warning('Package not found');

            return redirect()->route('w-packages.index');
        }
        if (!$checkDraftLabel)
            $item->updateCarrier();

        $pdf = PDF::loadView('admin.widgets.pdf_label', compact('item', 'shipper'));

        return $pdf->setPaper('a4', 'landscape')->setWarnings(false)->stream($id . '_label.pdf');
    }

    public function check()
    {
        if ($this->me()->parcelling) {
            return redirect()->route('w-parcels.index');
        }

        return redirect()->route('w-packages.index');
    }

    /**
     * @return LengthAwarePaginator
     */
    public function indexObject()
    {
        $validator = Validator::make(\Request::all(), [
            'q' => 'nullable|string',
            'warehouse_id' => 'nullable|integer|min:0',
            'status' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return ['error' => 'Unexpected variables!'];
        }

        $countryId = $this->me()->country_id;

        $items = Package::with(['parcel', 'bag'])->whereWarehouseId($this->id())->whereIn('packages.status', [0, 6]);
        /*->orWhere(function ($q) use ($countryId) {
           return $q->where('country_id', $countryId)->where('status', 6);
       });*/
        $items = $items->leftJoin('package_carriers', 'packages.id', 'package_carriers.package_id')->select('packages.*', 'package_carriers.inserT_DATE');

        if (\Request::get('q') != null) {
            $q = \Request::get('q');

            $items->where(function ($query) use ($q) {
                $query->where("tracking_code", "LIKE", "%" . $q . "%")->orWhere("website_name", "LIKE", "%" . $q . "%")->orWhere("custom_id", "LIKE", "%" . $q . "%")->orWhereHas('user', function (
                    $query
                ) use ($q) {
                    $query->where('customer_id', 'LIKE', '%' . $q . '%')->orWhere('email', 'LIKE', '%' . $q . '%')->orWhere(DB::raw('concat(trim(name)," ",trim(surname))'), 'LIKE', "%" . $q . "%");
                });
            });
        }
        if (\Request::get('status') != null) {
            $items->where('packages.status', \Request::get('status'));
        }

        if (\Request::get('incustoms') != null) {
            if (\Request::get('incustomscheck') != null) {
                if (auth()->guard('worker')->user()->warehouse->check_carriers)
                    $items->incustoms(\Request::get('incustoms'));
            } else {
                $items->incustoms(\Request::get('incustoms'));
            }
        }

        if (\Request::get('dec') == 1) {
            $items->ready();
            $items->incustoms();
        }

        if (\Request::get('dec') == 2) {
            $items->whereNull('shipping_amount_goods');
        }

        if (\Request::get('dec') == 3) {
            $items->whereNull('shipping_amount_goods');
        }

        if (\Request::get('start_date') != null) {
            $items->where('packages.created_at', '>=', \Request::get('start_date') . ' 00:00:00');
        }
        if (\Request::get('end_date') != null) {
            $items->where('packages.created_at', '<=', \Request::get('end_date') . ' 23:59:59');
        }


        $items = $items->orderBy('packages.created_at', 'desc')->orderBy('packages.id', 'desc');

        //$items = $items->latest();

        if (\request()->has('export') || (\request()->has('search_type') && \request()->get('search_type') == 'export') && !\request()->has('search')) {
            if ($items->count()) {
                $items = $items->get();
            } else {
                $items = $items->paginate($this->limit);
            }
        } else {
            $items = $items->paginate($this->limit);
        }

        return $items;
    }

    public function export($items = null)
    {
        if (request()->has('hidden_items')) {
            $items = explode(",", request()->get('hidden_items'));
        }

        $isBag = null !== \request()->get('bag') && \request()->get('bag') == 1;
        $bags = null;

        if ($isBag) {
            if (is_string($items)) {
                $bag = Bag::where('id', $items)->first();

                if ($bag) {
                    $items = $bag->packages;
                    $bags = [$bag];
                } else {
                    $items = [];
                    $bags = [];
                }
            }
        } else {
            if (is_string($items)) {
                $parcel = Parcel::with(['bags', 'packages'])->where('warehouse_id', $this->id())->where('id', $items)->first();

                if ($parcel) {
                    $items = $parcel->packages;
                    $bags = $parcel->bags;
                } else {
                    $items = [];
                    $bags = [];
                }
            }
        }

        $formats = ['Xlsx' => 'Xlsx', 'Mpdf' => 'pdf'];

        $type = isset($formats[\request()->get('format')]) ? \request()->get('format') : 'Xlsx';
        $ext = $formats[$type];

        return Excel::download(new PackagesExport($items, $bags), 'packages_' . uniqid() . '.' . $ext, $type);
    }

    public function manifest($items = null)
    {
        if (request()->has('hidden_items')) {
            $items = explode(",", request()->get('hidden_items'));
        }

        $isBag = null !== \request()->get('bag') && \request()->get('bag') == 1;


        if ($isBag) {
            if (is_string($items)) {
                $bag = Bag::where('id', $items)->first();

                if ($bag) {
                    $items = $bag->packages;
                } else {
                    $items = [];
                }
            }
        } else {
            if (is_string($items)) {
                $parcel = Parcel::where('warehouse_id', $this->id())->where('id', $items)->first();

                if ($parcel) {
                    $items = $parcel->packages;
                } else {
                    $items = [];
                }
            }
        }


        $formats = ['Xlsx' => 'xlsx', 'Mpdf' => 'pdf'];

        if ($this->me()->allow_make_fake_invoice) {
            $type = 'Xlsx';
            $ext = 'xlsx';
        } else {
            $type = isset($formats[\request()->get('format')]) ? \request()->get('format') : 'Xlsx';
            $ext = $formats[$type];
        }

        return Excel::download(new ManifestExport($items, null, $type), 'manifest_' . uniqid() . '.' . $ext, $type);
    }

    public function create()
    {
        if ($this->me()->country->code == 'ru') {
            $this->fields = $this->ru_fields;
        }
        if ($this->me()->country->code == 'uk') {
            $this->fields[7]['validation'] = 'required|string|min:9|unique:packages,tracking_code';
            $this->fields[7]['hint'] = 'Special Tracking number (required)';
            unset($this->fields[7]['attributes']['data-validation-optional']);
        }
        View::share('fields', $this->fields);
        return parent::create();
    }

    /**
     * @param $id
     * @return Model|null|static
     */
    public function editObject($id)
    {
        $countryId = $this->me()->country->id;

        if ($this->me()->country->code == 'ru') {
            $this->fields = $this->ru_fields;
        }
        if ($this->me()->country->code == 'uk') {
            $this->fields[7]['validation'] = 'required|string|min:9|unique:packages,tracking_code';
            $this->fields[7]['hint'] = 'Special Tracking number (required)';
            unset($this->fields[7]['attributes']['data-validation-optional']);
        }
        View::share('fields', $this->fields);

        return Package::where(function ($q) use ($countryId) {
            $q->where('warehouse_id', $this->id())->orWhere(function ($q) use ($countryId) {
                $q->where('country_id', $countryId)->where('status', 6);
            });
        })->whereWarehouseId($this->id())->whereId($id)->first();
    }

    /**
     * @param $id
     * @return Model|null|static
     */
    public function deleteObject($id)
    {
        return Package::whereWarehouseId($this->id())->whereId($id)->first();
    }

    /**
     * Get id
     *
     * @return mixed
     */
    public function id()
    {
        return $this->me() ? $this->me()->getAuthIdentifier() : null;
    }

    public function me()
    {
        return auth()->guard('worker')->user()->warehouse;
    }

    /**
     * Default attributes on create
     *
     * @return array
     */
    public function autoFill()
    {
        return [
            'warehouse_id' => $this->id(),
        ];
    }

    public function warehouseFill()
    {
        return [
            'warehouse_id' => $this->id(),
            'status' => 0,
        ];
    }

    public function update(Request $request, $id)
    {
        $used = $this->editObject($id);

        $data = [];

        if (trim($used->status) != trim($request->get('status'))) {
            $data['status'] = [
                'before' => trim($used->status),
                'after' => trim($request->get('status')),
            ];

            /* Send Notification */
            Notification::sendPackage($id, trim($request->get('status')));
        }

        if (!empty($data)) {
            $log = new PackageLog();
            $log->data = json_encode($data);
            $log->warehouse_id = $this->id();
            $log->package_id = $id;
            $log->save();
        }
        $res = parent::update($request, $id);
        $package = Package::find($id);
        if ($package) $package->saveGoodsFromRequest($request);

        return $res;
    }

    public function store(Request $request)
    {
        file_put_contents('/var/log/ase_error.log', date('Y-m-d H:i:s') . " Request: ".json_encode($request->all())."\n", FILE_APPEND);
        $res = parent::store($request);
        $id = $this->itemId;
        if (!empty($id)) {
            $package = Package::find($id);
            if ($package) $package->saveGoodsFromRequest($request);
        }
        return $res;
    }

    public function ajax(Request $request, $id)
    {
        if ($request->get('name') == 'status') {
            $used = $this->editObject($id);

            $data = [];

            if (trim($used->status) != trim($request->get('value'))) {
                $data['status'] = [
                    'before' => trim($used->status),
                    'after' => trim($request->get('value')),
                ];

                /* Send Notification */
                Notification::sendPackage($id, trim($request->get('value')));
            }

            if (!empty($data)) {
                $log = new PackageLog();
                $log->data = json_encode($data);
                $log->warehouse_id = $this->id();
                $log->package_id = $id;
                $log->save();
            }
        }

        return parent::ajax($request, $id);
    }

    public function modal($id)
    {
        $item = Package::with(['user', 'warehouse', 'country'])->whereWarehouseId($this->id())->find($id);
        $shipper = $item->warehouse_id ? $item->warehouse : ($item->country ? $item->country->warehouse : null);

        if (!$item) {
            return null;
        }

        return view('warehouse.widgets.modal', compact('item', 'shipper'));
    }

    function utExport($id)
    {
        $ut = new UniTradeModel();
        $zipFile = $ut->getParcelZip($id);
        if (!empty($zipFile)) {
            return response()->download($zipFile)->deleteFileAfterSend(true);
        }
    }

    public function barcodeScan($code)
    {

        //$code
        try {
	    $check_carriers=auth()->guard('worker')->user()->warehouse->check_carriers;

            $checkDraftLabel = 0;
            if (request()->has('cdfl') && (int)request()->get('cdfl') == 1)
                $checkDraftLabel = 1;

            $id = $this->id();
            $parcel = null;
            // Check barcode
	    $item=NULL;
	    $w_id = auth()->guard('worker')->user()->warehouse->w_id;
	    if($w_id) {
	        $item = Package::whereRaw("((tracking_code like '%" . $code . "%') or (custom_id='".$code."'))");
	        $item = $item->whereRaw("(warehouse_id=".$id." or warehouse_id=".$w_id." or warehouse_id is null)");
	        $item = $item->first();
	    } else {
                $item = Package::where(function ($query) use ($code) {
                    $query->where("tracking_code", "like", "%" . $code . "%")->orWhere("custom_id", $code);
                })->where(function ($query) use ($id) {
                    $query->where("warehouse_id", $id)->orWhereNull("warehouse_id");
	        })->first();
	    }



            if ($this->me()->country->code != 'ru' && $this->me()->country->code != 'tr') {

                $len = strlen($code) + 1;
                if (!$item && $len > 10) {
                    $item = Package::whereRaw("length(tracking_code)>=8 and instr('" . $code . "',tracking_code)=greatest(" . $len . "-length(tracking_code),10) and (warehouse_id=" . $id . " or warehouse_id is null)")->first();

                }

                if (!$item && strlen($code) >= 10) {
                    $start = -1 * strlen($code) + 1;
                    $cnt = 0;
                    for ($i = $start; $i <= -8; $i++) {
                        $code = substr($code, $i);
			if($w_id) {
	    		    $item = Package::whereRaw("((tracking_code like '%" . $code . "%') or (custom_id='".$code."'))");
	    		    $item = $item->whereRaw("(warehouse_id=".$id." or warehouse_id=".$w_id." or warehouse_id is null)");
            		    $item = $item->whereIn('status', [0, 6]);
	    		    $item = $item->first();
			} else {
                            $item = Package::where(function ($query) use ($code) {
                                $query->where("tracking_code", "like", "%" . $code . "%")->orWhere("custom_id", $code);
                            })->where(function ($query) use ($id) {
                                $query->where("warehouse_id", $id)->orWhereNull("warehouse_id");
			    })->whereIn('status', [0, 6])->first();
			}

                        if ($item) {
                            break;
                        }
                        $cnt++;
                        if ($cnt >= 8) break;
                    }
                }
            }

            $bag_id = 0;
            if (request()->has('scan'))
                $bag_id = (int)request()->get('scan');

            if ($item) {
                if ($id == 11 && $item->ukr_express_id) {
                    $ldate = date('Y-m-d H:i:s');
                    $message = "";
                    if ($item->tracking_code)
                        $message .= $item->tracking_code;
                    file_put_contents('/var/log/ase_uexpress2_partner.log', $ldate . " " . $message . "\n", FILE_APPEND);
                    return response()->json([
                        'error' => 'NEW SYSTEM', 'new_system' => true
                    ]);
                }

                if ($bag_id > 1) {
                    $bag = Bag::where('id', $bag_id)->first();
                    if (!$bag) {
                        return response()->json([
                            'error' => 'Bag not exists',
                        ]);
                    }
                    /*$checkBag = \DB::table('bag_package')->whereNotNull('bag_id')->where('package_id', $item->id)->first();
                    if ($checkBag) {
                        return response()->json([
                            'error' => 'Package has been already added to another bag!',
                        ]);
		    }*/
                    $parcel = Parcel::where("id", $bag->parcel_id)->where('sent', 0)->first();
                    if (!$parcel) {
                        return response()->json([
                            'error' => 'Parcel already was sent, Please create new parcel for adding the package!',
                        ]);
                    }
                    $checkParcel = \DB::table('parcel_package')->whereNotNull('parcel_id')->where('package_id', $item->id)->first();
                    if ($checkParcel) {
                        return response()->json([
                            'error' => 'Package has been already added to another parcel!',
                        ]);
                    }
                }

                if (!in_array($item->status, [0, 6])) {
                    return response()->json([
                        'error' => "You cannot add the package due to status!",
                    ]);
                }
                $status = $item->status;

                $item->warehouse_id = $this->id();
                if ($status == 6) {
                    $item->status = 0;
                }


                if (empty($item->user_id) && auth()->guard('worker')->user()->warehouse->id == 11 && ($item->unknown_status > 0)) {
                    $unknownMessage = config('ase.attributes.package.unknown_status_full')[$item->unknown_status];
                    $item->unknown_status_at = date('Y-m-d H:i:s');
                    $item->save();
                    return response()->json([
                        'error' => $unknownMessage,
                    ]);
                }

                $weight = $item->getWeight();
                if ($bag_id <= 1 && auth()->guard('worker')->user()->warehouse->only_weight_input && !$weight) {
                    return response()->json([
                        'add_package' => true,
                        'user' => isset($item->user_id) ? 'yes' : 'no',
                        'cwb' => $item->custom_id,
                    ]);
                }

                $item->save();

                if (!($checkDraftLabel && $bag_id <= 1) && !($item->is_ready  && (!$check_carriers || ($check_carriers && $item->is_in_customs))) && ($bag_id > 1 || ($bag_id <= 1 && auth()->guard('worker')->user()->warehouse->auto_print_pp_invoice))) {
                    return response()->json([
                        'error' => "Package is not ready. Please hold the package!",
                    ]);
                }
                if (!($checkDraftLabel && $bag_id <= 1) && !$item->fake_invoice && ($bag_id > 1 || ($bag_id <= 1 && auth()->guard('worker')->user()->warehouse->auto_print_pp_invoice))) {
                    return response()->json([
                        'error' => "Package has no invoice. Please hold the package!",
                    ]);
                }


                //$warehouse = Warehouse::find($item->warehouse_id);
                //if (isset($warehouse->country->code) && $warehouse->country->code == 'us') {
                if (!($checkDraftLabel && $bag_id <= 1)) {
                    $packageCarrier = PackageCarrier::where('package_id', $item->id)->first();
                    if (($bag_id > 1) && auth()->guard('worker')->user()->warehouse->check_carriers && (!$packageCarrier || ($packageCarrier && $packageCarrier->check_customs))) {
                        if (!$packageCarrier || !isset($packageCarrier->code) || (($packageCarrier->code != 200) && ($packageCarrier->code != 400))) {
                            return response()->json([
                                'error' => "Package is not in Smart Customs system. Please hold the package!",
                            ]);
                        }
                        if (!isset($packageCarrier->ecoM_REGNUMBER) || empty($packageCarrier->ecoM_REGNUMBER)) {
                            $cm = new CustomsModel();
                            $cm->retryCount = 7;
                            $cm->retrySleep = 0;
                            $cm->pinNumber = $packageCarrier->fin;
                            $cm->isCommercial = $packageCarrier->is_commercial;
                            $cm->trackingNumber = $item->custom_id;
                            $cpost = $cm->get_carrierposts2();
                            if ($cpost->code != 200) {
                                return response()->json([
                                    'error' => "Cannot get request from Smart Customs system.",
                                ]);
                            }

                            if (empty($cpost->inserT_DATE)) {
                                return response()->json([
                                    'error' => "Package is not in Smart Customs system. Please hold the package!",
                                ]);
                            }

                            $ldate = date('Y-m-d H:i:s');
                            $cm->updateDB($packageCarrier->package_id, $packageCarrier->fin, $item->custom_id, $ldate, $cpost);

                            if (!$packageCarrier->is_commercial && (!isset($cpost->ecoM_REGNUMBER) || empty($cpost->ecoM_REGNUMBER))) {
                                return response()->json([
                                    'error' => "Package has no Declaration in Smart Customs system. Please hold the package!",
                                ]);
                            }
                        }
                    }


                    /* Send Notification */
                    if ($status == 6) {
                        Notification::sendPackage($item->id, '0');
                    }

                } // !Draft label

                if (request()->has('scan')) {

                    // Check limitation
                    if ($bag_id > 1) {
                        $limitWeight = $this->me()->limit_weight;
                        $limitAmount = $this->me()->limit_amount;
                        $limitCurrency = $this->me()->limit_currency;
                        if ($limitAmount || $limitWeight) {
                            $limitShippingAmount = $limitAmount / getCurrencyRate($limitCurrency);
                            $totalShipInvoice = 0;
                            $totalWeight = 0;

                            foreach ($parcel->packages()->get() as $_package) {
                                $totalShipInvoice += $_package->getShippingAmountUSD();
                                $totalWeight += $_package->weight_goods;
                            }

                            $totalShipInvoice += $item->getShippingAmountUSD();
                            $totalWeight += $item->weight_goods;

                            if ($limitAmount && $limitShippingAmount < $totalShipInvoice) {
                                return response()->json([
                                    'error' => 'Parcel pass the ' . $limitAmount . config('ase.attributes.currencies')[$limitCurrency] . " limitation!",
                                ]);
                            }

                            if ($limitWeight && $limitWeight < $totalWeight) {
                                return response()->json([
                                    'error' => 'Parcel pass the ' . $limitWeight . "kg limitation!",
                                ]);
                            }
                        }
                        /* Attach new package to the parcel */
                        $packageCarrier = PackageCarrier::where('package_id', $item->id)->first();
                        if ($packageCarrier && $packageCarrier->is_commercial) {
                            $cm = new CustomsModel();
                            $cm->voen = $item->user->voen;
                            $cm->trackingNumber = $packageCarrier->trackingNumber;
                            $cm->isCommercial = $packageCarrier->is_commercial;
                            $cm->airwaybill = $parcel->custom_id;
                            $cm->depesH_NUMBER = $bag->custom_id;
                            sleep(1);
                            $res = $cm->update_carriers();
                            /*if(!isset($res->code))
                            {
                                            return response()->json([
                                                'error' => 'Cannot update package in customs system (Empty response)',
                                            ]);
                            }
                            if($res->code!=200)
                            {
                                            return response()->json([
                                                'error' => 'Cannot update package in customs system ('.$cm->errorStr.')',
                                            ]);
                            }*/
                        }
                        DB::delete("delete from bag_package where package_id=" . $item->id);
                        $bag->packages()->attach($item->id);
                        $parcel->packages()->attach($item->id);
                    }

                    unset($this->list['parcel']);
                    unset($this->list['bag']);

                    //if($bag_id<=1)
                    $item->processed_at = date('Y-m-d H:i:s');
                    $item->save();

                    if ($bag_id > 1)
                        return response()->json([
                            'id' => $item->id,
                            'is_ready' => ($item->is_ready && (!$check_carriers || ($check_carriers && $item->is_in_customs))),
                            'label' => ($item->user_id && isset($item->warehouse->address)) ? route('w-packages.pdf_label', $item->id) : null,
                            'invoice' => $item->fake_invoice ? asset($item->fake_invoice) : null,
                            'html' => view('warehouse.widgets.single-package')->with([
                                'extraActions' => $this->extraActions,
                                'item' => $item,
                                '_list' => $this->list,
                                '_view' => $this->view,
                            ])->render(),
                        ]);
                    else
                        return response()->json([
                            'id' => $item->id,
                            'is_ready' =>  ($item->is_ready && (!$check_carriers || ($check_carriers && $item->is_in_customs))),
                            'label' => ($item->user_id && isset($item->warehouse->address)) ? route('w-packages.pdf_label', $item->id) : null,
                            'invoice' => $item->fake_invoice ? asset($item->fake_invoice) : null,
                            'html' => view('warehouse.widgets.processed-package')->with([
                                'extraActions' => $this->extraActions,
                                'item' => $item,
                                'listProcessed' => $this->listProcessed,
                                '_view' => $this->view,
                            ])->render(),
                        ]);
                } else {
                    return response()->json([
                        'is_ready' =>  ($item->is_ready &&  (!$check_carriers || ($check_carriers && $item->is_in_customs))),
                        'label' => ($item->user_id && isset($item->warehouse->address)) ? route('w-packages.pdf_label', $item->id) : null,
                        'invoice' => $item->fake_invoice ? asset($item->fake_invoice) : null,
                        //'invoice' => null,
                        /*'package' => view('warehouse.widgets.modal-package')->with([
                            'item'  => $item,
                        ])->render(),*/
                    ]);
                }
            } else {
                if ($bag_id > 1) {
                    return response()->json([
                        //'error' => 'Package does not exist or not ready!',
                        'error' => 'Package does not exist!',
                    ]);
                }

                if (auth()->guard('worker')->user()->warehouse->only_weight_input) {
                    return response()->json([
                        'add_package' => true,
                        'user' => 'no',
                    ]);
                } else {
                    return response()->json([
                        //'error' => 'Package does not exist or not ready!',
                        'error' => 'Package does not exist!',
                    ]);
                }
            }
        } catch (Exception $exception) {
            Bugsnag::notifyException($exception);
	    file_put_contents('/var/log/ase_error.log', date('Y-m-d H:i:s') . " Exception: ".$exception->__toString()."\n", FILE_APPEND);
            return response()->json([
                'error' => 'The system is busy right now. Please try again!',
            ]);
        }
    }

    public function addPackage(Request $request, $id)
    {
        if ($this->me()->country->code == 'ru') {
            $this->fields = $this->ru_fields;
        }
        try {
            if ($id > 1) {
                $bag = Bag::where('id', $id)->first();
                if (!$bag) {
                    return response()->json([
                        'error' => 'Bag not exists',
                    ]);
                }
                $parcel = Parcel::where("id", $bag->parcel_id)->where('sent', 0)->first();
                if (!$parcel) {
                    return response()->json([
                        'error' => 'Parcel already was sent, Please create new parcel for adding the package!',
                    ]);
                }
            }

            $code = \request()->get('tracking_code');
            $code1 = $code;
            $warehouseId = $this->me()->id;
            if ($code1 != null) {

            /*    $item = Package::where(function ($query) use ($code1) {
                    $query->where("tracking_code", "like", "%" . $code1 . "%")->orWhere("custom_id", $code1);
                })->where(function ($query) use ($warehouseId) {
                    $query->where("warehouse_id", $warehouseId)->orWhereNull("warehouse_id");
		})->whereIn('status', [0, 6])->first();*/
	    $item=NULL;
	    $w_id =  $this->me()->w_id;
	    if($w_id) {
	        $item = Package::whereRaw("((tracking_code like '%" . $code1 . "%') or (custom_id='".$code1."'))");
	        $item = $item->whereRaw("(warehouse_id=".$warehouseId." or warehouse_id=".$w_id." or warehouse_id is null)");
		$item = $item->whereIn('status', [0, 6]);
	        $item = $item->first();
	    } else { 
                $item = Package::where(function ($query) use ($code1) {
                    $query->where("tracking_code", "like", "%" . $code1 . "%")->orWhere("custom_id", $code1);
                })->where(function ($query) use ($warehouseId) {
                    $query->where("warehouse_id", $warehouseId)->orWhereNull("warehouse_id");
                })->whereIn('status', [0, 6])->first();
	    }
	    if($item)


                if ($this->me()->country->code != 'ru' && $this->me()->country->code != 'tr') {

                    $len = strlen($code1) + 1;
                    if (!$item && $len > 10) {
			if($w_id) {
                            $item = Package::whereRaw("length(tracking_code)>=8 and instr('" . $code1 . "',tracking_code)=greatest(" . $len . "-length(tracking_code),10) and (warehouse_id=" . $id . " or warehouse_id is null or warehouse_id=".$w_id.") and status in (0,6)")->first();
			} else {
                            $item = Package::whereRaw("length(tracking_code)>=8 and instr('" . $code1 . "',tracking_code)=greatest(" . $len . "-length(tracking_code),10) and (warehouse_id=" . $id . " or warehouse_id is null) and status in (0,6)")->first();
			}
                    }

                    if (!$item && strlen($code1) >= 10) {
                        $start = -1 * strlen($code1) + 1;
                        for ($i = $start; $i <= -8; $i++) {
                            $code1 = substr($code1, $i);
                            /*$item = Package::where(function ($query) use ($code1) {
                                $query->where("tracking_code", "like", "%" . $code1 . "%")->orWhere("custom_id", $code1);
                            })->where(function ($query) use ($warehouseId) {
                                $query->where("warehouse_id", $warehouseId)->orWhereNull("warehouse_id");
			    })->whereIn('status', [0, 6])->first();*/
	    		    if($w_id) {
	        		$item = Package::whereRaw("((tracking_code like '%" . $code1 . "%') or (custom_id='".$code1."'))");
	        		$item = $item->whereRaw("(warehouse_id=".$warehouseId." or warehouse_id=".$w_id." or warehouse_id is null)");
				$item = $item->whereIn('status', [0, 6]);
	        		$item = $item->first();
	    		    } else {
                		$item = Package::where(function ($query) use ($code1) {
                    		    $query->where("tracking_code", "like", "%" . $code1 . "%")->orWhere("custom_id", $code1);
                		})->where(function ($query) use ($warehouseId) {
                    		    $query->where("warehouse_id", $warehouseId)->orWhereNull("warehouse_id");
                		})->whereIn('status', [0, 6])->first();
			    }

                            if ($item) {
                                break;
                            }
                        }
                    }
                }
            } else {
                $item = null;
            }

            $request->request->add(['only_id' => 'yes']);

            if (starts_with($code, "ASE")) {
                $request->merge(['tracking_code' => null]);
            }
            //dd($request);

            if (auth()->guard('worker')->user()->warehouse->allow_make_fake_invoice
                ||
                (auth()->guard('worker')->user()->warehouse->only_weight_input && !auth()->guard('worker')->user()->warehouse->allow_make_fake_invoice)
            ) {
                if (auth()->guard('worker')->user()->warehouse->allow_make_fake_invoice) {

                    $detailedType = [];
                    $amount = 0;

                    if ($request->has('types') && $request->get('types') != null && !empty($request->get('types') && isset($request->get('types')[0]))) {
                        foreach ($request->get('types') as $key => $type) {
                            $amm = $request->get('items')[$key];
                            $amount += $amm;
                            //$typeName = $type;
                            $typeName = (PackageType::find($type))->translateOrDefault('en')->name;

                            $detailedType[] = $amm . " x " . $typeName;
                        }
                        if ($detailedType) {
                            $request->request->add(['number_items' => $amount]);
                            $request->request->remove('types');
                            $request->request->remove('items');
                            $request->request->add(['detailed_type' => implode("; ", $detailedType)]);
                        }
                    }
                } else {
                    if ($warehouseId == 2)
                        $request->request->add(['shipping_amount_cur' => 3]);
                }

                if ($item) {
		    if($item->warehouse_id != $this->id()) {
			$item->warehouse_id=$this->id();
			$item->save();
		    }
                    if ($item->user_id) {
                        $remove = [
                            'user_id',
                            'website_name',
                            'shipping_amount',
                            'shipping_amount_cur',
                            'warehouse_comment',
                            'detailed_type',
                            'number_items',
                            'tracking_code',
                            'type_id',
                        ];
                        foreach ($remove as $removeIt) {
                            $request->merge([$removeIt => $item->{$removeIt}]);
                        }
                    }

                    //dd($item->user_id, $request->all());

                    $this->update($request, $item->id);
                    $itemId = $item->id;
                } else {
                    $itemId = $this->store($request);
                }
            } else {
                $itemId = 0;
            }

            $item = Package::find($itemId);
            if ($item) $item->saveGoodsFromRequest($request);
            //dump($itemId, $item);
            /* Send Notification */
            if ($item->status == 6) {
                $rt = 1;
                $item->status = 0;
		$item->warehouse_id=$this->id();
                $item->save();
            }
            Notification::sendPackage($item->id, '0');

            if ($id > 1) {
                $parcel = \DB::table('parcel_package')->whereNotNull('parcel_id')->where('package_id', $item->id)->first();
                if ($parcel) {
                    return response()->json([
                        'error' => 'Package has been already added to another parcel!',
                    ]);
                }
                $bag = \DB::table('bag_package')->whereNotNull('bag_id')->where('package_id', $item->id)->first();
                if ($bag) {
                    return response()->json([
                        'error' => 'Package has been already added to another bag!',
                    ]);
                }
            }

            return response()->json([
                'cwb' => $item->custom_id,
                'web_site' => auth()->guard('worker')->user()->warehouse->web_site,
            ]);
        } catch (Exception $exception) {
            Bugsnag::notifyException($exception);
	    file_put_contents('/var/log/ase_error.log', date('Y-m-d H:i:s') . " Exception: ".$exception->__toString()."\n", FILE_APPEND);
            return response()->json([
                'error' => 'The system is busy right now. Please try again!',
            ]);
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

    public function users()
    {

        $q = request()->get('q') != null ? request()->get('q') : request()->get('term');

        $users = User::where(function ($query) use ($q) {
            $query->where("customer_id", "LIKE", "%" . $q . "%")->orWhere(DB::raw('concat(trim(name)," ",trim(surname))'), 'LIKE', "%" . $q . "%");
        })->take(15)->get();
        $data = [];

        foreach ($users as $user) {
            $data[] = ["id" => $user->id, "text" => $user->full_name . " (" . $user->customer_id . ")"];
        }

        return \GuzzleHttp\json_encode(["results" => $data]);
    }

    public function aseLogic()
    {

    }
}
