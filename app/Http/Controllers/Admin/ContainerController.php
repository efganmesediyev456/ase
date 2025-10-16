<?php

namespace App\Http\Controllers\Admin;

use Alert;
use App\Exports\Admin\ContainerExport;
use App\Jobs\ResetPackageWeightJob;
use App\Models\Airbox;
use App\Models\AzeriExpress\AzeriExpressOrder;
use App\Models\AzeriExpress\AzeriExpressPackage;
use App\Models\Azerpost\AzerpostOrder;
use App\Models\Azerpost\AzerpostPackage;
use App\Models\Container;
use App\Models\Extra\Notification;
use App\Models\Kargomat\KargomatOrder;
use App\Models\Kargomat\KargomatPackage;
use App\Models\PackageCarrier;
use App\Models\Precinct\PrecinctOrder;
use App\Models\Precinct\PrecinctPackage;
use App\Models\Surat\SuratOrder;
use App\Models\Surat\SuratPackage;
use App\Models\Track;
use App\Models\YeniPoct\YenipoctOrder;
use App\Models\YeniPoct\YenipoctPackage;
use App\Services\Package\PackageService;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Excel;
use Carbon\Carbon;
use Request;
use View;

class ContainerController extends Controller
{
    protected $extraActions = [
        [
            'key' => 'id',
            'label' => 'Rename',
            'color' => 'info',
            'icon' => 'pencil',
            'id' => 'renameContainer',
            'data-id' => "id",
            'data-toggle' => "modal",
            'data-target' => "#updateModal",
            'data-url' => "containers.update_name",
        ],
        [
            'key' => 'id',
            'label' => 'Add bags & tracks',
            'color' => 'info',
            'icon' => 'pencil',
            'id' => 'addBagTracks',
            'data-id' => "id",
            'data-toggle' => "modal",
            'data-target' => "#updateMawb",
            'data-url' => "containers.update_mawb",
        ],
        [
            'key' => 'id',
            'label' => 'Depesh Start',
            'route' => 'containers.depesh_start',
            'color' => 'info',
            'icon' => 'airplane2',
            'confirm' => 'Start Depesh for this parcel',
        ],
        [
            'key' => 'id',
            'label' => 'Depesh Stop',
            'route' => 'containers.depesh_stop',
            'color' => 'info',
            'icon' => 'airplane2',
            'confirm' => 'Stop Depesh for this parcel',
        ],
        [
            'key' => 'id',
            'role' => 'customs-reset',
            'label' => 'Customs reset',
            'icon' => 'spinner11',
            'route' => 'tracks.parselcarrierupdate',
            'color' => 'info',
            'target' => '_blank',
        ],
        [
            'key' => 'id',
            'role' => 'customs-check',
            'label' => 'Customs check',
            'icon' => 'checkmark',
            'route' => 'tracks.parseldepeshcheck',
            'color' => 'info',
            'target' => '_blank',
        ],
        [
            'key' => 'id',
            'role' => 'customs-depesh',
            'label' => 'Customs depesh',
            'icon' => 'cart',
            'route' => 'tracks.parseldepesh',
            'color' => 'info',
            'target' => '_blank',
            'confirm' => 'depesh this parcel',
        ],
        [
            'key' => 'id',
            'label' => 'Sent',
            'route' => 'containers.sent',
            'color' => 'info',
            'icon' => 'airplane2',
            'confirm' => 'make sent this parcel',
        ],
        [
            'key' => 'id',
            'label' => 'Customs Clearance',
            'route' => 'containers.containerCustomsClearance',
            'color' => 'info',
            'icon' => 'loop',
            'confirm' => 'make customs clearance this parcel',
        ],
        [
            'key' => 'id',
            'label' => 'Customs Completed',
            'route' => 'containers.containerCustomsCompleted',
            'color' => 'info',
            'icon' => 'spinner',
            'confirm' => 'make customs completed this parcel',
        ],
        [
            'key' => 'id',
            'label' => 'Increase Weights',
            'route' => 'containers.containerIncreaseWeights',
            'color' => 'orange',
            'icon' => 'reset',
            'confirm' => 'Are you sure to increase weights?',
        ],
    ];
    protected $extraActionsForBag = [
        [
            'route' => 'tracks.bagcarrierupdate',
            'key' => 'id',
            'role' => 'customs-reset',
            'label' => 'Customs reset',
            'icon' => 'spinner11',
            'color' => 'success',
            'target' => '_blank',
        ],
        [
            'key' => 'id',
            'role' => 'customs-check',
            'label' => 'Customs check',
            'icon' => 'checkmark',
            'route' => 'tracks.bagdepeshcheck',
            'color' => 'info',
            'target' => '_blank',
        ],
        [
            'key' => 'id',
            'role' => 'customs-depesh',
            'label' => 'Customs depesh',
            'icon' => 'cart',
            'route' => 'tracks.bagdepesh',
            'color' => 'info',
            'target' => '_blank',
            'confirm' => 'depesh this bag',
        ],
        [
            'key' => 'id',
            'label' => 'Customs Clearance',
            'route' => 'containers.airboxCustomsClearance',
            'color' => 'info',
            'icon' => 'loop',
            'confirm' => 'make customs clearance this bag',
        ],
        [
            'key' => 'id',
            'label' => 'Customs Completed',
            'route' => 'containers.airboxCustomsCompleted',
            'color' => 'info',
            'icon' => 'spinner',
            'confirm' => 'make customs completed this bag',
        ],
    ];
    protected $extraActionsForPackage = [
        [
            'route' => 'tracks.carrier_update',
            'key' => 'id',
            'role' => 'customs-reset',
            'label' => 'Customs reset',
            'icon' => 'spinner11',
            'color' => 'success',
            'target' => '_blank',
        ],
        [
            'key' => 'id',
            'role' => 'customs-check',
            'label' => 'Customs check',
            'icon' => 'checkmark',
            'route' => 'tracks.packagedepeshcheck',
            'color' => 'info',
            'target' => '_blank',
        ],
        [
            'key' => 'id',
            'role' => 'customs-depesh',
            'label' => 'Customs depesh',
            'icon' => 'cart',
            'route' => 'tracks.packagedepesh',
            'color' => 'info',
            'target' => '_blank',
            'confirm' => 'depesh this track',
        ],
        [
            'key' => 'id',
            'label' => 'Customs Clearance',
            'route' => 'containers.trackCustomsClearance',
            'color' => 'info',
            'icon' => 'loop',
            'confirm' => 'make customs clearance this track',
        ],
        [
            'key' => 'id',
            'label' => 'Customs Completed',
            'route' => 'containers.trackCustomsCompleted',
            'color' => 'info',
            'icon' => 'spinner',
            'confirm' => 'make customs completed this track',
        ],
    ];
    protected $modelName = 'Container';
    protected $view = [
        'name' => 'Parcel',
        'search' => [
            [
                'name' => 'parcel',
                'type' => 'text',
                'attributes' => ['placeholder' => 'MAWB'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-2',
                ],
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
        ]
    ];
    protected $list = [
        'partner_with_label' => [
            'label' => 'Partner',
        ],
        'partner.country' => [
            'label' => 'Country',
            'type' => 'country',
        ],
        "tracking_code" => [
            'label' => 'Tracking #',
        ],
        'fullname' => [
            'label' => 'Customer',
        ],
        'weight' => [
            'label' => 'Weight',
            'type' => 'text',
        ],
        'number_items' => [
            'label' => 'Items',
        ],
        'carrier' => [
            'type' => 'carrier',
            'label' => 'Customs',
        ],
        'scanned_at' => [
            'label' => 'Delivered',
        ],
        'status_with_label' => [
            'label' => 'Status',
        ],
        'from_country' => [
            'label' => 'From country',
        ],
        'created_at' => [
            'label' => 'At',
            'type' => 'date',
        ],
    ];

    public function __construct()
    {
        set_time_limit(20 * 60);
        $this->limit = 5;
        parent::__construct();
        View::share('extraActionsForPackage', $this->extraActionsForPackage);
        View::share('extraActionsForBag', $this->extraActionsForBag);
    }

    public function containerCustomsClearance($id)
    {
        $container = Container::with(['tracks', 'partner', 'airboxes'])->find($id);
        if ($container && $container->tracks) {

            foreach ($container->tracks as $track) {
                $this->trackUpdateStatus($track, 44);
            }

            if ($container->airboxes) {
                foreach ($container->airboxes as $airbox) {
                    if ($airbox->status != 44) {
                        $airbox->status = 44;
                        $airbox->save();
                    }
                }
            }
            $container->status = 44;
            $container->save();
        }

        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Parcel',
            'key' => 'name',
            'value' => $container->name,
            'action' => 'Customs Clearance',
        ]));

        return redirect()->back();
    }

    public function containerCustomsCompleted($id)
    {
        $container = Container::with(['tracks', 'partner', 'airboxes'])->find($id);
        if ($container && $container->tracks) {

            foreach ($container->tracks as $track) {
                $this->trackUpdateStatus($track, 25);
            }

            if ($container->airboxes) {
                foreach ($container->airboxes as $airbox) {
                    if ($airbox->status != 25) {
                        $airbox->status = 25;
                        $airbox->save();
                    }
                }
            }
            $container->status = 25;
            $container->save();
        }

        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Parcel',
            'key' => 'name',
            'value' => $container->name,
            'action' => 'Customs Completed',
        ]));

        return redirect()->back();
    }

    public function containerIncreaseWeights($id)
    {
        $container = Container::with('tracks')->find($id);
        if ($container && $container->tracks) {
            foreach ($container->tracks as $track) {
                ResetPackageWeightJob::dispatch($track);
            }
        }
        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Parcel',
            'key' => 'name',
            'value' => $container->name,
            'action' => 'Weights will update soon..',
        ]));
        return redirect()->back();
    }

    public function airboxCustomsClearance($id)
    {
        $airbox = Airbox::with('tracks')->find($id);
        if ($airbox && $airbox->tracks) {

            foreach ($airbox->tracks as $track) {
                $this->trackUpdateStatus($track, 44);
            }

            $airbox->status = 44;
            $airbox->save();
        }

        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Bag',
            'key' => 'name',
            'value' => $airbox->name,
            'action' => 'Customs Clearance',
        ]));

        return redirect()->back();
    }

    public function airboxCustomsCompleted($id)
    {
        $airbox = Airbox::with('tracks')->find($id);
        if ($airbox && $airbox->tracks) {

            foreach ($airbox->tracks as $track) {
                $this->trackUpdateStatus($track, 25);
            }

            $airbox->status = 25;
            $airbox->save();
        }

        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Bag',
            'key' => 'name',
            'value' => $airbox->name,
            'action' => 'Customs Completed',
        ]));

        return redirect()->back();
    }

    public function trackCustomsClearance($id)
    {
        $track = Track::find($id);
        $this->trackUpdateStatus($track, 44);

        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Track',
            'key' => 'name',
            'value' => $track->tracking_code,
            'action' => 'Customs Clearance',
        ]));

        return redirect()->back();
    }

    public function trackCustomsCompleted($id)
    {
        $track = Track::find($id);
        $this->trackUpdateStatus($track, 25);

        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Track',
            'key' => 'name',
            'value' => $track->tracking_code,
            'action' => 'Customs Completed',
        ]));

        return redirect()->back();
    }

    public function trackUpdateStatus($track, $status)
    {
        if ($track->status != $status) {
            $track->status = $status;
            $track->save();

            // Send Notification
            if ($track->partner_id != 5 && $track->partner_id != 6)
                Notification::sendTrack($track->id, $status);
            (new PackageService())->updateStatus($track, $status);
        }
    }

    public function depesh_start($id)
    {
        $container = Container::find($id);
        if ($container) {
            $container->depesh_start = true;
            $container->depesh_start_at = date('Y-m-d H:i:s');
            $container->save();
        }

        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Parcel',
            'key' => 'name',
            'value' => $container->name,
            'action' => 'depesh start',
        ]));

        return redirect()->back();
    }

    public function depesh_stop($id)
    {
        $container = Container::find($id);
        if ($container) {
            $container->depesh_start = false;
            $container->save();
        }

        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Parcel',
            'key' => 'name',
            'value' => $container->name,
            'action' => 'depesh stop',
        ]));

        return redirect()->back();
    }

    public function sent($id)
    {
        $container = Container::with('tracks')->find($id);
        if ($container && $container->tracks) {

            foreach ($container->tracks as $track) {
                if ($track->status < 15) {
                    $track->status = 12;
                    $track->save();
                    $is_declared = PackageCarrier::where('track_id', $track->id)
                        ->whereNotNull('ecoM_REGNUMBER')
                        ->first();
                    if($track->partner_id == 9 && !$is_declared){
                        Notification::sendTrack($track->id, 'TAOBAO_SENT_UNDECLARED');
                    }elseif ($track->partner_id == 9 && $track->paid == 0){
                        Notification::sendTrack($track->id, 'TAOBAO_SENT_PAYMENT');
                    }

                    /* Send Notification */
                    if ($track->partner_id != 5 && $track->partner_id != 6)
                        Notification::sendTrack($track->id, '12');
                    (new PackageService())->updateStatus($track, 12);
                }
            }

            $container->sent = true;
            $container->save();
        }

        Alert::success(trans('saysay::crud.action_alert', [
            'name' => 'Parcel',
            'key' => 'name',
            'value' => $container->name,
            'action' => 'sent',
        ]));

        return redirect()->back();
    }

    public function indexObject()
    {
        $items = Container::whereHas('tracks')->with(['tracks' => function ($query) {
            $query->with('carrier')->get()->each->append('declared_weight_goods');
        }, 'partner', 'airboxes'])->withCount(['airboxes', 'tracks', 'trackcarriers', 'trackcarriersreg', 'trackcarriersdepesh', 'track_not_completed'])->orderBy('id', 'desc')->latest();

//        $items = Container::whereHas('tracks')->with(['tracks' => function ($query) {
//            $query->with('carrier')->get()->each->append('declared_weight_goods');
//        }, 'partner', 'airboxes'])->where('name','77110936800')->withCount(['airboxes', 'tracks', 'trackcarriers', 'trackcarriersreg', 'trackcarriersdepesh', 'track_not_completed'])->orderBy('id', 'desc')->latest();
//

        if (Request::get('parcel') != null) {
            $items->where('name', Request::get('parcel'));
        }
        if (Request::get('from_country') != null) {
            $items->where('from_country', Request::get('from_country'));
        }

        if (\Request::get('partner_id') != null) {
            $items->where('partner_id', \Request::get('partner_id'));
        }
        return $items->paginate($this->limit);
    }

    public function panelView($blade)
    {
        return 'admin.container.index';
    }

    public function update_name(\Illuminate\Http\Request $request, $containerId = null)
    {
        if ($containerId) {
            $container = Container::find($containerId);
        } else {
            $container = Container::find($request->input('container_id'));
        }
        if ($container && $request->filled('containerName')) {
            $container->name = $request->input('containerName');
            $container->save();
        }
        return response()->json(['success' => true, 'message' => "Successfully updated"]);
    }

    public function update_mawb(\Illuminate\Http\Request $request, $containerId = null)
    {
        if ($containerId) {
            $container = Container::find($containerId);
        } else {
            $container = Container::find($request->input('container_id'));
        }

        if (!$container) {
            return response()->json(['success' => false, 'message' => "Container not found"]);
        }

        $total_weight = 0;
        $total_count = 0;
        $bag_name = \Request::get('bagName');
        $tracking_codes = preg_split("/[;:,\s]+/", trim(\Request::get('trackings')));

        $notExistsTracks = [];
        $exists = [];

        foreach ($tracking_codes as $trackingCode) {
            $track = Track::where("tracking_code", $trackingCode)->first();
            if ($track) {
                $total_weight += $track->weight;
                $total_count += 1;
                $exists[] = $track->id;
            } else {
                $notExistsTracks[] = $trackingCode;
            }
        }


        if (!isset($exists[0])) {
            return response()->json(['success' => false, 'message' => "Given Trackings not found"]);
        }

        $box = Airbox::query()->where('name', $bag_name)->first();

        if ($box) {
            return response()->json(['success' => false, 'message' => "Box already exists"]);
        }

        $new_box = Airbox::query()->create([
            "name" => $bag_name,
            "partner_id" => $container->partner_id,
            "total_weight" => $total_weight,
            "total_count" => $total_count,
            'container_id' => $container->id
        ]);

        Track::query()->whereIn('id', $exists)->update([
            'airbox_id' => $new_box->id,
            'container_id' => $container->id
        ]);

        return response()->json(['success' => true, 'message' => "Successfully updated"]);
    }

    public function containerSend($id, $type)
    {
        $orderClasses = [
            'azeri_express' => ['order' => AzeriExpressOrder::class, 'package' => AzeriExpressPackage::class, 'statuses' => ['NOT_SENT', 'HAS_PROBLEM']],
            'azerpost' => ['order' => AzerpostOrder::class, 'package' => AzerpostPackage::class, 'statuses' => ['NOT_SENT', 'WAITING', 'HAS_PROBLEM']],
            'yenipoct' => ['order' => YenipoctOrder::class, 'package' => YenipoctPackage::class, 'statuses' => ['NOT_SENT', 'HAS_PROBLEM']],
            'kargomat' => ['order' => KargomatOrder::class, 'package' => KargomatPackage::class, 'statuses' => ['NOT_SENT', 'HAS_PROBLEM']],
            'surat' => ['order' => SuratOrder::class, 'package' => SuratPackage::class, 'statuses' => ['NOT_SENT', 'WAITING', 'HAS_PROBLEM']],
        ];

        if (!isset($orderClasses[$type])) {
            return back()->withErrors('Konteyner tapılmadı!');
        }

        $orderClass = $orderClasses[$type]['order'];
        $packageClass = $orderClasses[$type]['package'];
            $statusKeys = $orderClasses[$type]['statuses'];

        $container = $orderClass::find($id);
        if (!$container) {
            return back()->withErrors('Konteyner tapılmadı!');
        }

        $statuses = [];
        foreach ($statusKeys as $status) {
            $statuses[] = $packageClass::STATUSES[$status];
        }

        $parcels = $packageClass::where("{$type}_order_id", $id)
            ->whereIn('status', $statuses)
            ->get();
        foreach ($parcels as $parcel) {
            $parcel->company_sent = 1;
            $parcel->save();
        }
        $container->update([
            'status' => $orderClass::STATUSES['SENDING'],
            'user_sent_id' => Auth::user()->id,
            'sent_at' => Carbon::now(),
        ]);
        return redirect()->back();
    }


    public function containerCheck($id, $type)
    {
        if ($type == 'azeriexpress') {
            $result = AzeriExpressOrder::find($id);
            $parcels = AzeriExpressPackage::where('azeri_express_order_id', $id)->get();
        } elseif ($type == 'azerpost') {
            $result = AzerpostOrder::find($id);
            $parcels = AzerpostPackage::where('azerpost_order_id', $id)->get();
        } elseif ($type == 'yenipoct') {
            $result = YenipoctOrder::find($id);
            $parcels = YenipoctPackage::where('yenipoct_order_id', $id)->get();
        }elseif ($type == 'kargomat') {
            $result = KargomatOrder::find($id);
            $parcels = KargomatPackage::where('kargomat_order_id', $id)->get();
        } elseif ($type == 'precinct') {
            $result = PrecinctOrder::find($id);
            $parcels = PrecinctPackage::where('precinct_order_id', $id)->get();
        } elseif ($type == 'surat') {
            $result = SuratOrder::find($id);
            $parcels = SuratPackage::where('surat_order_id', $id)->get();
        } else {
            $result = null;
        }

        if (!$result) {
            echo 'Result Error';
            exit();
        }


        return view('admin.container.check', compact('id', 'type', 'result', 'parcels'));
    }


    public function containerCheckPost(\Illuminate\Http\Request $request)
    {
        $type = $request->type;
        $id = $request->id;
        $barcode = $request->barcode;

        if ($type == 'azeriexpress') {
            $result = AzeriExpressOrder::find($id);
            $parcel = AzeriExpressPackage::where('barcode', $barcode)->first();
            if ($parcel) {
                $parcel_order_id = $parcel->azeri_express_order_id;
            }
        } elseif ($type == 'azerpost') {
            $result = AzerpostOrder::find($id);
            $parcel = AzerpostPackage::where('barcode', $barcode)->first();
            if ($parcel) {
                $parcel_order_id = $parcel->azerpost_order_id;
            }
        } elseif ($type == 'yenipoct') {
            $result = YenipoctOrder::find($id);
            $parcel = YenipoctPackage::where('barcode', $barcode)->first();
            if ($parcel) {
                $parcel_order_id = $parcel->yenipoct_order_id;
            }
        }elseif ($type == 'kargomat') {
            $result = KargomatOrder::find($id);
            $parcel = KargomatPackage::where('barcode', $barcode)->first();
            if ($parcel) {
                $parcel_order_id = $parcel->kargomat_order_id;
            }
        } elseif ($type == 'precinct') {
            $result = PrecinctOrder::find($id);
            $parcel = PrecinctPackage::where('barcode', $barcode)->first();
            if ($parcel) {
                $parcel_order_id = $parcel->precinct_order_id;
            }
        } elseif ($type == 'surat') {
            $result = SuratOrder::find($id);
            $parcel = SuratPackage::where('barcode', $barcode)->first();
            if ($parcel) {
                $parcel_order_id = $parcel->surat_order_id;
            }
        } else {
            return redirect()->back()->with(['message' => 'Type tapılmadı !', 'status' => 'error']);
        }

        if (!$parcel) {
            return redirect()->back()->with(['message' => 'Bağlama tapılmadı !', 'status' => 'error']);
        } elseif (!$result) {
            return redirect()->back()->with(['message' => 'Kontayner tapılmadı !', 'status' => 'error']);
        }

        if ($result->id != $parcel_order_id) {
            return redirect()->back()->with(['message' => 'Bağlama bu Konteynera aid deyil !', 'status' => 'error']);
        }

        if ($parcel->check == 1) {
            return redirect()->back()->with(['message' => 'Bağlama artıq yoxlanılıb !', 'status' => 'error']);
        }

        if($result && $result->first_check_date == null){
            $result->first_check_date = date('Y-m-d H:i:s');
            $result->save();
        }

        $parcel->check = 1;
        $parcel->save();

        return redirect()->back()->with(['message' => 'Yoxlama uğurlu oldu', 'status' => 'success']);
    }

    public function checkedExcel(\Illuminate\Http\Request $request){
        $type = $request->input('type');
        $id = $request->input('id');

        if ($type == 'azeriexpress') {
            $result = AzeriExpressOrder::query()->find($id);
            $parcels = AzeriExpressPackage::query()->with(['package.user', 'track.customer'])->where('azeri_express_order_id', $id)->where('check',1)->get();
        } elseif ($type == 'azerpost') {
            $result = AzerpostOrder::query()->find($id);
            $parcels = AzerpostPackage::query()->with(['package.user', 'track.customer'])->where('azerpost_order_id', $id)->where('check',1)->get();
        } elseif ($type == 'yenipoct') {
            $result = YenipoctOrder::query()->find($id);
            $parcels = YenipoctPackage::query()->with(['package.user', 'track.customer'])->where('yenipoct_order_id', $id)->where('check',1)->get();
        }elseif ($type == 'kargomat') {
            $result = KargomatOrder::query()->find($id);
            $parcels = KargomatPackage::query()->with(['package.user', 'track.customer'])->where('kargomat_order_id', $id)->where('check',1)->get();
        } elseif ($type == 'precinct') {
            $result = PrecinctOrder::query()->find($id);
            $parcels = PrecinctPackage::query()->with(['package.user', 'track.customer'])->where('precinct_order_id', $id)->where('check',1)->get();
        } elseif ($type == 'surat') {
            $result = SuratOrder::query()->find($id);
            $parcels = SuratPackage::query()->with(['package.user', 'track.customer'])->where('surat_order_id', $id)->where('check',1)->get();
        } else {
            $result = null;
        }

        if (!$result) {
            echo 'Result Error';
            exit();
        }
        $excel = app()->make(Excel::class);
        return $excel->download(new ContainerExport($parcels), 'container_' . $id . '.xlsx');
    }

    public function unCheckedExcel(\Illuminate\Http\Request $request){
        //not needed for now because barcodes should not be visible
        $type = $request->input('type');
        $id = $request->input('id');

        if ($type == 'azeriexpress') {
            $result = AzeriExpressOrder::query()->find($id);
            $parcels = AzeriExpressPackage::query()->with(['package.user', 'track.customer'])->where('azeri_express_order_id', $id)->where('check',1)->get();
        } elseif ($type == 'azerpost') {
            $result = AzerpostOrder::query()->find($id);
            $parcels = AzerpostPackage::query()->with(['package.user', 'track.customer'])->where('azerpost_order_id', $id)->where('check',1)->get();
        } elseif ($type == 'yenipoct') {
            $result = YenipoctOrder::query()->find($id);
            $parcels = YenipoctPackage::query()->with(['package.user', 'track.customer'])->where('yenipoct_order_id', $id)->where('check',1)->get();
        }elseif ($type == 'kargomat') {
            $result = KargomatOrder::query()->find($id);
            $parcels = KargomatPackage::query()->with(['package.user', 'track.customer'])->where('kargomat_order_id', $id)->where('check',1)->get();
        } elseif ($type == 'precinct') {
            $result = PrecinctOrder::query()->find($id);
            $parcels = PrecinctPackage::query()->with(['package.user', 'track.customer'])->where('precinct_order_id', $id)->where('check',1)->get();
        } elseif ($type == 'surat') {
            $result = SuratOrder::query()->find($id);
            $parcels = SuratPackage::query()->with(['package.user', 'track.customer'])->where('surat_order_id', $id)->where('check',1)->get();
        } else {
            $result = null;
        }

        if (!$result) {
            echo 'Result Error';
            exit();
        }
        $excel = app()->make(Excel::class);
        return $excel->download(new ContainerExport($parcels), 'container_' . $id . '.xlsx');

    }


}
