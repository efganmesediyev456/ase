<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Exports\Admin\PortmanatExport;
use App\Exports\Admin\TransactionsExport;
use App\Exports\Admin\Transactions90Export;
use App\Http\Requests;
use App\Models\Transaction;
use Excel;
use Illuminate\Support\Facades\DB;
use Request;
use Validator;
use function request;

class TransactionController extends Controller
{
    protected $can = [
        'export' => true,
    ];
    protected $notificationKey = 'id';

    protected $view = [
        'sub_title' => 'system transactions',
        'formColumns' => 14,
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
                'name' => 'admin_id',
                'attribute' => 'name',
                'model' => 'App\Models\Admin',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'All Admins',
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
                'name' => 'paid_for',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.transaction.paid_for',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'For',
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
                'name' => 'type',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.transaction.types',
                'wrapperAttributes' => [
                    'class' => 'col-lg-1',
                ],
                'allowNull' => 'Type',
            ],
            [
                'name' => 'transaction_type',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.transaction.transaction_type',
                'wrapperAttributes' => [
                    'class' => 'col-lg-1',
                ],
                'allowNull' => 'Debt By',
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
                'name' => 'pe',
                'label' => 'Portmanat Excel Export',
                'type' => 'checkbox',
                'default' => 0,
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
            [
                'name' => '9e',
                'label' => '90% USD Excel Export',
                'type' => 'checkbox',
                'default' => 0,
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
            ],
        ],
    ];

    protected $list = [
        'id'=>[
            'label' => 'id',
        ],
        'admin.name' => [
            'label' => 'Admin',
        ],
        'user' => [
            'type' => 'custom.user',
            'label' => 'User',
        ],
        'customer' => [
            'label' => 'Customer',
            'type' => 'text',
        ],
        'courier_name' => [
            'label' => 'Courier',
        ],
        'city.name' => [
            'label' => 'City',
        ],
        'phone' => [
            'label' => 'Phone',
        ],
        'type' => [
            'label' => 'Type',
        ],
        'debt' => [
            'type' => 'boolean',
        ],
        'symbol_amount' => [
            'label' => 'Amount',
        ],
        'symbol_amount_90' => [
            'label' => 'Amount 90%',
        ],
        'warehouse.country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
        'awb' => [
            'label' => 'AWB',
            'type' => 'text',
        ],
        'cwb' => [
            'label' => 'CWB',
            'type' => 'text',
        ],
        'paid_for' => ['label' => 'For'],
        'paid_by' => ['label' => 'By'],
//        'paid_by' => [
//            'type' => 'custom.transaction-paid-by',
//            'label' => 'By',
//            'editable' => [
//                'route' => 'transactions.ajax',
//                'type' => 'select',
//                'sourceFromConfig' => 'ase.attributes.transaction.paid_by',
//            ],
//        ],
        'source_id' => [
            'label' => 'Kapital ID',
        ],
        'created_at' => [
            'label' => 'At',
        ],
        //'type',
    ];

    protected $fields = [
        [
            'label' => 'User',
            'type' => 'select2',
            'name' => 'user_id',
            'attribute' => 'full_name,customer_id',
            'model' => 'App\Models\User',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'nullable|integer',
            'allowNull' => true,
        ],
        [
            'name' => 'amount',
            'label' => 'Amount',
            'type' => 'text',
            'validation' => 'required|numeric',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
        ],

        [
            'name' => 'type',
            'label' => 'Type',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.transaction.types',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'required',
        ],

        [
            'name' => 'paid_for',
            'label' => 'For',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.transaction.paid_for',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'required',
        ],

        [
            'name' => 'paid_by',
            'label' => 'By',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.transaction.paid_by',
            'wrapperAttributes' => [
                'class' => 'col-md-2',
            ],
            'validation' => 'required',
        ],

    ];


    public function indexObject()
    {
        $validator = Validator::make(request()->all(), [
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

        $items = Transaction::where('type','OUT')->latest();

        $prefix = "%";
        $condition = "LIKE";


        /* Filter cities */
        $cities = auth()->guard('admin')->user()->cities->pluck('id')->all();
        if ($cities) {
            $items->where(function (
                $query
            ) use ($cities) {
                $query->whereIn('city_id', $cities)->orWhere('city_id', null);
            });
        } else {
            if (request()->get('city_id') != null) {
                $items->where('city_id', request()->get('city_id'));
            }
        }

        if (request()->get('id') != null) {
            $items->where('id', request()->get('id'));
        } else {
            if (request()->get('q') != null) {
                $q = $prefix . request()->get('q') . $prefix;
                $items->where(function ($query) use ($q, $condition) {
                    $query->orWhere("paid_for", $condition, $q)
                        ->orWhere("paid_by", $condition, $q)
                        ->orWhere("type", $condition, $q)
                        ->orWhereHas('user', function ($query) use ($q, $condition) {
                            $query->where('customer_id', $condition, $q)
                                ->orWhere("passport", $condition, $q)
                                ->orWhere("fin", $condition, $q)
                                ->orWhere("phone", $condition, $q)
                                ->orWhere("email", $condition, $q)
                                ->orWhere(DB::raw('concat(trim(name)," ",trim(surname))'), $condition, $q)
                                ->orWhereHas('dealer', function ($query) use ($q) {
                                    $query->where('customer_id', 'LIKE', '%' . $q . '%')
                                        ->orWhere('passport', 'LIKE', '%' . $q . '%')
                                        ->orWhere('fin', 'LIKE', '%' . $q . '%')
                                        ->orWhere('phone', 'LIKE', '%' . $q . '%')
                                        ->orWhere('email', 'LIKE', '%' . $q . '%')
                                        ->orWhere(DB::raw('concat(trim(name)," ",trim(surname))'), 'LIKE', "%" . $q . "%");
                                });
                        })
                        ->orWhereHas('package', function ($query) use ($q, $condition) {
                            $query->where('custom_id', $condition, $q)
                                ->orWhere("tracking_code", $condition, $q);
                        })
//                        ->orWhereHas('cd', function ($query) use ($q, $condition) {
//                            $query->where('packages_str', $condition, $q);
//                        })
                        ->orWhereHas('track', function ($query) use ($q, $condition) {
                            $query->where('tracking_code', $condition, $q);
                        });
                });
            }


            if (request()->get('paid_by') != null) {
                $items->where('paid_by', request()->get('paid_by'));
            }
            if (request()->get('admin_id') != null) {
                $items->where('admin_id', request()->get('admin_id'));
            }
            if (request()->get('paid_for') != null) {
                $items->where('paid_for', request()->get('paid_for'));
            }


            if (Request::get('courier_id') != null) {
                $items->whereHas('cd', function (
                    $query
                ) {
                    $query->where('courier_id', Request::get('courier_id'));
                });
            }


            if (Request::get('warehouse_id') != null) {
                $items->whereHas('package', function (
                    $query
                ) {
                    $query->where('warehouse_id', Request::get('warehouse_id'));
                });
            }


            if (request()->get('start_date') != null) {
                $items->where('created_at', '>=', request()->get('start_date') . " 00:00:00");
            }
            if (request()->get('end_date') != null) {
                $items->where('created_at', '<=', request()->get('end_date') . " 23:59:59");
            }
        }

        if (request()->get('type') != null) {
            if( request()->get('type') == 'DEBT'){
                $items->where('debt', 1);
            }else{
                $items->where('debt', '!=',1);
            }

        }
        if (request()->get('transaction_type') != null) {
            if( request()->get('transaction_type') == 'PACKAGE_DEBT'){
                $items->where('paid_for', 'PACKAGE_DEBT');
            }elseif( request()->get('transaction_type') == 'TRACK_DEBT'){
                $items->where('paid_for','TRACK_DEBT');
            }

        }

        if (request()->get('search_type') == 'export') {
            if (request()->has('pe') && request()->get('pe') == 1) {
            } else {
                if ($items->count()) {
                    $items = $items->get();
                } else {
                    $items = $items->paginate($this->limit);
                }
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


        if (request()->has('pe') && request()->get('pe') == 1) {
            $str = "select t.extra_data,sum(t.amount) as amount,max(t.created_at) as created_at,GROUP_CONCAT( p.custom_id SEPARATOR ' , ') as pkg_custom_id from transactions t";
            $str .= " left outer join packages p on p.id=t.custom_id";
            $str .= " where t.type!='ERROR'";
            $str .= " and t.paid_by='PORTMANAT'";
            if (request()->get('start_date') != null) {
                $str .= " and t.created_at >= '" . request()->get('start_date') . " 00:00:00'";
            }
            if (request()->get('end_date') != null) {
                $str .= " and t.created_at <='" . request()->get('end_date') . " 23:59:59'";
            }
            $str .= " group by t.extra_data";
            $str .= " order by created_at";
            $items = DB::select($str);
            return Excel::download(new PortmanatExport($items), 'portmanat_' . uniqid() . '.xlsx');
	} else if (request()->has('9e') && request()->get('9e') == 1) {
            return Excel::download(new Transactions90Export($items), 'transactions_90usd' . uniqid() . '.xlsx');
        } else {
            return Excel::download(new TransactionsExport($items), 'transactions_' . uniqid() . '.pdf', 'Mpdf');
        }
        //return \Excel::download(new TransactionsExport($items), 'transactions_' . uniqid() . '.xlsx');
    }

//    public function ajax(Request $request, $id)
//    {
//        $transaction = Transaction::find($id);
//        if ($request->get('name') == 'paid_by') {
//            dd('sa');
//        }
//
//        return parent::ajax($request, $id);
//    }

}
