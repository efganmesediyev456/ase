<?php

namespace App\Http\Controllers\Admin\CourierSaas;

use App\Exports\Admin\CourierSaasExport;
use App\Http\Controllers\Controller;
use App\Jobs\SendPackageToKuryeraJob;
use App\Models\CourierSaas\CourierSaasOrder;
use App\Models\CourierSaas\CourierSaasPackage;
use App\Models\Filial;
use App\Models\Package;
use App\Models\Track;
use App\Services\Kuryera\KuryeraService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Maatwebsite\Excel\Excel;
use Milon\Barcode\DNS1D;

class CourierSaasController extends Controller
{
    public function __construct()
    {

    }

    public function index(Request $request)
    {
        $packagesQuery = CourierSaasPackage::query()
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

        $packagesQuery->when($request->filled('start_date'), function ($query) use ($request) {
            $date = Carbon::createFromFormat('Y-m-d', $request->get('start_date'));
            return $query->where('updated_at', '>=', $date->startOfDay());
        });

        $packagesQuery->when($request->filled('end_date'), function ($query) use ($request) {
            $date = Carbon::createFromFormat('Y-m-d', $request->get('end_date'));
            return $query->where('updated_at', '<=', $date->endOfDay());
        });

        return view('admin.courier-saas.index', [
            'packages' => $packagesQuery->paginate(25),
            'total_packages' => $packagesQuery->count(),
            'statuses' => CourierSaasPackage::STATUSES
        ]);
    }

    public function containers(Request $request)
    {
        $offices = Filial::all();
        $statuses = CourierSaasOrder::STATUSES;

        $rowsQuery = CourierSaasOrder::query()
            ->with(['sender', 'creator'])
            ->whereNotNull('user_id');

        $rowsQuery = $rowsQuery->when($request->filled('status'), function ($query) use ($request) {
            return $query->where('status', $request->get('status'));
        }, function ($query) {
            return $query->where('status', CourierSaasOrder::STATUSES['WAITING']);
        });

        $rows = $rowsQuery
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.courier-saas.containers', compact('rows', 'offices', 'statuses'));
    }

    public function storeContainer(Request $request): RedirectResponse
    {
        $authId = Auth::user()->id;
        CourierSaasOrder::query()->create([
            'name' => $request->input('name'),
            'user_id' => $authId,
            'office' => $request->input('courier_saas_office_id'),
            'status' => CourierSaasOrder::STATUSES['WAITING'],
            'barcode' => (new KuryeraService())->generateNewBarcode(),
            'created_at' => now()
        ]);

        return back()->with('success', $request->input('name') . ' created');
    }

    public function editContainer(Request $request, $id)
    {
        $container = CourierSaasOrder::where('id', $id)->first();
        if (!$container) {
            return back()->withErrors('CS container not found');
        }

        $packagesCount = CourierSaasPackage::query()->where('courier_saas_order_id', $id)->count();
        $sentPackagesCount = CourierSaasPackage::query()->where('courier_saas_order_id', $id)->whereNotIn('status', [CourierSaasPackage::STATUSES['HAS_PROBLEM'], CourierSaasPackage::STATUSES['NOT_SENT']])->count();
        $notSentPackagesCount = CourierSaasPackage::query()->where('courier_saas_order_id', $id)->where('status', CourierSaasPackage::STATUSES['NOT_SENT'])->count();
        $packagesProblemCount = CourierSaasPackage::query()->where([
            'courier_saas_order_id' => $id,
            'status' => CourierSaasPackage::STATUSES['HAS_PROBLEM']
        ])->count();

        $packages = CourierSaasPackage::query()->where('courier_saas_order_id', $id)
            ->with(['container', 'package.user', 'track.customer']);

        if ($request->has('export')) {
            $date = $packages->first()->container->created_at;
            $b = $date ? (clone $date->startOfDay()) : now()->startOfDay();
            $e = $date ? (clone $date->endOfDay()) : now()->endOfDay();
            $_containers = CourierSaasOrder::query()->with(['packages.package.user', 'packages.track.customer'])->whereBetween('created_at', [$b, $e])->get();
            $excel = app()->make(Excel::class);
            return $excel->download(new CourierSaasExport($_containers), 'courier_saas_container_' . $id . '.xlsx');
        }

        $packages = $packages->orderBy('status')->paginate(100);

        return view('admin.courier-saas.container', compact('container', 'packages', 'packagesCount', 'packagesProblemCount', 'sentPackagesCount', 'notSentPackagesCount'));
    }

    public function store(Request $request, $id)
    {
        $container = CourierSaasOrder::query()->where('id', $id)->first();
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

        if (!$package && !$track) {
            return back()->withErrors('Bağlama mövcud deyil!');
        }

        $preventDuplicate = CourierSaasPackage::query()
            ->where([
                'type' => $package ? 'package' : 'track',
                'package_id' => $package ? $package->id : $track->id,
                'courier_saas_order_id' => $container->id,
            ])
            ->where('status', '!=', CourierSaasPackage::STATUSES['HAS_PROBLEM'])
            ->first();

        if ($preventDuplicate) {
            return back()->withErrors($barcode . ' artıq əlavə edilib!');
        }

        CourierSaasPackage::query()->updateOrCreate([
            'package_id' => $package ? $package->id : $track->id,
            'barcode' => $package ? $package->custom_id : $track->tracking_code,
            'type' => $package ? 'package' : 'track',
        ], [
            'courier_saas_order_id' => $id,
            'user_id' => $package ? $package->user_id : $track->customer_id,
            'status' => CourierSaasPackage::STATUSES['NOT_SENT'],
            'added_by' => Auth::user()->id,
            'payment_status' => $package ? $package->paid : $track->paid,
        ]);

        CourierSaasOrder::query()
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
        $container = CourierSaasOrder::query()->where('id', $id)->first();
        if (!$container) {
            return back()->withErrors('Konteyner tapılmadı!');
        }

        if ($container->status !== CourierSaasOrder::STATUSES['WAITING']) {
//            return back()->withErrors('Artıq Göndərilmə siyahısına əlavə edilib!');
        }

        $query = CourierSaasPackage::query()
            ->with(['container', 'package.user', 'track.customer'])
            ->whereIn('status', [CourierSaasPackage::STATUSES['NOT_SENT'], CourierSaasPackage::STATUSES['WAITING'], CourierSaasPackage::STATUSES['HAS_PROBLEM']])
            ->where('courier_saas_order_id', $container->id);

        $packages = $query->get();

        Log::channel('cs')->debug("Packages will send: ", $packages->pluck('barcode'));
        foreach ($packages as $package) {
            dispatch(new SendPackageToKuryeraJob($package))->onQueue('default');
        }

        $query->update([
            'status' => CourierSaasPackage::STATUSES['WAITING'],
        ]);

        $container->update([
            'status' => CourierSaasOrder::STATUSES['SENDING'],
            'user_sent_id' => Auth::user()->id,
            'sent_at' => Carbon::now(),
        ]);

        return back()->withSuccess('Göndərilməyib statusunda olan bağlamalar Kuryera\'a göndərilir!');
    }

    public function print($id, Request $request)
    {
        $order = CourierSaasOrder::query()->withCount(['sentPackages'])->findOrFail($id);

        $dns1d = new DNS1D();
        $barcode = $dns1d->getBarcodeSvg($order->barcode, "C128", 2.5, 80);
        $view = view('panel.exports.189_print', compact('order', 'barcode'));
        return $view->render();
    }

    public function deleteContainer($id)
    {
        $find = CourierSaasOrder::find($id);
        if (!$find) {
            return back()->withErrors('tapılmadı');
        }

        if ($find->status == CourierSaasOrder::STATUSES['SENT']) {
            return back()->withErrors('SMS göndərilib statusundadır. Silmək olmaz!');
        }

        CourierSaasPackage::query()
            ->where('courier_saas_order_id', $find->id)
            ->delete();
        $find->delete();

        return back()->withSuccess('Silindi');
    }

    public function deletePackage($id)
    {
        $find = CourierSaasPackage::find($id);
        if (!$find) {
            return back()->withErrors('Bağlama tapılmadı');
        }

        if (in_array($find->status, [
            CourierSaasPackage::STATUSES['SENT'],
            CourierSaasPackage::STATUSES['IN_PROCESS'],
            CourierSaasPackage::STATUSES['WAREHOUSE'],
            CourierSaasPackage::STATUSES['ARRIVEDTOPOINT'],
            CourierSaasPackage::STATUSES['DELIVERED']
        ])) {
            return back()->withErrors('Bağlama artıq göndərilib statusundadır. Silmək olmaz!');
        }

        $find->delete();
        return back()->withSuccess('Silindi');
    }

    public function updateContainer(Request $request)
    {
        $order = CourierSaasOrder::findOrFail($request->id);

        $data = [
            'name' => $request->name,
            'status' => $request->filled('status') ? $request->status : $order->status,
        ];

        $order->update($data);

        return back()->withSuccess('Konteyner yeniləndi');
    }
}
