<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\Customer;
use Request;
use Excel;
use App\Exports\Admin\CustomersExport;
use App\Models\Activity;

class CustomerController extends Controller
{
    protected $can = [
        'export' => true,
    ];

    protected $view = [
        'name' => "Customer",
        'search' => [
            [
                'name' => 'q',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Search...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
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
                'type' => 'select2',
                'name' => 'city_id',
                'attribute' => 'name',
                'model' => 'App\Models\City',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All Cities',
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
        ],
    ];

    protected $list = [
        'partnerWithLabel' => [
            'label' => 'Partner',
        ],
        'fin',
        'fullname',
        'courier.name' => [
            'label' => 'Courier',
        ],
        'city.name' => [
            'label' => 'city'
        ],
        'phone',
        'email',
        'address',


        'created_at' => [
            'label' => 'CreatedAt',
            'order' => 'created_at',
        ],
    ];

    protected $fields = [
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
            'name' => 'fullname',
            'label' => 'Full name',
            'type' => 'text',
            'validation' => 'required|string',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
        ],
        [
            'name' => 'phone',
            'label' => 'Phone',
            'type' => 'text',
            'validation' => 'required|string',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
        ],
        [
            'name' => 'email',
            'label' => 'Email',
            'type' => 'email',
            'validation' => 'nullable|email',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
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
                'class' => 'col-md-2',
            ],
        ],
        [
            'label' => 'City',
            'type' => 'select2',
            'name' => 'city_id',
            'attribute' => 'name',
            'model' => 'App\Models\City',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|integer',
            //'allowNull'         => true,
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10 clearfix"></div>',
        ],
        [
            'name' => 'address',
            'label' => 'Address',
            'type' => 'textarea',
            'validation' => 'required|string',
            'attributes' => [
                'rows' => 8,
            ],
        ],
        [
            'label' => 'Courier',
            'type' => 'select2',
            'name' => 'courier_id',
            'attribute' => 'name',
            'model' => 'App\Models\Courier',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'nullable|integer',
            'allowNull'         => true,
        ],
    ];

    protected $extraActions = [
        [
            'route' => 'customers.logs',
            'key' => 'id',
            'label' => 'Logs',
            'icon' => 'list',
            'color' => 'default',
            'target' => '_blank',
        ],
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function export($items = null)
    {
        $formats = ['Xlsx' => 'Xlsx', 'Mpdf' => 'pdf'];
        $type = isset($formats[\request()->get('format')]) ? \request()->get('format') : 'Xlsx';
        $ext = $formats[$type];

        return Excel::download(new CustomersExport($items), 'customers_' . uniqid() . '.' . $ext, $type);
    }

    public function indexObject()
    {
        $items = Customer::with([]);
        if (Request::get('q') != null) {
            $q = str_replace('"', '', Request::get('q'));
            $items->where(function ($query) use ($q) {
                $query->orWhere("fin", "LIKE", "%" . $q . "%")->orWhere("fullname", "LIKE", "%" . $q . "%")->orWhere("address", "LIKE", "%" . $q . "%")->orWhere("phone", "LIKE", "%" . $q . "%")->orWhere("email", "LIKE", "%" . $q . "%");
            });
        }

        if (Request::get('start_date') != null) {
            $dateField = Request::get('date_by', 'created_at');
            $dateField = 'customers.' . $dateField;
            $items->where($dateField, '>=', Request::get('start_date') . " 00:00:00");
        }
        if (Request::get('end_date') != null) {
            $dateField = Request::get('date_by', 'created_at');
            $dateField = 'customers.' . $dateField;
            $items->where($dateField, '<=', Request::get('end_date') . " 23:59:59");
        }

        if (Request::get('city_id') != null) {
            $items->where('customers.city_id', Request::get('city_id'));
        }

        if (\Request::get('partner_id') != null) {
            $items->where('customers.partner_id', \Request::get('partner_id'));
        }

        if (\request()->get('search_type') == 'export' || \request()->has('export')) {
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

    public function logs($id)
    {
        $logs = Activity::where('content_id', $id)->where('content_type', Customer::class)->orderBy('id', 'desc')->get();
        if (!$logs) {
            return back();
        }

        return view('admin.widgets.logs', compact('logs', 'id'));
    }

//    public function ajax(Request $request, $id)
//    {
//        $customer = Customer::find($id);
//        if ($request->get('name') == 'courier.courier_id') {
//
//
//            $customer->courier_id = $request->get('courier_id');
//            $customer->save();
//
//            return;
//        }
//        return parent::ajax($request, $id);
//    }
}
