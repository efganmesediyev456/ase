<?php

namespace App\Http\Controllers\Admin\Surat;

use App\Exports\Admin\Reports\ReportsExport;
use App\Exports\Admin\SuratExport;
use App\Http\Controllers\Controller;
use App\Jobs\SendPackageToSuratJob;
use App\Models\Package;
use App\Models\Partner;
use App\Models\Surat\SuratOffice;
use App\Models\Surat\SuratOrder;
use App\Models\Surat\SuratPackage;
use App\Models\Track;
use App\Services\Surat\SuratService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Maatwebsite\Excel\Excel;
use Milon\Barcode\DNS1D;

class SuratController extends Controller
{
    public function __construct()
    {

    }

    public function index(Request $request)
    {
        $packagesQuery = SuratPackage::query()
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
            $date = now()->format('Y-m-d-m-Y-H-i-s');

            return $excel->download(new ReportsExport($packagesQuery->get(), 'surat'), 'surat-report-' . $date . '.xlsx');
        }

        return view('admin.surat.index', [
            'packages' => $packagesQuery->paginate(25),
            'total_packages' => $packagesQuery->count(),
            'statuses' => SuratPackage::STATUSES
        ]);
    }

    public function containers(Request $request)
    {
        $offices = SuratOffice::all();
        $statuses = SuratOrder::STATUSES;

        $rowsQuery = SuratOrder::query()
            ->with(['sender', 'creator'])
            ->withCount(['packages'])
            ->whereNotNull('user_id');

        $rowsQuery = $rowsQuery->when($request->filled('status'), function ($query) use ($request) {
            if ($request->get('status') == SuratOrder::STATUSES['WAITING']) {
                return $query->whereIn('status', [SuratOrder::STATUSES['WAITING'], SuratOrder::STATUSES['SENDING']]);
            }
            return $query->where('status', $request->get('status'));
        }, function ($query) {
            return $query->whereIn('status', [SuratOrder::STATUSES['WAITING'], SuratOrder::STATUSES['SENDING']]);
        });

        $rowsQuery = $rowsQuery->when(
            $request->filled('date-from') && $request->filled('date-to'),
            function ($query) use ($request) {
                $dateFrom = Carbon::parse($request->get('date-from'))->startOfDay();
                $dateTo = Carbon::parse($request->get('date-to'))->endOfDay();

                if($request->filled('status') && $request->get('status') == SuratOrder::STATUSES['SENT']){
                    return $query->where('sent_at', '>=', $dateFrom)
                        ->where('sent_at', '<=', $dateTo);
                }
                return $query->where('updated_at', '>=', $dateFrom)
                    ->where('updated_at', '<=', $dateTo);
            }
        );

        $totalPackagesCount  = SuratPackage::query()->whereIn('surat_order_id', $rowsQuery->pluck('id'))->count();

        $rows = $rowsQuery
            ->withCount([
                // Count NOT_SENT packages
                'packages as not_sent_count' => function ($query) {
                    $query->whereIn('status', [
                        SuratPackage::STATUSES['NOT_SENT'],
                        SuratPackage::STATUSES['WAITING'],
                        SuratPackage::STATUSES['HAS_PROBLEM']
                    ]);
                },
                // Count SEND packages
                'packages as sent_count' => function ($query) {
                    $query->where('status', SuratPackage::STATUSES['SENT']);
                },
                // Count of not accepted
                'packages as not_accepted_count' => function ($query) {
                    $query->whereIn('status', [
                        SuratPackage::STATUSES['NOT_SENT'],
                        SuratPackage::STATUSES['HAS_PROBLEM'],
                        SuratPackage::STATUSES['SENT'],
                        SuratPackage::STATUSES['WAITING'],
                    ]);
                },
            ])
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.surat.containers', compact('rows', 'offices', 'statuses', 'totalPackagesCount'));
    }

    public function storeContainer(Request $request): RedirectResponse
    {
        $authId = Auth::user()->id;
        SuratOrder::query()->create([
            'name' => $request->input('name'),
            'user_id' => $authId,
            'surat_office_id' => $request->input('surat_office_id'),
            'status' => SuratOrder::STATUSES['WAITING'],
            'barcode' => (new SuratService())->generateNewBarcode(),
            'created_at' => now()
        ]);

        return back()->with('success', $request->input('name') . ' created');
    }

    public function editContainer(Request $request, $id)
    {
        $container = SuratOrder::where('id', $id)->first();
        if (!$container) {
            return back()->withErrors('Surat container not found');
        }

        $packagesCount = SuratPackage::query()->where('surat_order_id', $id)->count();
        $sentPackagesCount = SuratPackage::query()->where('surat_order_id', $id)->whereNotIn('status', [SuratPackage::STATUSES['HAS_PROBLEM'], SuratPackage::STATUSES['NOT_SENT']])->count();
        $notSentPackagesCount = SuratPackage::query()->where('surat_order_id', $id)->where('status', SuratPackage::STATUSES['NOT_SENT'])->count();
        $packagesProblemCount = SuratPackage::query()->where([
            'surat_order_id' => $id,
            'status' => SuratPackage::STATUSES['HAS_PROBLEM']
        ])->count();

        $packages = SuratPackage::query()->where('surat_order_id', $id)
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
            $_containers = SuratOrder::query()->with(['packages.package.user', 'packages.track.customer'])->whereBetween('created_at', [$b, $e])->get();
            $excel = app()->make(Excel::class);
            return $excel->download(new SuratExport($_containers), 'surat_container_' .$id . '_' . time() . '.xlsx');
        }

        $packages = $packages->orderByRaw('CAST(status AS UNSIGNED) ASC')->paginate(100);
        $partners = Partner::all();
        return view('admin.surat.container', compact('partners','container', 'packages', 'packagesCount', 'packagesProblemCount', 'sentPackagesCount', 'notSentPackagesCount'));
    }

    public function store(Request $request, $id)
    {
        $container = SuratOrder::query()->where('id', $id)->first();
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

        $preventDuplicate = SuratPackage::query()
            ->where([
                'type' => $package ? 'package' : 'track',
                'package_id' => $package ? $package->id : $track->id,
                'surat_order_id' => $container->id,
            ])
            ->where('status', '!=', SuratPackage::STATUSES['HAS_PROBLEM'])
            ->first();

        if ($preventDuplicate) {
            return back()->withErrors($barcode . ' artıq əlavə edilib!');
        }

        SuratPackage::query()->updateOrCreate([
            'package_id' => $package ? $package->id : $track->id,
            'barcode' => $package ? $package->custom_id : $track->tracking_code,
            'type' => $package ? 'package' : 'track',
        ], [
            'surat_order_id' => $id,
            'user_id' => $package ? $package->user_id : $track->customer_id,
            'package_id' => $package ? $package->id : $track->id,
            'status' => SuratPackage::STATUSES['NOT_SENT'],
            'added_by' => Auth::user()->id,
            'barcode' => $package ? $package->custom_id : $track->tracking_code,
            'payment_status' => $package ? $package->paid : $track->paid,
        ]);

        SuratOrder::query()
            ->where([
                'id' => $container->id
            ])
            ->update([
                'weight' => floatval($container->weight) + floatval($package ? $package->weight : $track->weight),
            ]);
        $compact['success'] = $barcode . ' uğurla əlavə edildi!';

        return back()->with($compact);
    }

    public function sendPackages(Request $request, $id)
    {
        $container = SuratOrder::query()->where('id', $id)->first();
        if (!$container) {
            return back()->withErrors('Konteyner tapılmadı!');
        }

        if ($container->status !== SuratOrder::STATUSES['WAITING']) {
//            return back()->withErrors('Artıq Göndərilmə siyahısına əlavə edilib!');
        }

        $tracks = SuratPackage::query()
            ->with(['container.suratOffice', 'track.customer'])
            ->whereIn('status', [SuratPackage::STATUSES['NOT_SENT'], SuratPackage::STATUSES['WAITING'], SuratPackage::STATUSES['HAS_PROBLEM']])
            ->where('surat_order_id', $container->id)
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
        if (!$request->filled('temu')) {
            $packages = SuratPackage::with(['container.suratOffice', 'package.user'])
                ->whereIn('status', [
                    SuratPackage::STATUSES['NOT_SENT'],
                    SuratPackage::STATUSES['WAITING'],
                    SuratPackage::STATUSES['HAS_PROBLEM'],
                ])
                ->where('surat_order_id', $container->id)
                ->where('type', 'package')
                ->get();
        }

        $all = $tracks->merge($packages);
        Log::channel('surat')->debug("Packages will send: ", $all->pluck('barcode'));
        $assignAdmin = Auth::user()->name ?? null;
        foreach ($all as $package) {
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
//            dispatch(new SendPackageToSuratJob($package))->onQueue('default');
        }

//        $tracks->update([
//            'status' => SuratPackage::STATUSES['WAITING'],
//        ]);
//        $packages->update([
//            'status' => SuratPackage::STATUSES['WAITING'],
//        ]);

        $container->update([
            'status' => SuratOrder::STATUSES['SENDING'],
            'user_sent_id' => Auth::user()->id,
            'sent_at' => Carbon::now(),
        ]);

        return back()->withSuccess('Göndərilməyib statusunda olan bağlamalar Surat Kargo\'ya göndərilir!');
    }

    public function print($id, Request $request)
    {
        $order = SuratOrder::query()->withCount(['sentPackages'])->findOrFail($id);

        $dns1d = new DNS1D();
        $barcode = $dns1d->getBarcodeSvg($order->barcode, "C128", 2.5, 80);
        $view = view('panel.exports.189_print', compact('order', 'barcode'));
        return $view->render();
    }

    public function deleteContainer($id)
    {
        $find = SuratOrder::find($id);
        if (!$find) {
            return back()->withErrors('tapılmadı');
        }

        if ($find->status == SuratOrder::STATUSES['SENT']) {
            return back()->withErrors('SMS göndərilib statusundadır. Silmək olmaz!');
        }

        SuratPackage::query()
            ->where('surat_order_id', $find->id)
            ->delete();
        $find->delete();

        return back()->withSuccess('Silindi');
    }

    public function deletePackage($id)
    {
        $find = SuratPackage::find($id);
        if (!$find) {
            return back()->withErrors('Bağlama tapılmadı');
        }

//        if (in_array($find->status, [
//            SuratPackage::STATUSES['SENT'],
//            SuratPackage::STATUSES['IN_PROCESS'],
//            SuratPackage::STATUSES['WAREHOUSE'],
//            SuratPackage::STATUSES['ARRIVEDTOPOINT'],
//            SuratPackage::STATUSES['DELIVERED']
//        ])) {
//            return back()->withErrors('Bağlama artıq göndərilib statusundadır. Silmək olmaz!');
//        }

        $find->delete();
        return back()->withSuccess('Silindi');
    }

    public function updateContainer(Request $request)
    {
        $order = SuratOrder::findOrFail($request->id);

        $data = [
            'name' => $request->name,
            'status' => $request->filled('status') ? $request->status : $order->status,
        ];

        $order->update($data);

        return back()->withSuccess('Konteyner yeniləndi');
    }
}
