<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Http\Requests;
use App\Models\Extra\Notification;
use App\Models\Link;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class OrderController extends Controller
{
    protected $extraActions = [
        [
            'route' => 'orders.links',
            'key' => 'id',
            'label' => 'Links',
            'icon' => 'link',
            'color' => 'success',
            'target' => '_blank',
        ],
    ];

    protected $notificationKey = 'custom_id';

    protected $withCount = 'links';

    protected $view = [
        'name' => 'Request',
        'formColumns' => 10,
        'sub_title' => 'User requests',
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
                'name' => 'country_id',
                'attribute' => 'name',
                'model' => 'App\Models\Country',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'All Countries',
            ],
            [
                'name' => 'status',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.attributes.request.status',
                'wrapperAttributes' => [
                    'class' => 'col-lg-1',
                ],
                'allowNull' => 'All',
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
                    'class' => 'col-lg-2',
                ],
            ],
        ],
    ];

    protected $list = [
        'custom_id' => [
            'label' => 'No',
        ],
        'country' => [
            'type' => 'country',
            'label' => 'Country',
        ],
        'user' => [
            'type' => 'custom.user',
            'label' => 'User',
        ],
        'links_count' => [
            'label' => 'Links',
        ],
        'total_price' => [
            'label' => 'Total Price',
        ],
        'status' => [
            'label' => 'Status',
            'type' => 'select-editable',
            'editable' => [
                'route' => 'orders.ajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.request.statusWithLabel',
            ],
        ],
        'admin.name' => [
            'label' => 'Admin',
        ],
        'created_at' => [
            'label' => 'At',
            'type' => 'date',
        ],
    ];

    protected $fields = [
        [
            'label' => 'User',
            'type' => 'select2',
            'name' => 'user_id',
            'attribute' => 'full_name,customer_id',
            'model' => 'App\Models\User',
            'hint' => 'Not suggest to change user',
            'wrapperAttributes' => [
                'class' => 'col-md-5',
            ],
            'validation' => 'required|integer',
        ],
        [
            'label' => 'Country',
            'type' => 'select2',
            'name' => 'country_id',
            'attribute' => 'name',
            'model' => 'App\Models\Country',
            'wrapperAttributes' => [
                'class' => ' col-md-4',
            ],
            'allowNull' => true,
            'validation' => 'nullable|integer',
        ],

        [
            'name' => 'status',
            'label' => 'Status',
            'type' => 'select_from_array',
            'optionsFromConfig' => 'ase.attributes.request.status',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'required|integer',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"><h3 class="text-center">User notes</h3></div>',
        ],

        [
            'name' => 'note',
            'type' => 'textarea',
            'label' => 'User note',
            'attributes' => [
                'disabled' => 'disabled',
            ],
            'wrapperAttributes' => [
                'class' => 'col-md-7',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'extra_contacts',
            'type' => 'textarea',
            'label' => 'Extra contacts',
            'attributes' => [
                'disabled' => 'disabled',
            ],
            'wrapperAttributes' => [
                'class' => 'col-md-5',
            ],
            'validation' => 'nullable|string',
        ],

        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
        ],
        [
            'name' => 'price',
            'type' => 'text',
            'label' => 'Price',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'coupon_sale',
            'type' => 'text',
            'label' => 'Coupon sale',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'service_fee',
            'type' => 'text',
            'label' => 'Service Price',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'name' => 'total_price',
            'type' => 'text',
            'label' => 'Total Price',
            'prefix' => '<i class="icon-coin-dollar"></i>',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
        ],
        [
            'name' => 'admin_note',
            'type' => 'summernote',
            'label' => 'Admin note',
            'validation' => 'nullable|string',
        ],

    ];

    public function links($id)
    {
        $order = Order::find($id);
        if (!$order) {
            abort(404);
        }

        $key = 'status';
        $head = [
            'editable' => [
                'route' => 'orders.linkajax',
                'type' => 'select',
                'sourceFromConfig' => 'ase.attributes.request.link.statusWithLabel',
            ],
        ];
        $links = Link::whereOrderId($id)->get();

        return view('admin.orders.links', compact('order', 'links', 'key', 'head'));
    }

    public function linkajax(Request $request, $id)
    {
        $item = Link::find($id);
        $item->{$request->get('name')} = $request->get('value');
        $item->save();

        return 'Ok';
    }

    public function indexObject()
    {
        $validator = Validator::make(\Request::all(), [
            'q' => 'string',
            'status' => 'integer',
            'country_id ' => 'integer',
            'start_date' => 'date',
            'start_end' => 'date',
        ]);

        if ($validator->failed()) {
            Alert::error('Unexpected variables!');

            return redirect()->route("my.dashboard");
        }

        $items = Order::withCount('links')->latest();

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
                $query->orWhere("note", "LIKE", "%" . $q . "%")->orWhere("custom_id", "LIKE", "%" . $q . "%")->orWhereHas('user', function (
                    $query
                ) use ($q) {
                    $query->orWhere('customer_id', 'LIKE', '%' . $q . '%')
                        ->orWhere('passport', 'LIKE', '%' . $q . '%')
                        ->orWhere('fin', 'LIKE', '%' . $q . '%')
                        ->orWhere('phone', 'LIKE', '%' . $q . '%')
                        ->orWhere('email', 'LIKE', '%' . $q . '%')
                        ->orWhere('email', 'LIKE', '%' . $q . '%')
                        ->orWhere(DB::raw('concat(trim(name)," ",trim(surname))'), 'LIKE', "%" . $q . "%");
                });
            });
        }
        if (\Request::get('status') != null) {
            $items->where('status', \Request::get('status'));
        }

        if (\Request::get('country_id') != null) {
            $items->where('country_id', \Request::get('country_id'));
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


    public function update(Request $request, $id)
    {
        $used = Order::find($id);

        if (trim($used->status) != trim($request->get('status'))) {

            /* Send Notification */
            Notification::sendOrder($id, trim($request->get('status')));
        }

        return parent::update($request, $id);
    }

    public function ajax(Request $request, $id)
    {
        if ($request->get('name') == 'status') {
            $used = Order::find($id);

            if (trim($used->status) != trim($request->get('value'))) {
                /* Send Notification */
                Notification::sendOrder($id, trim($request->get('value')));
            }
        }

        return parent::ajax($request, $id);
    }

    public function edit($id)
    {
        $order = Order::find($id);
        if ($order && $order->admin_id && $order->admin_id != auth()->guard('admin')->user()->id) {
            Alert::error(trans('saysay::crud.unauthorized_access'));

            return redirect()->back();
        }

        return parent::edit($id);
    }
}
