<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Models\DeliveryPoint;
use App\Models\Extra\Notification;
use App\Models\Package;
use App\Models\PackageLog;
use App\Models\PackageTrackInBaku;
use App\Models\Track;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;
use View;

class CellController extends Controller
{
    protected $modelName = 'Package';

    protected $user = null;

    protected $view = [
        'formColumns' => 6,
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
                'name' => 'requested',
                'type' => 'select_from_array',
                'optionsFromConfig' => 'ase.warehouse.filter',
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Filter',
            ],
            [
                'name' => 'store',
                'type' => 'select_from_array',
                'options' => [1 => 'In Baku', 2 => 'In Kobia'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
                'allowNull' => 'Store',
            ],
            [
                'type' => 'select2',
                'name' => 'azexof_id',
                'attribute' => 'description',
                'model' => 'App\Models\AzeriExpress\AzeriExpressOffice',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'AzeriExpress Office',
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

    protected $list = [
        'scanned_at' => [
            'label' => 'Delivered At',
            //    'type'  => 'date',
        ],
        'requested_at' => [
            'label' => 'Requested At',
        ],
        /*'cell'              => [
            'label'    => 'Cell',
            'type'     => 'select-editable',
            'editable' => [
                'route'  => 'cells.ajax',
                'type'   => 'select',
                'key'    => 'cell',
                'source' => null,
            ],
    ],*/
        'cell' => [
            'label' => 'Cell',
        ],
        'custom_id' => [
            'label' => 'CWB #',
        ],
        'self' => [
            'type' => 'custom.pt_user',
            'label' => 'User',
        ],
        'worker_comments' => [
            'label' => 'Comments',
        ],
        'warehouse.country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
        'city_name' => [
            'label' => 'City',
        ],
        /*        'weight_with_type'  => [
                    'label' => 'Weight',
            ],*/
        'statusWithLabel' => [
            'label' => 'status',
        ],
        'package_filial_name' => [
            'label' => 'Package Filial',
            'order' => 'package_filial_name',
        ],
        'user_filial_name' => [
            'label' => 'User Filial',
            'order' => 'user_filial_name',
        ],
        /*'dp_description' => [
            'label' => 'Precinct',
	     'order' => 'dp_description',
        ],
        'azeri_express_use' => [
            'label' => 'AzExpress',
            'type' => 'yes_no',
	     'order' => 'azeri_express_use',
        ],
        'ao_description' => [
            'label' => 'AzExpress Office',
	     'order' => 'ao_description',
     ],*/
        'weight' => [
            'label' => 'Weight',
        ],
        'number_items' => [
            'label' => 'Items',
        ],
    ];

    protected $fields = [
        [
            'name' => 'custom_id',
            'label' => 'CWB Number',
            'type' => 'text',
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
            'name' => 'parcel_name',
            'label' => 'MAWB Number',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ],
        [
            'name' => 'bag_name',
            'label' => 'Bag Number',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ],
        [
            'name' => 'cell',
            'label' => 'Cell',
            'type' => 'select_from_array',
            'wrapperAttributes' => [
                'class' => 'col-md-3',
            ],
            'options' => null,
            'allowNull' => 'Select Cell',
            'validation' => 'nullable|string',
        ],

    ];

    protected $with = ['user'];

    public function __construct()
    {

        if (\request()->route() != null && \request()->route()->getName() == 'cells.edit') {
            $this->middleware(function ($request, $next) {
                $this->list['cell']['editable']['source'] = generateCells();
                $this->fields[3]['options'] = generateCells(false);

                $cellsView = null;
                $includes = [];

                $id = (int)\request()->route()->parameter('id');
                $track = 0;
                if (\request()->has('track')) {
                    //$track = \request()->get('track');
                    $track = Track::find($id);
                    $this->fields[] = [
                        'name' => 'track',
                        'type' => 'hidden',
                        'default' => '1',
                        'short' => true,
                    ];
                }
                $nearBy = NULL;
                $nearByCount = 0;
                $dealer = NULL;
                $user = NULL;
                $package = NULL;
                if ($track) {
                    $this->modelName = 'Track';
                    $this->fields[0]['name'] = 'tracking_code';
                    $this->fields[1]['name'] = 'container_name';
                    $this->fields[2]['name'] = 'airbox_name';

                    $cellList = listCells();
                    $nearByCount = Track::where('customer_id', $track->customer_id)->where('id', '!=', $id)->whereNotNull('cell')->whereIn('cell', $cellList)->whereIn('status', [16, 20])->count();

                    if ($nearByCount) {
                        $nearBy = Track::where('customer_id', $track->customer_id)->where('id', '!=', $id)->whereNotNull('cell')->whereIn('cell', $cellList)->whereIn('status', [16, 20])->orderBy('cell', 'asc')->first();
                        $nearBy = $nearBy->cell;
                    }
                } else {
                    $package = Package::find($id);
                    $user = $package->user;

                    $dealer = $package->user->dealer ?: null;
                    $cellList = listCells();

                    if ($dealer) {
                        $usersId = User::whereParentId($dealer->id)->where('id', '!=', $dealer->id)->pluck("id")->all();
                        $nearBy = Package::whereIn('user_id', $usersId)->where('id', '!=', $id)->whereNotNull('cell')->whereIn('cell', $cellList)->orderBy('cell', 'asc')->whereIn('status', [2, 8])->first();
                        $nearByCount = Package::whereIn('user_id', $usersId)->where('id', '!=', $id)->whereNotNull('cell')->whereIn('cell', $cellList)->whereIn('status', [2, 8])->count();
                    } else {
                        $nearBy = Package::where('user_id', $user->id)->where('id', '!=', $id)->whereNotNull('cell')->whereIn('cell', $cellList)->whereIn('status', [2, 8])->orderBy('cell', 'asc')->first();
                        $nearByCount = Package::where('user_id', $user->id)->where('id', '!=', $id)->whereNotNull('cell')->whereIn('cell', $cellList)->whereIn('status', [2, 8])->count();
                    }

                    if ($nearByCount) {
                        $nearBy = $nearBy->cell;
                    }

                }

                $includes[] = [
                    'view' => 'admin.widgets.alerting',
                    'data' => [
                        'nearBy' => $nearBy,
                        'nearByCount' => $nearByCount,
                        'dealer' => $dealer,
                        'user' => $user,
                        'package' => $package,
                        'track' => $track,
                    ],
                ];

                $includes[] = [
                    'view' => 'admin.widgets.cells',
                    'data' => [
                        'nearBy' => $nearBy,
                        'track' => $track,
                        //'cells'  => $cells,
                    ],
                ];

                View::share([
                    'includes' => $includes,
                ]);
                parent::__construct();
                return $next($request);
            });
            return;
        }

        parent::__construct();
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

        //$items = Package::select('id','custom_id')->where('status', 2)->orderBy('requested_at', 'desc')->orderBy('cell', 'asc');
        //$items = DB::table('inbaku_v')->select('*')->orderBy('requested_at', 'desc')->orderBy('cell', 'asc');
        //$items = DB::table('inbaku_v')->select('id','custom_id','scanned_at','requested_at','cell','number_items','weight','track','city_name','fullname')->orderBy('requested_at', 'desc')->orderBy('cell', 'asc');
        $admin = auth()->guard('admin')->user();

        $items = PackageTrackInBaku::orderBy('requested_at', 'desc')
            ->orderBy('cell', 'asc');

        if ($admin->store_status == 1) {
            $items->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('track', false)
                        ->where('status', 2);
                })->orWhere(function ($sub) {
                    $sub->where('track', true)
                        ->where('status', 16);
                })->orWhere(function ($sub) {
                        $sub->where('track', true)
                            ->where('status', 19)->orWhere('status', 27);
                    });
            });
        } elseif ($admin->store_status == 2) {
            $items->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->where('track', false)
                        ->where('status', 8);
                })->orWhere(function ($sub) {
                    $sub->where('track', true)
                        ->where('status', 20);
                })->orWhere(function ($sub) {
                    $sub->where('track', true)
                        ->where('status', 19)->orWhere('status', 27);
                });
            });
        }

        //$items = Package::select('id','custom_id')->where('status', 2);
        //$tracks=Track::select('id',DB::raw('tracking_code as custom_id'))->where('status',16);
        //$items->union($tracks)->orderBy('requested_at', 'desc')->orderBy('cell', 'asc');
        //$tracks= Track::where('status',16)->orderBy('requested_at','desc')->orderBy('cell','asc');

        /* Filter cities */
        $cities = auth()->guard('admin')->user()->cities->pluck('id')->all();
        if ($cities) {
            /*$items->whereHas('user', function (
                $query
            ) use ($cities) {
                $query->whereIn('city_id', $cities)->orWhere('city_id', null);
	    });*/
            $items->where(function ($query) use ($cities) {
                $query->whereIn('city_id', $cities)->orWhereRaw('(city_id is NULL or track=1)');
            });
        }

        if (\request()->get('requested') == 1) {
            $items->whereNotNull('requested_at');
        }

        if (\Request::get('q') != null) {
            $q = \Request::get('q');
            $items->whereRaw("custom_id like '%" . $q . "%' or fullname like '%" . $q . "%' or fin like '%" . $q . "%' or phone like '%" . $q . "%'");
        }

        if (\Request::get('warehouse_id') != null) {
            $items->where('warehouse_id', \Request::get('warehouse_id'));
        }

        if (\Request::get('store') != null) {
            $store = \Request::get('store');
            if ($store == 1) { //In baku
                $items->whereRaw('((track=0 and status=2) or (track=1 and status=16))');
            } else if ($store == 2) { //In kobia
                $items->whereRaw('((track=0 and status=8) or (track=1 and status=20))');
            }
        }

        if (\Request::get('azexof_id') != null) {
            $items->where('azeri_express_use', 1)->where('azeri_express_office_id', \Request::get('azexof_id'));
        }

        if (\Request::get('start_date') != null) {
            $items->where('scanned_at', '>=', \Request::get('start_date') . " 00:00:00");
        }
        if (\Request::get('end_date') != null) {
            $items->where('scanned_at', '<=', \Request::get('end_date') . " 23:59:59");
        }

        $items = $items->paginate($this->limit);

        return $items;
    }

    public function ajax(Request $request, $id)
    {
        $used = Package::find($id);


        if ($request->get('name') == 'cell') {
            if (!$used->cell) {
                //$used->scanned_at = Carbon::now();
                Notification::sendPackage($used->id, '2');
                //$used->save();
            }
        }

        if ($request->get('name') == 'status') {

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

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $this->fields[] = [
            'name' => 'tracking_code',
            'label' => 'Cell',
            'type' => 'text',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
            'validation' => 'nullable|string',
        ];
        if (\Request::has('action') && \Request::get('action')) {
            $action = \Request::get('action');
            if ($action == 'send_filial') {
                if (\Request::has('store_status') && \Request::get('store_status')) {
                    $store_status = \Request::get('store_status');
                    if (\Request::has('track') && \Request::get('track')) {
                        $track = Track::find($id);
                        if ($track && $track->store_status == 1) {
                            $track->bot_comment = "Send";
                            $dp1 = NULL;
                            $dp2 = NULL;
                            if ($track->store_status)
                                $dp1 = DeliveryPoint::withTrashed()->where('id', $track->store_status)->first();
                            if ($store_status)
                                $dp2 = DeliveryPoint::withTrashed()->where('id', $store_status)->first();
                            if ($dp1)
                                $track->bot_comment .= " from " . $dp1->description;
                            if ($dp2)
                                $track->bot_comment .= " to " . $dp2->description;
                            $track->store_status = $store_status;
                            $track->cell = NULL;
                            $track->save();
                            (new  \App\Services\Package\PackageService())->addPackageToContainer('precinct', $store_status, 'track', $track->tracking_code,true);
                            return redirect()->route("cells.edit", ['id' => $id, 'track' => 1]);
                        }
                    } else {
                        $package = Package::find($id);
                        if ($package && $package->store_status == 1) {
                            $package->bot_comment = "Send";
                            $dp1 = NULL;
                            $dp2 = NULL;
                            if ($package->store_status)
                                $dp1 = DeliveryPoint::withTrashed()->where('id', $package->store_status)->first();
                            if ($store_status)
                                $dp2 = DeliveryPoint::withTrashed()->where('id', $store_status)->first();
                            if ($dp1)
                                $package->bot_comment .= " from " . $dp1->description;
                            if ($dp2)
                                $package->bot_comment .= " to " . $dp2->description;
                            $package->store_status = $store_status;
                            $package->cell = NULL;
                            $package->save();
                            (new  \App\Services\Package\PackageService())->addPackageToContainer('precinct', $store_status, 'package', $package->custom_id,true);

//                            if ($package->paid == 1 || in_array($package->store_status, [1, 3, 4, 7, 8])) {
//                            }
                            return redirect()->route("cells.edit", $id);
                        }
                    }
                }
            }
        }
        return parent::edit($id);
    }

    public function update(Request $request, $id)
    {
        $admin = Auth::guard('admin')->user();
        $ldate = date('Y-m-d H:i:s');
        if ($request->has('track') && $request->get('track')) {
            $str = $id;
            if ($request->has('cell')) {
                $str .= " " . $request->get('cell');
            }
            //file_put_contents('/var/log/ase_track_scan.log', $ldate . " " . $str . " \n", FILE_APPEND);
            $track = Track::find($id);
            if ($track) {
                $track->scanned_at = $ldate;//Carbon::now();
                if ($request->has('cell')) {
                    $track->cell = $request->get('cell');
                    if (!$track->notification_inbaku_at) {
                        $track->notification_inbaku_at = Carbon::now();
                    }
                }
                if ($admin->store_status == 2) { //In Store
                    if ($track->status != 20) {
                        $track->status = 20;
                        if ($track->partner_id != 5 && $track->partner_id != 6) {
                            Notification::sendTrack($track->id, 20);
                        }
                    }
                } else { // In Baku
                    if ($track->status != 16) {
                        $track->status = 16;
                        if ($track->partner_id != 5 && $track->partner_id != 6 && $track->city_id != 3 && $track->city_id != 6) {
                            Notification::sendTrack($track->id, 16);
                        }
                    }
                }
                $track->save();
                $str .= " " . $track->tracking_code;
                //file_put_contents('/var/log/ase_track_scan.log', $ldate . " " . $str . " \n", FILE_APPEND);
            }
            return redirect()->route("cells.index");
        }

        $package = Package::with(['parcel', 'bag'])->find($id);
        if ($admin->store_status == 2) { //In Store
            if ($package->status != 8) {
                $package->status = 8;
                Notification::sendPackage($package->id, '8');
            }
        } else { //In Baku
            if ($package->status != 2) {
                $package->status = 2;
                Notification::sendPackage($package->id, '2');
            }
        }
        $package->save();
        if (!$package->scanned_at) {
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
            //if (!$package->cell) {
            //}
        }

        parent::update($request, $id);
        return redirect()->route("cells.index");
    }
}
