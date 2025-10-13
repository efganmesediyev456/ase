<?php

namespace App\Http\Controllers\Admin\Kargomat;

use App\Exports\Admin\KargomatExport;
use App\Exports\Admin\Reports\ReportsExport;
use App\Http\Controllers\Controller;
use App\Imports\BarcodesImport;
//use App\Jobs\SendPackageToYeniPoctJob;
use App\Models\Kargomat\KargomatOffice;
use App\Models\Kargomat\KargomatOrder;
use App\Models\Kargomat\KargomatPackage;
use App\Models\Package;
use App\Models\Partner;
use App\Models\Track;
//use App\Services\Kargomat\KargomatService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Maatwebsite\Excel\Excel;
use Milon\Barcode\DNS1D;

class KargomatController extends Controller
{
    public function __construct()
    {

    }

    public function index(Request $request)
    {
        $packagesQuery = KargomatPackage::query()
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

        return view('admin.kargomat.index', [
            'packages' => $packagesQuery->paginate(25),
            'total_packages' => $packagesQuery->count(),
            'statuses' => KargomatPackage::STATUSES
        ]);
    }

    public function notSendPackages(Request $request)
    {
        $packagesQuery = KargomatPackage::query()
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

        return view('admin.kargomat.not-send-packages', [
            'packages' => $packagesQuery->paginate(25),
            'total_packages' => $packagesQuery->count(),
            'statuses' => KargomatPackage::STATUSES
        ]);
    }

    public function containers(Request $request)
    {
        $offices = KargomatOffice::all();
        $statuses = KargomatOrder::STATUSES;

        $rowsQuery = KargomatOrder::query()
            ->with(['sender', 'creator'])
            ->withCount(['packages'])
            ->whereNotNull('user_id');

        $rowsQuery = $rowsQuery->when($request->filled('status'), function ($query) use ($request) {
            if($request->get('status') == KargomatOrder::STATUSES['WAITING']){
                return $query->whereIn('status', [KargomatOrder::STATUSES['WAITING'], KargomatOrder::STATUSES['SENDING']]);
            }
            return $query->where('status', $request->get('status'));
        }, function ($query) {
            return $query->whereIn('status', [KargomatOrder::STATUSES['WAITING'], KargomatOrder::STATUSES['SENDING']]);
        });

        $rowsQuery = $rowsQuery->when(
            $request->filled('date-from') && $request->filled('date-to'),
            function ($query) use ($request) {
                $dateFrom = Carbon::parse($request->get('date-from'))->startOfDay();
                $dateTo = Carbon::parse($request->get('date-to'))->endOfDay();

                if($request->filled('status') && $request->get('status') == KargomatOrder::STATUSES['SENT']){
                    return $query->where('sent_at', '>=', $dateFrom)
                        ->where('sent_at', '<=', $dateTo);
                }
                return $query->where('updated_at', '>=', $dateFrom)
                    ->where('updated_at', '<=', $dateTo);
            }
        );

        $totalPackagesCount  = KargomatPackage::query()->whereIn('kargomat_order_id', $rowsQuery->pluck('id'))->count();

        $rows = $rowsQuery
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.kargomat.containers', compact('rows', 'offices', 'statuses', 'totalPackagesCount'));
    }

    public function storeContainer(Request $request): RedirectResponse
    {
        $authId = Auth::user()->id;
        KargomatOrder::query()->create([
            'name' => $request->input('name'),
            'user_id' => $authId,
            'kargomat_id' => $request->input('kargomatoffice_id'),
            'status' => KargomatOrder::STATUSES['WAITING'],
            'barcode' => (new KargomatService())->generateNewBarcode(),
            'created_at' => now()
        ]);

        return back()->with('success', $request->input('name') . ' created');
    }

    public function editContainer(Request $request, $id)
    {
        $container = KargomatOrder::where('id', $id)->first();
        if (!$container) {
            return back()->withErrors('Kargomat container not found');
        }

        $packagesCount = KargomatPackage::query()->where('kargomat_order_id', $id)->count();
        $sentPackagesCount = KargomatPackage::query()->where('kargomat_order_id', $id)->whereNotIn('status', [KargomatPackage::STATUSES['HAS_PROBLEM'], KargomatPackage::STATUSES['NOT_SENT']])->count();
        $notSentPackagesCount = KargomatPackage::query()->where('kargomat_order_id', $id)->where('status', KargomatPackage::STATUSES['NOT_SENT'])->count();
        $packagesProblemCount = KargomatPackage::query()->where([
            'kargomat_order_id' => $id,
            'status' => KargomatPackage::STATUSES['HAS_PROBLEM']
        ])->count();

        $packages = KargomatPackage::query()->where('kargomat_order_id', $id)
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
            $_containers = KargomatOrder::query()->with(['packages.package.user', 'packages.track.customer'])->whereBetween('created_at', [$b, $e])->get();
//            $packages = $packages->orderBy('status')->get();
            $excel = app()->make(Excel::class);
            return $excel->download(new KargomatExport($_containers), 'kargomat_container_' . $id . '.xlsx');
        }

        $packages = $packages->orderByRaw('CAST(status AS UNSIGNED) ASC')->paginate(100);
        $partners = Partner::all();
        return view('admin.kargomat.container', compact('partners','container', 'packages', 'packagesCount', 'packagesProblemCount', 'sentPackagesCount', 'notSentPackagesCount'));
    }

    public function store(Request $request, $id)
    {
        $container = KargomatOrder::query()->where('id', $id)->first();
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

        if (isset($package) && $package->paid == 0) {
            return back()->withErrors('Bağlama ödənilməyib!');
        }

        $preventDuplicate = KargomatPackage::query()
            ->where([
                'type' => $package ? 'package' : 'track',
                'package_id' => $package ? $package->id : $track->id,
                'kargomat_order_id' => $container->id,
            ])
            ->where('status', '!=', KargomatPackage::STATUSES['HAS_PROBLEM'])
            ->first();

        if ($preventDuplicate) {
            return back()->withErrors($barcode . ' artıq əlavə edilib!');
        }

        KargomatPackage::query()->updateOrCreate([
            'package_id' => $package ? $package->id : $track->id,
            'barcode' => $package ? $package->custom_id : $track->tracking_code,
            'type' => $package ? 'package' : 'track',
        ], [
            'kargomat_order_id' => $id,
            'user_id' => $package ? $package->user_id : $track->customer_id,
            'package_id' => $package ? $package->id : $track->id,
            'status' => KargomatPackage::STATUSES['NOT_SENT'],
            'added_by' => Auth::user()->id,
            'barcode' => $package ? $package->custom_id : $track->tracking_code,
            'payment_status' => $package ? $package->paid : $track->paid,
        ]);

        KargomatOrder::query()
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

        $container = KargomatOrder::query()->where('id', $id)->first();
        if (!$container) {
            return back()->withErrors('Konteyner tapılmadı!');
        }

//        if ($container->status !== AzeriExpressOrder::STATUSES['WAITING']) {
//            return back()->withErrors('Artıq Göndərilmə siyahısına əlavə edilib!');
//        }

        $tracks = KargomatPackage::with(['container.kargomatOffice','track.customer'])
            ->whereIn('status', [
                KargomatPackage::STATUSES['NOT_SENT'],
                KargomatPackage::STATUSES['HAS_PROBLEM'],
            ])
            ->where('kargomat_order_id', $container->id)
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
            $packages = KargomatPackage::with(['container.kargomatOffice', 'package.user', 'track.customer'])
                ->whereIn('status', [
                    KargomatPackage::STATUSES['NOT_SENT'],
                    KargomatPackage::STATUSES['HAS_PROBLEM'],
                ])
                ->where('kargomat_order_id', $container->id)
                ->where('type', 'package')
                ->get();
        }

        $packages = $tracks->merge($packages);
        $assignAdmin = Auth::user()->name ?? null;
        foreach ($packages as $package) {
            $package->company_sent = 1;
            $package->save();
            if ($package->type == 'package') {
                $_package = Package::find($package->package_id);
                $_package->bot_comment = "Bağlama Kargomat-ə göndərildi".' - '.$assignAdmin;
                $_package->save();

            } else if ($package->type == 'track') {
                $_track = Track::find($package->package_id);
                $_track->bot_comment = "Bağlama Kargomat-ə göndərildi".' - '.$assignAdmin;
                $_track->save();
            }
//            dispatch(new SendPackageToYeniPoctJob($package))->onQueue('default');
        }

        $container->update([
            'status' => KargomatOrder::STATUSES['SENDING'],
            'user_sent_id' => Auth::user()->id,
            'sent_at' => Carbon::now(),
        ]);

        return back()->withSuccess('Göndərilməyib statusunda olan bağlamalar Kargomat\'a göndərilir!');
    }

    public function print($id, Request $request)
    {
        $order = KargomatOrder::query()->withCount(['sentPackages'])->findOrFail($id);

        $dns1d = new DNS1D();
        $barcode = $dns1d->getBarcodeSvg($order->barcode, "C128", 2.5, 80);
        $view = view('panel.exports.189_print', compact('order', 'barcode'));
        return $view->render();
    }

    public function destroyGroup($id)
    {
        $find = KargomatOrder::find($id);
        if (!$find) {
            return back()->withErrors('tapılmadı');
        }

        if ($find->status == KargomatOrder::STATUSES['SENT']) {
            return back()->withErrors('SMS göndərilib statusundadır. Silmək olmaz!');
        }

        KargomatPackage::query()
            ->where('kargomat_order_id', $find->id)
            ->delete();
        $find->delete();

        return back()->withSuccess('Silindi');
    }

    public function deletePackage($id)
    {
        $find = KargomatPackage::find($id);
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
        $order = KargomatOrder::findOrFail($request->id);

        $data = [
            'name' => $request->name,
            'status' => $request->filled('status') ? $request->status : $order->status,
        ];

        $order->update($data);

        return back()->withSuccess('Konteyner yeniləndi');
    }
}
