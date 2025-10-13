<?php

namespace App\Http\Controllers\Admin\Precinct;

use App\Exports\Admin\PrecinctExport;
use App\Exports\Admin\Reports\ReportsExport;
use App\Http\Controllers\Controller;
use App\Models\DeliveryPoint;
use App\Models\Extra\Notification;
use App\Models\Package;
use App\Models\Precinct\PrecinctOffice;
use App\Models\Precinct\PrecinctOrder;
use App\Models\Precinct\PrecinctPackage;
use App\Models\Track;
use App\Services\Package\PackageService;
use App\Services\Precinct\PrecinctService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Maatwebsite\Excel\Excel;
use Milon\Barcode\DNS1D;

class PrecinctController extends Controller
{
    public function __construct()
    {

    }

    public function index(Request $request)
    {
        $packagesQuery = PrecinctPackage::query()
            ->with(['container']);

        if (!auth()->user()->can('precinct_see_all_packages')) {
            $packagesQuery = $packagesQuery->whereHas('container', function ($query) {
                return $query->where('precinct_office_id', auth()->user()->store_status);
            });
        }

        if (!auth()->user()->can('add-precinct_package_to_containers')) {
            $packagesQuery = $packagesQuery->whereIn('status', [3, 4, 6, 8, 10]);
        }

        $packagesQuery->when($request->filled('code'), function ($query) use ($request) {
            return $query->where('barcode', 'like', '%' . $request->get('code') . '%');
        });

        $packagesQuery->when($request->filled('status'), function ($query) use ($request) {
            $statuses = $request->get('status');
            if (is_array($statuses)) {
                return $query->whereIn('status', $statuses);
            }
            return $query->where('status', $statuses);
        });

        $packagesQuery->when($request->filled('passport_fin') || $request->filled('c_code'), function ($query) use ($request) {
            return $query->whereHas('package', function ($query2) use ($request) {
                return $query2->whereHas('user', function ($query3) use ($request) {
                    if ($request->filled('passport_fin')) {
                        $query3->where('fin', 'like', '%' . $request->get('passport_fin') . '%');
                    }
                    if ($request->filled('c_code')) {
                        $query3->where('customer_id', 'like', '%' . $request->get('c_code') . '%');
                    }
                    return $query3;
                });
            })->orWhereHas('track', function ($query2) use ($request) {
                return $query2->whereHas('customer', function ($query3) use ($request) {
                    if ($request->filled('passport_fin')) {
                        return $query3->where('fin', 'like', '%' . $request->get('passport_fin') . '%');
                    }
                });
            });
        });

        $packagesQuery->when($request->filled('office'), function ($query) use ($request) {
            return $query->whereHas('container', function ($q) use ($request) {
                return $q->where('precinct_office_id', $request->get('office'));
            });
        });

        $columnName = 'updated_at';
        if($request->filled('export')){
            $columnName = 'created_at';
        }

        if($request->filled('by_accepted_at')){
            $columnName = 'accepted_at';
        }

        $packagesQuery->when($request->filled('start_date'), function ($query) use ($request, $columnName) {
            $date = Carbon::createFromFormat('Y-m-d', $request->get('start_date'));
            return $query->where($columnName, '>=', $date->startOfDay());
        });

        $packagesQuery->when($request->filled('end_date'), function ($query) use ($request, $columnName) {
            $date = Carbon::createFromFormat('Y-m-d', $request->get('end_date'));
            return $query->where($columnName, '<=', $date->endOfDay());
        });

        if ($request->filled('export') && $request->get('export') == 1) {
            $excel = app()->make(Excel::class);
            $date = now()->format('Y-m-d-m-Y-H-i-s');
            return $excel->download(new ReportsExport($packagesQuery->get(), 'precinct'), 'precinct-report-' . $date . '.xlsx');
        }

        $offices = DeliveryPoint::query()->get();

        return view('admin.precinct.index', [
            'packages' => $packagesQuery->paginate(25),
            'total_packages' => $packagesQuery->count(),
            'statuses' => PrecinctPackage::STATUSES,
            'offices' => $offices
        ]);
    }

    public function notSendPackages(Request $request)
    {
        $packagesQuery = PrecinctPackage::query()
            ->where('payment_status', false)
            ->with(['container']);

        $packagesQuery->when($request->filled('status'), function ($query) use ($request) {
            return $query->whereHas('package', function ($pQ) use ($request) {
                return $pQ->where('paid', $request->get('status'));
            });
        });

        $packagesQuery->when($request->filled('region'), function ($query) use ($request) {
            return $query->whereHas('package', function ($query2) use ($request) {
                return $query2->whereHas('trendyolPackage', function ($query3) use ($request) {
                    $regions = [
                        0 => 'Yasamal',
                        1 => 'Nəsimi'
                    ];
                    return $query3->where('buyer_region', 'like', '%' . $regions[$request->get('region')] . '%');
                });
            });
        });

        return view('admin.precinct.not-send-packages', [
            'packages' => $packagesQuery->paginate(25),
            'total_packages' => $packagesQuery->count(),
            'statuses' => PrecinctPackage::STATUSES
        ]);
    }

    public function containers(Request $request)
    {
        $offices = DeliveryPoint::all();

        $rowsQuery = PrecinctOrder::query()
            ->with(['sender', 'creator'])
            ->withCount(['packages'])
            ->whereNotNull('user_id');

        if (!auth()->user()->can('precinct_see_all_packages')) {
            $rowsQuery = $rowsQuery->where('precinct_office_id', auth()->user()->store_status);
        }

        $rowsQuery = $rowsQuery->when($request->filled('status'), function ($query) use ($request) {
            if ($request->get('status') == PrecinctOrder::STATUSES['WAITING']) {
                return $query->whereIn('status', [PrecinctOrder::STATUSES['WAITING'], PrecinctOrder::STATUSES['SENDING']]);
            }
            return $query->where('status', $request->get('status'));
        }, function ($query) {
            return $query->whereIn('status', [PrecinctOrder::STATUSES['WAITING'], PrecinctOrder::STATUSES['SENDING']]);
        });

        $rowsQuery = $rowsQuery->when($request->filled('office'), function ($query) use ($request) {
            return $query->where('precinct_office_id', $request->get('office'));
        });

        $rowsQuery = $rowsQuery->when(
            $request->filled('date-from') && $request->filled('date-to'),
            function ($query) use ($request) {
                $dateFrom = Carbon::parse($request->get('date-from'))->startOfDay();
                $dateTo = Carbon::parse($request->get('date-to'))->endOfDay();

                if ($request->filled('status') && $request->get('status') == PrecinctOrder::STATUSES['SENT']) {
                    return $query->where('sent_at', '>=', $dateFrom)
                        ->where('sent_at', '<=', $dateTo);
                }
                return $query->where('updated_at', '>=', $dateFrom)
                    ->where('updated_at', '<=', $dateTo);
            }
        );

        $totalPackagesCount  = PrecinctPackage::query()->whereIn('precinct_order_id', $rowsQuery->pluck('id'))->count();

        $rows = $rowsQuery->withCount([
            // Count NOT_SENT packages
            'packages as not_sent_count' => function ($query) {
                $query->whereIn('status', [
                    PrecinctPackage::STATUSES['NOT_SENT'],
                    PrecinctPackage::STATUSES['WAITING'],
                    PrecinctPackage::STATUSES['HAS_PROBLEM']
                ]);
            },
            // Count SEND packages
            'packages as sent_count' => function ($query) {
                $query->where('status', PrecinctPackage::STATUSES['SENT']);
            },
            // Count of not accepted
            'packages as not_accepted_count' => function ($query) {
                $query->whereIn('status', [
                    PrecinctPackage::STATUSES['NOT_SENT'],
                    PrecinctPackage::STATUSES['HAS_PROBLEM'],
                    PrecinctPackage::STATUSES['SENT'],
                    PrecinctPackage::STATUSES['WAITING'],
                ]);
            },
        ])
            ->orderByDesc('id')
            ->paginate(20);
        $statuses = PrecinctOrder::STATUSES;

        return view('admin.precinct.containers.index', compact('rows', 'offices', 'statuses', 'totalPackagesCount'));
    }

    public function storeContainer(Request $request): RedirectResponse
    {
        $authId = Auth::user()->id;
        PrecinctOrder::query()->create([
            'name' => $request->input('name'),
            'user_id' => $authId,
            'precinct_office_id' => $request->input('precinct_office_id'),
            'status' => PrecinctOrder::STATUSES['WAITING'],
            'barcode' => (new PrecinctService())->generateNewBarcode(),
            'created_at' => now()
        ]);

        return back()->with('success', $request->input('name') . ' created');
    }

    public function editContainer(Request $request, $id)
    {

        $group = PrecinctOrder::where('id', $id)->first();
        if (!$group) {
            return back()->withErrors('Container not found');
        }

        $packagesCount = PrecinctPackage::where('precinct_order_id', $id)->count();
        $sentPackagesCount = PrecinctPackage::where('precinct_order_id', $id)->whereNotIn('status', [PrecinctPackage::STATUSES['HAS_PROBLEM'], PrecinctPackage::STATUSES['NOT_SENT']])->count();
        $notSentPackagesCount = PrecinctPackage::where('precinct_order_id', $id)->where('status', PrecinctPackage::STATUSES['NOT_SENT'])->count();
        $packagesProblemCount = PrecinctPackage::where([
            'precinct_order_id' => $id,
            'status' => PrecinctPackage::STATUSES['HAS_PROBLEM']
        ])->count();

        $packages = PrecinctPackage::where('precinct_order_id', $id)
            ->with(['container', 'package.user', 'track.customer']);

        if ($request->has('export')) {
            $date = $packages->first()->container->created_at;
            $b = $date ? (clone $date->startOfDay()) : now()->startOfDay();
            $e = $date ? (clone $date->endOfDay()) : now()->endOfDay();
            $_containers = PrecinctOrder::query()->with(['packages.package.user', 'packages.track.customer'])->whereBetween('created_at', [$b, $e])->get();
//            $packages = $packages->orderBy('status')->get();
//            return (new PrecinctExport($_containers))->view();
            $excel = app()->make(Excel::class);
            return $excel->download(new PrecinctExport($_containers), 'precinct_container_' . $id . '.xlsx');
        }

        if ($request->filled('status') && $request->filled('status') == PrecinctPackage::STATUSES['HAS_PROBLEM']) {
            $packages->where('status', '=', PrecinctPackage::STATUSES['HAS_PROBLEM']);
        }

        $packages = $packages->orderByRaw('CAST(status AS UNSIGNED) ASC')->paginate(100);

        return view('admin.precinct.containers.show', compact('group', 'packages', 'packagesCount', 'packagesProblemCount', 'sentPackagesCount', 'notSentPackagesCount'));
    }

    public function store(Request $request, $id)
    {
        $container = PrecinctOrder::query()->where('id', $id)->first();
        if (!$container) {
            return back()->withErrors('Konteyner movcud deyil!');
        }

        if (!$request->filled('barcode')) {
            return back()->withErrors('Barkod daxil edilməyib!');
        }

        $barcode = $request->input('barcode');

        $package = Package::query()
            ->where('custom_id', $barcode)
            ->first();

        $track = Track::query()
            ->where('tracking_code', $barcode)
            ->first();

        if ($package && optional($package->delivery_point)->id === 20 && !$package->paid) {
            return back()->withErrors('Bağlama Balakən filialındadır və ödənişi ödənilməyib!');
        }

        if (!$package && !$track) {
            return back()->withErrors('Bağlama mövcud deyil!');
        }

        if (isset($package) && $package->paid == 0 && !in_array($package->store_status, [1,3,4,7,8])) {
            return back()->withErrors('Bağlama ödənilməyib!');
        }

        $preventDuplicate = PrecinctPackage::query()
            ->where([
                'type' => $package ? 'package' : 'track',
                'package_id' => $package ? $package->id : $track->id,
                'precinct_order_id' => $container->id,
            ])
            ->where('status', '!=', PrecinctPackage::STATUSES['HAS_PROBLEM'])
            ->first();

        if ($preventDuplicate) {
            return back()->withErrors($barcode . ' artıq əlavə edilib!');
        }


        PrecinctPackage::query()->updateOrCreate([
            'package_id' => $package ? $package->id : $track->id,
            'barcode' => $package ? $package->custom_id : $track->tracking_code,
            'type' => $package ? 'package' : 'track',
        ], [
            'precinct_order_id' => $id,
            'user_id' => $package ? $package->user_id : $track->customer_id,
            'package_id' => $package ? $package->id : $track->id,
            'status' => PrecinctPackage::STATUSES['NOT_SENT'],
            'added_by' => Auth::user()->id,
            'barcode' => $package ? $package->custom_id : $track->tracking_code,
            'payment_status' => $package ? $package->paid : $track->paid,
        ]);

        PrecinctOrder::query()
            ->where([
                'id' => $container->id
            ])
            ->update([
                'weight' => floatval($container->weight) + floatval($package ? $package->weight : $track->weight),
            ]);
        $compact['success'] = $barcode . ' uğurla əlavə edildi!';

        return back()->with($compact);
    }

    public function destroyGroup($id)
    {
        $find = PrecinctOrder::find($id);
        if (!$find) {
            return back()->withErrors('tapılmadı');
        }

//        if ($find->status == PrecinctOrder::STATUSES['SENT']) {
//            return back()->withErrors('SMS göndərilib statusundadır. Silmək olmaz!');
//        }

        PrecinctPackage::query()
            ->where('precinct_order_id', $find->id)
            ->delete();
        $find->delete();

        return back()->withSuccess('Silindi');
    }

    public function deletePackage($id)
    {
        $find = PrecinctPackage::find($id);
        if (!$find) {
            return back()->withErrors('Bağlama tapılmadı');
        }

//        if (in_array($find->status, [
//            PrecinctPackage::STATUSES['SENT'],
//            PrecinctPackage::STATUSES['IN_PROCESS'],
//            PrecinctPackage::STATUSES['WAREHOUSE'],
//            PrecinctPackage::STATUSES['ARRIVEDTOPOINT'],
//            PrecinctPackage::STATUSES['DELIVERED']
//        ])) {
//            return back()->withErrors('Bağlama artıq göndərilib statusundadır. Silmək olmaz!');
//        }

        $find->delete();
        return back()->withSuccess('Silindi');
    }

    public function updateContainer(Request $request)
    {
        $order = PrecinctOrder::findOrFail($request->id);

        $data = [
            'name' => $request->name,
            'status' => $request->filled('status') ? $request->status : $order->status,
        ];

        $order->update($data);

        return back()->withSuccess('Konteyner yeniləndi');
    }

    public function print($id, Request $request)
    {
        $order = PrecinctOrder::query()->withCount(['sentPackages'])->findOrFail($id);

        $dns1d = new DNS1D();
        $barcode = $dns1d->getBarcodeSvg($order->barcode, "C128", 2.5, 80);
        $view = view('panel.exports.189_print', compact('order', 'barcode'));
        return $view->render();
    }

    public function offices(Request $request)
    {
        $offices = DeliveryPoint::whereNotIn('id', [1, 2])->paginate(15);

        return view('admin.precinct.offices.index', compact('offices'));
    }

    public function storeOffice(Request $request): RedirectResponse
    {
        PrecinctOffice::query()->create([
            'name' => $request->input('name'),
            'note' => $request->input('note'),
            'created_at' => now()
        ]);

        return back()->with('success', $request->input('name') . ' created');
    }

    public function deleteOffice($id)
    {
        $find = PrecinctOffice::find($id);
        if (!$find) {
            return back()->withErrors('tapılmadı');
        }
        $find->delete();

        return back()->withSuccess('Silindi');
    }

    public function sendPackages(Request $request, $id)
    {
        $container = PrecinctOrder::query()->where('id', $id)->first();
        if (!$container) {
            return back()->withErrors('Konteyner tapılmadı!');
        }

        if ($container->status !== PrecinctOrder::STATUSES['WAITING']) {
            return back()->withErrors('Artıq Göndərilmə siyahısına əlavə edilib!');
        }

        $data = [];
        $tracks = PrecinctPackage::query()->with(['track'])->where([
            'status' => PrecinctPackage::STATUSES['NOT_SENT'],
            'precinct_order_id' => $container->id
        ])
            ->when($request->filled('temu'), function ($query) {
                $query->whereHas('track', function ($subQuery) {
                    $subQuery->where('partner_id', 8);
                });
            }, function ($query) {
                $query->whereHas('track', function ($subQuery) {
                    $subQuery->where('partner_id', '!=', 8);
                });
            })
            ->get();

        $packages = collect();
        if (!$request->filled('temu')) {
            $packages = PrecinctPackage::with(['container.precinctOffice', 'package.user'])
                ->whereIn('status', [
                    PrecinctPackage::STATUSES['NOT_SENT'],
                    PrecinctPackage::STATUSES['WAITING'],
                    PrecinctPackage::STATUSES['HAS_PROBLEM'],
                ])
                ->where('precinct_order_id', $container->id)
                ->where('type', 'package')
                ->get();
        }

        $all = $tracks->merge($packages);
        $assignAdmin = Auth::user()->name ?? null;
        foreach ($all as $package) {
            if ($package->type == 'package') {
                $_package = Package::find($package->package_id);
                $_package->bot_comment = "Bağlama Precinct-ə göndərildi".' - '.$assignAdmin;
                $_package->save();
            }
            if ($package->type == 'track') {
                $_track = Track::find($package->package_id);
                $_track->comment_txt = "Bağlama Precinct-ə göndərildi".' - '.$assignAdmin;
                $_track->save();
            }
        }
        PrecinctPackage::query()
            ->where([
                'status' => PrecinctPackage::STATUSES['NOT_SENT'],
                'precinct_order_id' => $container->id
            ])
            ->update([
                'status' => PrecinctPackage::STATUSES['SENT']
            ]);
        $container->update([
            'status' => PrecinctOrder::STATUSES['SENT'],
            'user_sent_id' => Auth::user()->id,
            'sent_at' => Carbon::now(),
        ]);

        return back()->withSuccess('Göndərilməyib statusunda olan bağlamalar Məntəqəyə göndərilir!');
    }

    public function acceptPackages(Request $request, $id)
    {
        $container = PrecinctOrder::query()->where('id', $id)->first();
        if (!$container) {
            return back()->withErrors('Konteyner tapılmadı!');
        }

        if ($container->status !== PrecinctOrder::STATUSES['SENT']) {
            return back()->withErrors('Artıq qəbul edilib!');
        }

        PrecinctPackage::query()
            ->where([
                'status' => PrecinctPackage::STATUSES['SENT'],
                'precinct_order_id' => $container->id
            ])
            ->update([
                'accepted_at' => now(),
                'status' => PrecinctPackage::STATUSES['ARRIVEDTOPOINT']
            ]);

        //PACKAGES
        $packages = PrecinctPackage::query()
            ->where([
                'type' => 'package',
                'status' => PrecinctPackage::STATUSES['SENT'],
                'precinct_order_id' => $container->id
            ])->get()->pluck('package_id');

        $_packages = Package::query()->whereIn('id', $packages)->get();
        foreach ($_packages as $_package) {
            $_package->bot_comment = "Bağlama Precinct(" . Auth::id() . '-' . Auth::user()->email . ") tərəfindən qəbul edildi";
            $_package->status = Package::STATES['InBaku'];
            $_package->save();
            Notification::sendPackage($_package->id, 2);//todo :must change to PrecinctPackage::STATUSES["ARRIVEDTOPOINT"]
        }

        //TRACKS
        $tracks = PrecinctPackage::query()
            ->where([
                'type' => 'track',
                'status' => PrecinctPackage::STATUSES['SENT'],
                'precinct_order_id' => $container->id
            ])
            ->get()
            ->pluck('package_id');
        $_tracks = Track::query()->whereIn('id', $tracks)->get();

        foreach ($_tracks as $track) {
            $track->comment_txt = "Bağlama Precinct(" . Auth::id() . '-' . Auth::user()->email . ") tərəfindən qəbul edildi";
            $track->status = Track::STATES['InBaku'];
            $track->save();
            (new PackageService())->updateStatus($track, 16);
            Notification::sendTrack($_track->id, 16);//todo :must change to PrecinctPackage::STATUSES["ARRIVEDTOPOINT"]
        }

        $container->update([
            'status' => PrecinctOrder::STATUSES['ACCEPTED'],
        ]);

        return back()->withSuccess('Göndərilib statusunda olan bağlamalar qəbul edildi!');
    }

    public function acceptPackage(Request $request)
    {
        $request->validate([
            'container' => 'required',
            'barcode' => 'required'
        ]);

        $container = PrecinctOrder::query()->where('id', $request->input('container'))->first();
        if (!$container) {
            return back()->withErrors('Konteyner tapılmadı!');
        }

        $package = PrecinctPackage::query()
            ->where([
                'barcode' => $request->input('barcode'),
                'precinct_order_id' => $container->id
            ])->first();

        if (!$package) {
            return back()->withErrors('Bağlama bu konteynerde mövcud deyil!');
        }

        if ($package->status == PrecinctPackage::STATUSES['HAS_PROBLEM']) {
            return back()->withErrors('Bağlama problemli olduğu üçün qəbul edilə bilməz!');
        }

        if (in_array($package->status, [PrecinctPackage::STATUSES['IN_PROCESS'], PrecinctPackage::STATUSES['WAREHOUSE'], PrecinctPackage::STATUSES['ARRIVEDTOPOINT']])) {
            return back()->withErrors('Bağlama artıq qəbul edilib!');
        }

        $package->update([
            'accepted_at' => now(),
            'status' => PrecinctPackage::STATUSES['ARRIVEDTOPOINT']
        ]);

        if ($package->type == 'package') {
            $_package = Package::find($package->package_id);
            $_package->bot_comment = "Bağlama Precinct(" . Auth::id() . '-' . Auth::user()->email . ") tərəfindən qəbul edildi";
            $_package->status = Package::STATES['InBaku'];
            $_package->save();

            if(in_array($_package->store_status,[3,4,7,8]) && $_package->paid == 0){
                //niyese burda bildirim gedir asagidaki 2 filiala ona esasen yazilib
                if ($_package->store_status != 2 || $_package->store_status != 1){
                    Notification::sendPackage($package->package_id, 'Precint_notpaid');
                }
            }
            Notification::sendPackage($package->package_id, Package::STATES['InBaku']);
        }

        if ($package->type == 'track') {
            $_track = Track::find($package->package_id);
            $_track->comment_txt = "Bağlama Precinct(" . Auth::id() . '-' . Auth::user()->email . ") tərəfindən qəbul edildi";
            $_track->status = Track::STATES['InBaku'];
            $_track->save();

            (new PackageService())->updateStatus($_track, 16);
            Notification::sendTrack($package->package_id, Track::STATES['InBaku']);
        }

        return back()->withSuccess('Bağlama qəbul edildi!');
    }

    public function handover($id, Request $request)
    {
        Log::info("StatusLog: ", [$request->all()]);

        $precinctPackage = PrecinctPackage::query()->where('id', $id)->firstOrFail();

        $packageQuery = Package::query()->where('id', $precinctPackage->package_id);
        $trackQuery = Track::query()->where('id', $precinctPackage->package_id);

        $status = 3;
        if ($precinctPackage->status != $status || !$precinctPackage->package->paid) {
            $precinctPackage->update([
                'status' => PrecinctPackage::STATUSES['DELIVERED']
            ]);
            $_package = $packageQuery->first();
            $_track = $trackQuery->first();

            if ($_package && $precinctPackage->type == 'package') {
                $_package->bot_comment = "Bağlama Precinct(" . Auth::id() . '-' . Auth::user()->email . ") tərəfindən təhvil verildi.";
                $_package->status = Package::STATES['Done'];
                $_package->save();
            }
            if ($_track && $precinctPackage->type == 'track') {
                $_track->comment_txt = "Bağlama Precinct(" . Auth::id() . '-' . Auth::user()->email . ") tərəfindən təhvil verildi.";
                $_track->status = Track::STATES['Done'];
                $_track->save();

                (new PackageService())->updateStatus($_track, 17);
            }

            //update container's status
            $container = $precinctPackage->container;
            $undeliveredPackagesCount = PrecinctPackage::query()
                ->where('precinct_order_id', $container->id)
                ->whereIn('status', [PrecinctPackage::STATUSES['IN_PROCESS'], PrecinctPackage::STATUSES['WAREHOUSE'], PrecinctPackage::STATUSES['ARRIVEDTOPOINT']])
                ->count();
            if ($undeliveredPackagesCount == 0) {
                PrecinctOrder::query()->where('id', $container->id)->update([
                    'status' => PrecinctOrder::STATUSES['DELIVERED']
                ]);
            }

            return response()->json(['status' => true, 'data' => []]);
        }

        return response()->json(['status' => false, 'data' => [], 'message' => "Bağlama anbara qəbul edilməyib və ya ödənilməyib!"]);
    }

    public function receipt(Request $request)
    {
        $packageIds = $request->get('packages') ?? [];
        $trackIds = $request->get('tracks') ?? [];

        $packages = Package::query()->whereIn('id', $packageIds)->get();
        $tracks = Track::query()->whereIn('id', $trackIds)->get();

        return view('admin.precinct.receipt', compact('packages', 'tracks'));
    }
}
