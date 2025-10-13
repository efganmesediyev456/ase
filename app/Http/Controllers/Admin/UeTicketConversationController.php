<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Http\Requests;
use App\Models\Package;
use App\Models\UETicket;
use App\Models\UETicketConversation;
use App\Models\UkrExpressModel;
use Illuminate\Http\Request;
use Session;
use Validator;

class UeTicketConversationController extends Controller
{
    protected $view = [
        'name' => 'UE Ticket Conversation',
        'formColumns' => 20,
        'sub_title' => 'UE Ticket Conversation',
        'search' => [
            [
                'name' => 'q',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Search text...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
            ],
        ],
    ];

    protected $route = 'ue_ticket_conversations';
    protected $modelName = 'UETicketConversation';


    protected $list = [
        'ticket.number' => [
            'label' => 'Number',
        ],
        'ticket.package.tracking_code' => [
            'label' => 'Tracking #',
        ],
        /*'name'     => [
             'label' => 'Name',
     ],*/
        'type' => [
            'label' => 'Type',
        ],
        'text' => [
            'label' => 'Text',
            'type' => 'raw',
        ],
        'is_read' => [
            'label' => 'Read',
            'type' => 'yes_no',
        ],
        'created_timestamp' => [
            'label' => 'Created At',
        ],
    ];

    protected $fields = [
        [
            'label' => 'Text',
            'type' => 'textarea',
            'name' => 'text',
            'attributes' => [
                'rows' => '4',
            ],
            'validation' => 'required|string|min:2',
        ],
    ];

    public function __construct()
    {
        $tId = request()->route('ue_ticket_id');
        $ticket = UETicket::find($tId);
        if (!$ticket) {
            return back();
        }
        $this->routeParams = [
            'ue_ticket_id' => $ticket->id,
        ];
        $this->view['name'] = 'Ticket conversation for ' . $ticket->number;
        parent::__construct();
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
        }
        $ticketConversation = new UETicketConversation();
        $ue = new UkrExpressModel();
        $ticketConversation->ue_ticket_id = $this->routeParams['ue_ticket_id'];
        $ticketConversation->text = $request->get("text");
        if (!$ue->ticket_conversation_add($ticketConversation)) {
            $message = "Error while adding ticket conversation to UE: " . $ue->message;
            Session::flash('error', $message);
            return redirect()->back()->withInput($request->all());
        }
        $ticketConversation->ukr_express_id = $ue->id;
        $ticketConversation->save();
        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'TicketConversations',
            'key' => 'Ticket Conversation #',
            'value' => $ticketConversation->ukr_express_id,
            'action' => 'created',
        ]));

        return redirect()->route($this->route . '.index', $this->routeParams);
    }

    public function indexObject()
    {
        $validator = Validator::make(\Request::all(), [
            'q ' => 'string',
        ]);

        if ($validator->failed()) {
            Alert::error('Unexpected variables!');
            return redirect()->route("my.dashboard");
        }
        $ticket = UETicket::find($this->routeParams['ue_ticket_id']);
        if (!$ticket) {
            Alert::error('UE Ticket not found!');
            return redirect()->route("my.dashboard");
        }
        $ue = new UkrExpressModel();
        $ticketConversations = $ue->ticket_conversation_list($ticket);
        if ($ticketConversations && count($ticketConversations) > 0) {
            $items = UETicketConversation::where('ue_ticket_id', $ticket->id)->get();
            foreach ($ticketConversations as $ueConv) {
                $found = false;
                foreach ($items as $item) {
                    if ($item->ukr_express_id == $ueConv->id) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $ticketConversation = new UETicketConversation();
                    $ticketConversation->ue_ticket_id = $ticket->id;
                    $ticketConversation->ukr_express_id = $ueConv->id;
                    $ticketConversation->text = $ueConv->text;
                    $ticketConversation->type = $ueConv->type;
                    $ticketConversation->is_read = $ueConv->is_read;
                    $ticketConversation->created_timestamp = date('Y-m-d H:i:s', $ueConv->created_timestamp);
                    $ticketConversation->save();
                }
            }
        }

        $items = UETicketConversation::where('ue_ticket_id', $ticket->id);

        if (\Request::get('q') != null) {
            $q = str_replace('"', '', \Request::get('q'));
            $items->where('text', 'like', '%' . $q . '%');
        }
        $items = $items->orderBy('created_timestamp', 'desc');
        $items = $items->paginate($this->limit);

        return $items;
    }
}
