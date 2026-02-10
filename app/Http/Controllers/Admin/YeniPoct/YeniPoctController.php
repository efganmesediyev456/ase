<?php

namespace App\Http\Controllers\Admin\YeniPoct;

use App\Exports\Admin\YeniPoctExport;
use App\Exports\Admin\Reports\ReportsExport;
use App\Http\Controllers\Controller;
use App\Imports\BarcodesImport;
//use App\Jobs\SendPackageToYeniPoctJob;
use App\Models\Partner;
use App\Models\YeniPoct\YenipoctOffice;
use App\Models\YeniPoct\YenipoctOrder;
use App\Models\YeniPoct\YenipoctPackage;
use App\Models\Package;
use App\Models\Track;
//use App\Services\YeniPoct\YeniPoctService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Maatwebsite\Excel\Excel;
use Milon\Barcode\DNS1D;

class YeniPoctController extends Controller
{
    public function __construct()
    {

    }

    public function index(Request $request)
    {
        $packagesQuery = YenipoctPackage::query()
            ->with(['container']);

        $packagesQuery->when($request->filled('code'), function ($query) use ($request) {
            return $query->where('barcode', 'like', '%' . $request->get('code') . '%');
        });

        $packagesQuery->when($request->filled('status'), function ($query) use ($request) {
            return $query->where('status', $request->get('status'));
        });

        $packagesQuery->when($request->filled('passport_fin') || $request->filled('phone'), function ($query) use ($request) {
            return $query->whereHas('package', function ($query2) use ($request) {
                return $query2->whereHas('user', function ($query3) use ($request) {
                    if ($request->filled('passport_fin')) {
                        $query3->where('passport_fin', 'like', '%' . $request->get('passport_fin') . '%');
                    }
                    if ($request->filled('phone')) {
                        $query3->where('mobile', 'like', '%' . $request->get('phone') . '%');
                    }
                    return $query3;
                });
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

        $columnName = 'updated_at';
        if($request->filled('export')){
            $columnName = 'created_at';
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
            $date = now()->format('Y-m-d');
            return $excel->download(new ReportsExport($packagesQuery->get(), 'yp'), 'yp-report-' . $date . '.xlsx');
        }

        return view('admin.yenipoct.index', [
            'packages' => $packagesQuery->paginate(25),
            'total_packages' => $packagesQuery->count(),
            'statuses' => YenipoctPackage::STATUSES
        ]);
    }

    public function notSendPackages(Request $request)
    {
        $packagesQuery = YenipoctPackage::query()
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

        return view('admin.yenipoct.not-send-packages', [
            'packages' => $packagesQuery->paginate(25),
            'total_packages' => $packagesQuery->count(),
            'statuses' => YenipoctPackage::STATUSES
        ]);
    }

    public function containers(Request $request)
    {
        $offices = YeniPoctOffice::all();
        $statuses = YenipoctOrder::STATUSES;

        $rowsQuery = YenipoctOrder::query()
            ->with(['sender', 'creator'])
            ->withCount(['packages'])
            ->whereNotNull('user_id');

        $rowsQuery = $rowsQuery->when($request->filled('status'), function ($query) use ($request) {
            if($request->get('status') == YenipoctOrder::STATUSES['WAITING']){
                return $query->whereIn('status', [YenipoctOrder::STATUSES['WAITING'], YenipoctOrder::STATUSES['SENDING']]);
            }
            return $query->where('status', $request->get('status'));
        }, function ($query) {
            return $query->whereIn('status', [YenipoctOrder::STATUSES['WAITING'], YenipoctOrder::STATUSES['SENDING']]);
        });

        $rowsQuery = $rowsQuery->when(
            $request->filled('date-from') && $request->filled('date-to'),
            function ($query) use ($request) {
                $dateFrom = Carbon::parse($request->get('date-from'))->startOfDay();
                $dateTo = Carbon::parse($request->get('date-to'))->endOfDay();

                if($request->filled('status') && $request->get('status') == YenipoctOrder::STATUSES['SENT']){
                    return $query->where('sent_at', '>=', $dateFrom)
                        ->where('sent_at', '<=', $dateTo);
                }
                return $query->where('updated_at', '>=', $dateFrom)
                    ->where('updated_at', '<=', $dateTo);
            }
        );

        $totalPackagesCount  = YenipoctPackage::query()->whereIn('yenipoct_order_id', $rowsQuery->pluck('id'))->count();

        $rows = $rowsQuery
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.yenipoct.containers', compact('rows', 'offices', 'statuses', 'totalPackagesCount'));
    }

    public function storeContainer(Request $request): RedirectResponse
    {
        $authId = Auth::user()->id;
        YenipoctOrder::query()->create([
            'name' => $request->input('name'),
            'user_id' => $authId,
            'yenipoctoffice_id' => $request->input('yenipoctoffice_id'),
            'status' => YenipoctOrder::STATUSES['WAITING'],
            'barcode' => (new YeniPoctService())->generateNewBarcode(),
            'created_at' => now()
        ]);

        return back()->with('success', $request->input('name') . ' created');
    }

    public function editContainer(Request $request, $id)
    {
        $container = YenipoctOrder::where('id', $id)->first();
        if (!$container) {
            return back()->withErrors('YeniPoct container not found');
        }

        $packagesCount = YenipoctPackage::query()->where('yenipoct_order_id', $id)->count();
        $sentPackagesCount = YenipoctPackage::query()->where('yenipoct_order_id', $id)->whereNotIn('status', [YenipoctPackage::STATUSES['HAS_PROBLEM'], YenipoctPackage::STATUSES['NOT_SENT']])->count();
        $notSentPackagesCount = YenipoctPackage::query()->where('yenipoct_order_id', $id)->where('status', YenipoctPackage::STATUSES['NOT_SENT'])->count();
        $packagesProblemCount = YenipoctPackage::query()->where([
            'yenipoct_order_id' => $id,
            'status' => YenipoctPackage::STATUSES['HAS_PROBLEM']
        ])->count();

        $packages = YenipoctPackage::query()->where('yenipoct_order_id', $id)
            ->with(['container', 'package.user', 'track.customer'])
            ->when($request->partner_id, function ($q) use ($request) {
                $q->whereHas('track', function ($sub) use ($request) {
                    $sub->where('partner_id', $request->partner_id);
                });
            });

        if ($request->has('export')) {
            $date = $packages->first()->container->created_at;
            $b = $date ? (clone $date->startOfDay()) : now()->startOfDay();
            $e = $date ? (clone $date->endOfDay()) : now()->endOfDay();
            $_containers = YenipoctOrder::query()->with(['packages.package.user', 'packages.track.customer'])->whereBetween('created_at', [$b, $e])->get();
//            $packages = $packages->orderBy('status')->get();
            $excel = app()->make(Excel::class);
            return $excel->download(new YeniPoctExport($_containers), 'yenipoct_container_' . $id . '.xlsx');
        }

        $packages = $packages->orderByRaw('CAST(status AS UNSIGNED) ASC')->paginate(100);
        $partners = Partner::all();
        return view('admin.yenipoct.container', compact('partners','container', 'packages', 'packagesCount', 'packagesProblemCount', 'sentPackagesCount', 'notSentPackagesCount'));
    }

    public function store(Request $request, $id)
    {
        $container = YenipoctOrder::query()->where('id', $id)->first();
        if (!$container) {
            return back()->withErrors('Konteyner movcud deyil!');
        }

        if ($request->hasFile('barcodes')) {
            return $this->import($request);
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

        if (!$package && !$track) {
            return back()->withErrors('Bağlama mövcud deyil!');
        }


        if (
            ($package && $package->paid == 0) ||
            ($track && $track->paid == 0 && $track->partner_id == 9)
        ) {
            return back()->withErrors('Bağlama ödənilməyib!');
        }


        if (isset($package) && $package->status !== 8) {
            return back()->withErrors('Bağlama In Kobia statusunda deyil!');
        }

        if (isset($track) && $track->status !== 20) {
            return back()->withErrors('Bağlama In Kobia statusunda deyil!');
        }

        $preventDuplicate = YenipoctPackage::query()
            ->where([
                'type' => $package ? 'package' : 'track',
                'package_id' => $package ? $package->id : $track->id,
                'yenipoct_order_id' => $container->id,
            ])
            ->where('status', '!=', YenipoctPackage::STATUSES['HAS_PROBLEM'])
            ->first();

        if ($preventDuplicate) {
            return back()->withErrors($barcode . ' artıq əlavə edilib!');
        }

        YenipoctPackage::query()->updateOrCreate([
            'package_id' => $package ? $package->id : $track->id,
            'barcode' => $package ? $package->custom_id : $track->tracking_code,
            'type' => $package ? 'package' : 'track',
        ], [
            'yenipoct_order_id' => $id,
            'user_id' => $package ? $package->user_id : $track->customer_id,
            'package_id' => $package ? $package->id : $track->id,
            'status' => YenipoctPackage::STATUSES['NOT_SENT'],
            'added_by' => Auth::user()->id,
            'barcode' => $package ? $package->custom_id : $track->tracking_code,
            'payment_status' => $package ? $package->paid : $track->paid,
        ]);

        YenipoctOrder::query()
            ->where([
                'id' => $container->id
            ])
            ->update([
                'weight' => floatval($container->weight) + floatval($package ? $package->weight : $track->weight),
            ]);
        $compact['success'] = $barcode . ' uğurla əlavə edildi!';

        return back()->with($compact);
    }

    public function import(Request $request)
    {
        $request->validate([
//            'barcodes' => 'required|file|mimes:xlsx,xls,csv|max:20480', // Max size 20MB
        ]);

        $file = $request->file('barcodes');

        \Maatwebsite\Excel\Facades\Excel::import(new BarcodesImport, $file);

        return back()->with("ELave edildi!");
    }

    public function sendPackages(Request $request, $id)
    {
//        dd($request->all());

        $container = YenipoctOrder::query()->where('id', $id)->first();
        if (!$container) {
            return back()->withErrors('Konteyner tapılmadı!');
        }

//        if ($container->status !== AzeriExpressOrder::STATUSES['WAITING']) {
//            return back()->withErrors('Artıq Göndərilmə siyahısına əlavə edilib!');
//        }

        $tracks = YenipoctPackage::with(['container.yeniPoctOffice','track.customer'])
            ->whereIn('status', [
                YenipoctPackage::STATUSES['NOT_SENT'],
                YenipoctPackage::STATUSES['HAS_PROBLEM'],
            ])
            ->where('yenipoct_order_id', $container->id)
            ->when($request->filled('temu'), function ($query) {
                $query->whereHas('track', function ($subQuery) {
                    $subQuery->where('partner_id', 8);
                });
            }, function ($query) {
                $query->whereHas('track', function ($subQuery) {
                    $subQuery->where('partner_id', '!=', 8);
                });
            })
            ->where('type', 'track')
            ->get();

        $packages = collect();
        if(!$request->filled('temu')){
            $packages = YenipoctPackage::with(['container.yeniPoctOffice', 'package.user', 'track.customer'])
                ->whereIn('status', [
                    YenipoctPackage::STATUSES['NOT_SENT'],
                    YenipoctPackage::STATUSES['HAS_PROBLEM'],
                ])
                ->where('yenipoct_order_id', $container->id)
                ->where('type', 'package')
                ->get();
        }

        $packages = $tracks->merge($packages);
        $assignAdmin = Auth::user()->name ?? null;

        foreach ($packages as $package) {
//            $package->company_sent = 1;
//            $package->track->bot_comment = "Bağlama YeniPoct-ə göndərildi".' - '.$assignAdmin;
//            $package->save();

            $package->company_sent = 1;
            $package->save();

            $package->track->bot_comment = "Bağlama YeniPoct-ə göndərildi - ".$assignAdmin;
            $package->track->save();

//            dispatch(new SendPackageToYeniPoctJob($package))->onQueue('default');
        }

        $container->update([
            'status' => YenipoctOrder::STATUSES['SENT'],
            'user_sent_id' => Auth::user()->id,
            'sent_at' => Carbon::now(),
        ]);



        return back()->withSuccess('Göndərilməyib statusunda olan bağlamalar YeniPoct\'a göndərilir!');
    }

    public function print($id, Request $request)
    {
        $order = YenipoctOrder::query()->withCount(['sentPackages'])->findOrFail($id);

        $dns1d = new DNS1D();
        $barcode = $dns1d->getBarcodeSvg($order->barcode, "C128", 2.5, 80);
        $view = view('panel.exports.189_print', compact('order', 'barcode'));
        return $view->render();
    }

    public function destroyGroup($id)
    {
        $find = YenipoctOrder::find($id);
        if (!$find) {
            return back()->withErrors('tapılmadı');
        }

        if ($find->status == YenipoctOrder::STATUSES['SENT']) {
            return back()->withErrors('SMS göndərilib statusundadır. Silmək olmaz!');
        }

        YenipoctPackage::query()
            ->where('yenipoct_order_id', $find->id)
            ->delete();
        $find->delete();

        return back()->withSuccess('Silindi');
    }

    public function deletePackage($id)
    {
        $find = YenipoctPackage::find($id);
        if (!$find) {
            return back()->withErrors('Bağlama tapılmadı');
        }

//        if (in_array($find->status, [
//            YenipoctPackage::STATUSES['SENT'],
//            YenipoctPackage::STATUSES['IN_PROCESS'],
//            YenipoctPackage::STATUSES['WAREHOUSE'],
//            YenipoctPackage::STATUSES['ARRIVEDTOPOINT'],
//            YenipoctPackage::STATUSES['DELIVERED']
//        ])) {
//            return back()->withErrors('Bağlama artıq göndərilib statusundadır. Silmək olmaz!');
//        }

        $find->delete();
        return back()->withSuccess('Silindi');
    }

    public function updateContainer(Request $request)
    {
        $order = YenipoctOrder::findOrFail($request->id);

        $data = [
            'name' => $request->name,
            'status' => $request->filled('status') ? $request->status : $order->status,
        ];

        $order->update($data);

        return back()->withSuccess('Konteyner yeniləndi');
    }
}
