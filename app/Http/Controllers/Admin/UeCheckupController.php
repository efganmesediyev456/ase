<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Http\Requests;
use App\Models\Package;
use App\Models\UECheckup;
use App\Models\UkrExpressModel;
use Illuminate\Http\Request;
use Session;
use Validator;

class UeCheckupController extends Controller
{
    protected $view = [
        'name' => 'UE Checkups',
        'formColumns' => 20,
        'sub_title' => 'UE Checkups',
        'search' => [
            [
                'name' => 'q',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Search tracking number...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
            ],
            [
                'type' => 'select2',
                'name' => 'user_id',
                'attribute' => 'full_name,custom_id',
                'model' => 'App\Models\User',
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
                'allowNull' => 'All Users',
            ],
        ],
    ];

    protected $route = 'ue_checkups';
    protected $modelName = 'UECheckup';

    protected $extraButtons = [
        [
            'label' => 'Refresh All Unprocessed',
            'icon' => 'spinner11',
            'route' => 'ue_checkups.refreshAll',
            'color' => 'warning',
            'attributes' => [
                'id' => 'refreshAllUnprocessed',
                'onclick' => 'return confirm("Are you sure you want to refresh all unprocessed checkups?")'
            ],
        ],
    ];

    protected $extraActions = [
        [
            'key' => 'id',
            'role' => 'close-ue_checkups',
            'label' => 'Close',
            'icon' => 'checkmark',
            'route' => 'ue_checkups.close',
            'color' => 'info',
            //        'target' => '_blank',
        ],
        [
            'key' => 'id',
            'role' => 'close-ue_checkups',
            'label' => 'Refresh',
            'icon' => 'spinner11',
            'route' => 'ue_checkups.refresh',
            'color' => 'info',
            //        'target' => '_blank',
        ],
    ];

    protected $list = [

        'id' => [
            'label' => 'ID',
        ],
        'package.tracking_code' => [
            'label' => 'Package',
        ],
        'package.user.full_name' => [
            'label' => 'User',
        ],
        'description' => [
            'label' => 'Description',
            'type' => 'raw',
        ],
        'requested' => [
            'label' => 'Requested',
            'type' => 'yes_no',
        ],
        'processed' => [
            'label' => 'Processed',
            'type' => 'yes_no',
        ],
        'result' => [
            'label' => 'Result',
            'type' => 'raw',
        ],
        'total_price' => [
            'label' => 'Price',
        ],
        'is_closed' => [
            'label' => 'Closed',
            'type' => 'yes_no',
        ],
        'created_at' => [
            'label' => 'Created At',
        ],
    ];

    protected $fields = [
        [
            'label' => 'Tracking Number',
            'type' => 'text',
            'name' => 'tracking_code',
            'wrapperAttributes' => [
                'class' => 'col-md-5',
            ],
            'validation' => 'nullable|string',
        ],
        [
            'label' => 'Description',
            'type' => 'textarea',
            'name' => 'description',
            'attributes' => [
                'rows' => '4',
            ],
            'validation' => 'required|string|min:2',
        ],
    ];

    public function __construct()
    {
        //$this->fields[0]["default"]=1;
        parent::__construct();
    }
    public function refreshAll()
    {
        $unprocessedCheckups = UECheckup::leftJoin("packages", "ue_checkups.package_id", "packages.id")
            ->leftJoin("users", "packages.user_id", "users.id")
            ->select("ue_checkups.*", "packages.tracking_code", "users.name", "users.surname")
            ->where(function($query) {
                $query->where('ue_checkups.processed', false)
                    ->orWhereNull('ue_checkups.processed');
            })
            ->get();

        $ue = new UkrExpressModel();
        $successCount = 0;
        $errorCount = 0;
        $errorMessages = [];

        foreach ($unprocessedCheckups as $checkup) {
            if (!$checkup->package || !$checkup->package->user || !$checkup->package->user->ukr_express_id || !$checkup->package->ukr_express_id) {
                $errorCount++;
                $errorMessages[] = "Checkup #{$checkup->id} has missing package or user data";
                continue;
            }

            $track = $ue->track_get($checkup->package->ukr_express_id, $checkup->package->user->ukr_express_id);

            if (!$track) {
                $errorCount++;
                $errorMessages[] = "Checkup #{$checkup->id}: Cannot get track from UE - " . $ue->message;
                continue;
            }

            $checkup->requested = $track->additional_services->checkup->requested;
            $checkup->processed = $track->additional_services->checkup->processed;
            $checkup->description = $track->additional_services->checkup->request_description;
            $checkup->result = $track->additional_services->checkup->checkup_result;

            if (isset($track->fees) && isset($track->fees->checkup) && isset($track->fees->checkup->total_cents)) {
                $checkup->total_price = number_format(0 + round($track->fees->checkup->total_cents / 100, 2), 2, ".", "");
            }

            $checkup->save();
            $successCount++;
        }

        $message = "Refreshed {$successCount} checkups successfully.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} checkups failed to refresh.";
            Alert::warning($message);

            // If you want to show all error messages (might be too many)
            // foreach ($errorMessages as $error) {
            //     Alert::error($error);
            // }
        } else {
            Alert::success($message);
        }

        return redirect()->route($this->route . '.index', $this->routeParams);
    }
    public function refresh(Request $request, $id)
    {
        $checkup = UECheckup::find($id);
        if (!$checkup) {
            Alert::error(trans('saysay::crud.not_found'));
            return back();
        }
        if (!$checkup->package) {
            Alert::error('No package in checkup');
            return back();
        }
        if (!$checkup->package->user) {
            Alert::error('No user in checkup');
            return back();
        }
        if (!$checkup->package->user->ukr_express_id) {
            Alert::error('User not in UkrExpress');
            return back();
        }
        if (!$checkup->package->ukr_express_id) {
            Alert::error('Package not in UkrExpress');
            return back();
        }
        $ue = new UkrExpressModel();
        $track = $ue->track_get($checkup->package->ukr_express_id, $checkup->package->user->ukr_express_id);
        if (!$track) {
            $message = "Cannot get track from UE: " . $ue->message;
            Alert::error($message);
            return back();
        }
        $checkup->requested = $track->additional_services->checkup->requested;
        $checkup->processed = $track->additional_services->checkup->processed;
        $checkup->description = $track->additional_services->checkup->request_description;
        $checkup->result = $track->additional_services->checkup->checkup_result;
        if (isset($track->fees) && isset($track->fees->checkup) && isset($track->fees->checkup->total_cents)) {
            $checkup->total_price = number_format(0 + round($track->fees->checkup->total_cents / 100, 2), 2, ".", "");
        }

        $checkup->save();
        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Checkups updated',
            'key' => 'Checkup updated#',
            'value' => $checkup->id,
            'action' => 'refresh',
        ]));
        return redirect()->route($this->route . '.index', $this->routeParams);
    }

    public function close(Request $request, $id)
    {
        $checkup = UECheckup::find($id);
        if (!$checkup) {
            Alert::error(trans('saysay::crud.not_found'));
            return back();
        }
        $ue = new UkrExpressModel();
        if (!$ue->checkup_close($checkup)) {
            return;
            $message = "Error while closing checkup to UE: " . $ue->message;
            Alert::error($message);
            return back();
        }
        $checkup->is_closed = true;
        $checkup->save();
        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Checkups',
            'key' => 'Checkup #',
            'value' => $checkup->id,
            'action' => 'closed',
        ]));
        return redirect()->route($this->route . '.index', $this->routeParams);
    }

    public function store(Request $request)
    {
        $this->validate($request, $this->generateValidation('store'));
        $trackingNumber = $request->get('tracking_code');
        $package = NULL;
        if (!empty($trackingNumber)) {
            $packages = Package::where('tracking_code', $trackingNumber)->get();
            if (!$packages || count($packages) <= 0) {
                $message = "No packages with tracking number: " . $trackingNumber;
                Session::flash('error', $message);
                return redirect()->back()->withInput($request->all());
            }
            if (count($packages) > 1) {
                $message = "Too many packages with tracking number: " . $trackingNumber;
                Session::flash('error', $message);
                return redirect()->back()->withInput($request->all());
            }
            $package = $packages[0];
            if (!$package->ukr_express_id) {
                $message = "This track were not added to Ukraine Express: " . $trackingNumber;
                Session::flash('error', $message);
                return redirect()->back()->withInput($request->all());
            }
        }


        $checkup = new UECheckup();
        $ue = new UkrExpressModel();

        $checkup->description = $request->get("description");

        $checkup->package_id = NULL;
        if ($package) {
            $checkup->package_id = $package->id;
        }



        if (!$ue->checkup_add($checkup)) {
            $message = "Error while adding checkup to UE: " . $ue->message;
            Session::flash('error', $message);
            return redirect()->back()->withInput($request->all());
        }

        $checkup->save();
        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Checkups',
            'key' => 'Checkup #',
            'value' => $checkup->id,
            'action' => 'created',
        ]));

        return redirect()->route($this->route . '.index', $this->routeParams);
    }

    public function indexObject()
    {
        $validator = Validator::make(\Request::all(), [
            'q ' => 'string',
            'user_id ' => 'integer',
        ]);

        if ($validator->failed()) {
            Alert::error('Unexpected variables!');

            return redirect()->route("my.dashboard");
        }
        $items = UECheckup::leftJoin("packages", "ue_checkups.package_id", "packages.id")->leftJoin("users", "packages.user_id", "users.id")->select("ue_checkups.*", "packages.tracking_code", "users.name", "users.surname");


        if (\Request::get('q') != null) {
            $q = str_replace('"', '', \Request::get('q'));
            $items->where('packages.tracking_code', 'like', '%' . $q . '%');
        }
        if (\Request::get('user_id') != null) {
            $items->where('packages.user_id', \Request::get('user_id'));
        }
        $items = $items->orderBy('created_at', 'desc');
        $items = $items->paginate($this->limit);

        return $items;
    }
}
