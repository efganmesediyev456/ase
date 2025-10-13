<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Models\Package;
use App\Models\PackageLog;
use Auth;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Validator;

class DoneController extends Controller
{
    protected $modelName = 'Package';

    protected $view = [
        'formColumns' => 10,
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
                'type' => 'select2',
                'name' => 'warehouse_id',
                'attribute' => 'company_name,country.name',
                'model' => 'App\Models\Warehouse',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'All warehouses',
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
        ],
    ];

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
            'route' => 'packages.label',
            'key' => 'id',
            'label' => 'Label',
            'icon' => 'windows2',
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
        'custom_id' => [
            'label' => 'CWB #',
        ],
        'tracking_code' => [
            'label' => 'Track #',
        ],
        'user' => [
            'type' => 'custom.user',
            'label' => 'User',
        ],
        'warehouse.country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
        'weight_with_type' => [
            'label' => 'Weight',
        ],
        'number_items_goods' => [
            'label' => 'Items',
        ],
        'shipping_org_price' => [
            'label' => 'Invoice Price',
        ],
        'merged_delivery_price' => [
            'label' => 'Delivery Price',
        ],
        'discount_percent_with_label' => [
            'label' => 'Discount',
        ],
        'merged_delivery_price_discount' => [
            'label' => 'Delivery Price (with discount)',
        ],
        'total_price_with_label' => [
            'label' => 'Declared Value',
        ],
        'show_label_with_label' => [
            'label' => 'Show Label',
        ],
        'status' => [
            'label' => 'Status',
        ],
        'created_at' => [
            'label' => 'At',
            'type' => 'date',
        ],
    ];

    protected $fields = [
        [
            'label' => 'WareHouse',
            'type' => 'select2',
            'name' => 'warehouse_id',
            'attribute' => 'company_name,country.name',
            'model' => 'App\Models\Warehouse',
            'wrapperAttributes' => [
                'class' => ' col-md-6',
            ],
            'validation' => 'nullable|integer',
            'allowNull' => true,
        ],
        [
            'label' => 'User',
            'type' => 'select2',
            'name' => 'user_id',
            'attribute' => 'full_name,customer_id',
            'model' => 'App\Models\User',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
            ],
            'validation' => 'nullable|integer',
            'allowNull' => true,
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
        ],
        [
            'name' => 'tracking_code',
            'label' => 'Tracking Code',
            'type' => 'text',
            'hint' => 'Special Tracking number',
            'prefix' => '<i class="icon-truck"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
            ],
            'validation' => 'nullable|required_without_all:website_name,custom_id|string',
        ],
        [
            'name' => 'status',
            'label' => 'Status',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.package.status',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'paid',
            'label' => 'Paid',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.package.paid',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'name' => 'custom_id',
            'label' => 'CWB Number',
            'type' => 'text',
            'hint' => 'Special CWB number',
            'prefix' => '<i class="icon-check"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-5',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'website_name',
            'label' => 'WebSite name',
            'type' => 'text',
            'hint' => 'Also accept url',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'nullable|required_without_all:tracking_code,custom_id|string',
        ],
        [
            'label' => 'Type',
            'type' => 'select2',
            'name' => 'type_id',
            'attribute' => 'name',
            'model' => 'App\Models\PackageType',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'allowNull' => true,
            'validation' => 'nullable|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12"><h3 class="text-center">Parameters</h3></div>',
        ],
        [
            'name' => 'weight',
            'label' => 'Weight',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'required|numeric',
        ],
        [
            'name' => 'weight_type',
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.weight',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'delivery_price',
            'label' => 'Delivery Price',
            'type' => 'text',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
            ],
            'validation' => 'nullable|numeric',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'name' => 'width',
            'label' => 'Width',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|numeric',
        ],
        [
            'name' => 'height',
            'label' => 'Height',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|numeric',
        ],
        [
            'name' => 'length',
            'label' => 'Length',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|numeric',
        ],
        [
            'name' => 'length_type',
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.length',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12"><h3 class="text-center">Shipping</h3></div>',
        ],

        [
            'name' => 'number_items',
            'label' => 'Number Items',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'shipping_amount',
            'label' => 'Shipping amount',
            'type' => 'text',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'nullable|numeric',
        ],
        [
            'name' => 'shipping_amount_cur',
            'label' => '&nbsp',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.currencies',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'name' => 'show_label',
            'label' => 'Show label for warehouse',
            'type' => 'checkbox',
            'wrapperAttributes' => [
                'class' => 'col-md-3 mt-15',
            ],
            'validation' => 'nullable|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12"><h3 class="text-center">Comments</h3></div>',
        ],
        [
            'name' => 'admin_comment',
            'label' => 'Admin Comment',
            'type' => 'textarea',
            'prefix' => '<i class="icon-user-tie"></i>',
            'validation' => 'nullable|string',
        ],

        [
            'name' => 'user_comment',
            'label' => 'User Comment',
            'type' => 'textarea',
            'prefix' => '<i class="icon-user"></i>',
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ],

        [
            'name' => 'warehouse_comment',
            'label' => 'Warehouse Comment',
            'type' => 'textarea',
            'prefix' => '<i class="icon-office"></i>',
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ],

        [
            'type' => 'html',
            'html' => '<div class="form-group mt-10 col-lg-12"><h3 class="text-center">Attachments</h3></div>',
        ],

        [
            'name' => 'invoice',
            'label' => 'Invoice',
            'type' => 'file',
            'wrapperAttributes' => [
                'class' => 'col-md-6',
            ],
            'validation' => 'nullable|mimes:jpeg,jpg,png,gif,svg,pdf,doc,docx,csv,xls',
        ],

    ];

    protected $with = ['type', 'warehouse', 'user', 'logs'];

    public function label($id)
    {
        $item = Package::with(['user', 'warehouse'])->find($id);
        if (!$item) {
            abort(404);
        }
        $item->updateCarrier();

        return view('admin.widgets.label', compact('item'));
    }

    public function logs($id)
    {
        $logs = PackageLog::with([
            'package',
            'warehouse',
            'admin',
        ])->where('package_id', $id)->orderBy('id', 'desc')->get();
        if (!$logs) {
            return back();
        }

        return view('admin.widgets.logs', compact('logs', 'id'));
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

        $items = Package::where('status', 3)->latest();

        /* Filter cities */
        $cities = auth()->guard('admin')->user()->cities->pluck('id')->all();
        if ($cities) {
            $items->whereHas('user', function (
                $query
            ) use ($cities) {
                $query->whereIn('city_id', $cities)->orWhere('city_id', null);
            });
        }

        if (\Request::get('q') != null) {
            $q = \Request::get('q');
            $items->where(function ($query) use ($q) {
                $query->orWhere("tracking_code", "LIKE", "%" . $q . "%")
                    ->orWhere("website_name", "LIKE", "%" . $q . "%")
                    ->orWhere("custom_id", "LIKE", "%" . $q . "%")->orWhereHas('user', function (
                        $query
                    ) use ($q) {
                        $query->where('customer_id', 'LIKE', '%' . $q . '%');
                    });
            });
        }

        if (\Request::get('warehouse_id') != null) {
            $items->where('warehouse_id', \Request::get('warehouse_id'));
        }

        if (\Request::get('start_date') != null) {
            $items->where('created_at', '>=', \Request::get('start_date') . " 00:00:00");
        }
        if (\Request::get('end_date') != null) {
            $items->where('created_at', '<=', \Request::get('end_date') . " 23:59:59");
        }

        $items = $items->paginate($this->limit);

        return $items;
    }

    public function ajax(Request $request, $id)
    {
        if ($request->get('name') == 'status') {
            $used = Package::find($id);

            $data = [];

            if (trim($used->status) != trim($request->get('value'))) {
                $data['status'] = [
                    'before' => trim($used->status),
                    'after' => trim($request->get('value')),
                ];
            }

            if (!empty($data)) {
                $log = new PackageLog();
                $log->data = json_encode($data);
                $log->admin_id = Auth::guard('admin')->user()->id;
                $log->package_id = $id;
                $log->save();
            }
        }

        return parent::ajax($request, $id);
    }
}
