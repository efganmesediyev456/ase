<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Http\Requests;
use App\Models\Package;
use App\Models\UETicket;
use App\Models\UkrExpressModel;
use Illuminate\Http\Request;
use Session;
use Validator;

class UeTicketController extends Controller
{
    protected $view = [
        'name' => 'UE Tickets',
        'formColumns' => 20,
        'sub_title' => 'UE Tickets',
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

    protected $route = 'ue_tickets';
    protected $modelName = 'UETicket';

    protected $extraActions = [
        [
            'key' => 'id',
            'role' => 'read-ue_ticket_conversations',
            'label' => 'Conversations',
            'icon' => 'map',
            'route' => 'ue_ticket_conversations.index',
            'color' => 'info',
            'target' => '_blank',
        ],
        [
            'key' => 'id',
            'role' => 'close-ue_tickets',
            'label' => 'Close',
            'icon' => 'checkmark',
            'route' => 'ue_tickets.close',
            'color' => 'info',
            //        'target' => '_blank',
        ],
    ];

    protected $list = [
        'number' => [
            'label' => 'Number',
        ],
        'package.tracking_code' => [
            'label' => 'Package',
        ],
        'package.user.full_name' => [
            'label' => 'User',
        ],
        'subject' => [
            'label' => 'Subject',
        ],
        'description' => [
            'label' => 'Description',
            'type' => 'raw',
        ],
        'is_closed' => [
            'label' => 'Closed',
            'type' => 'yes_no',
        ],
        'created_timestamp' => [
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
        /*        [
                    'label'             => 'Answer required',
                    'type'              => 'checkbox',
                    'name'              => 'answer_required',
                    'wrapperAttributes' => [
                        'class' => 'col-md-2',
                    ],
                    'validation'        => 'nullable|integer',
            ],*/
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
        ],
        [
            'label' => 'Subject',
            'type' => 'text',
            'name' => 'subject',
            'wrapperAttributes' => [
                'class' => 'col-md-7',
            ],
            'validation' => 'required|string|min:2',
        ],
        [
            'type' => 'html',
            'html' => '<div class="form-group col-lg-12 mt-10"></div>',
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

    public function close(Request $request, $id)
    {
        $ticket = UETicket::find($id);
        if (!$ticket) {
            Alert::error(trans('saysay::crud.not_found'));
            return back();
        }
        $ue = new UkrExpressModel();
        if (!$ue->ticket_close($ticket)) {
            return;
            $message = "Error while closing ticket to UE: " . $ue->message;
            Alert::error($message);
            return back();
        }
        $ticket->save();
        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Tickets',
            'key' => 'Ticket #',
            'value' => $ticket->number,
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
        $ticket = new UETicket();
        $ue = new UkrExpressModel();
        $ticket->subject = $request->get("subject");
        $ticket->description = $request->get("description");
        //$ticket->answer_required=$request->get("answer_required");
        $ticket->package_id = NULL;
        $ticket->linked_tracking_id = 0;
        $ticket->linked_parcel_id = 0;
        if ($package) {
            $ticket->package_id = $package->id;
            $ticket->number = $package->tracking_code;
            if ($package->ukr_express_id)
                $ticket->linked_tracking_id = $package->ukr_express_id;
            if ($package->ukr_express_parcel_id)
                $ticket->linked_parcel_id = $package->ukr_express_parcel_id;
        }
        if (!$ue->ticket_add($ticket)) {
            $message = "Error while adding ticket to UE: " . $ue->message;
            Session::flash('error', $message);
            return redirect()->back()->withInput($request->all());
        }
        $ticket->ukr_express_id = $ue->id;
        $ticket->save();
        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Tickets',
            'key' => 'Ticket #',
            'value' => $ticket->number,
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
        $items = UETicket::leftJoin("packages", "ue_tickets.package_id", "packages.id")->leftJoin("users", "packages.user_id", "users.id")->select("ue_tickets.*", "packages.tracking_code", "users.name", "users.surname");


        if (\Request::get('q') != null) {
            $q = str_replace('"', '', \Request::get('q'));
            $items->where('packages.tracking_code', 'like', '%' . $q . '%');
        }
        if (\Request::get('user_id') != null) {
            $items->where('packages.user_id', \Request::get('user_id'));
        }
        $items = $items->orderBy('created_timestamp', 'desc');
        $items = $items->paginate($this->limit);

        return $items;
    }
}
