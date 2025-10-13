<?php

namespace App\Http\Controllers\Admin\Hub;

use App\Exports\Admin\AzeriexpressExport;
use App\Http\Controllers\Controller;
use App\Imports\BarcodesImport;
use App\Jobs\SendPackageToAzeriExpressJob;
use App\Models\AzeriExpress\AzeriExpressOffice;
use App\Models\AzeriExpress\AzeriExpressOrder;
use App\Models\AzeriExpress\AzeriExpressPackage;
use App\Models\Hub\Box;
use App\Models\Hub\BoxPackage;
use App\Models\Package;
use App\Models\Track;
use App\Services\AzeriExpress\AzeriExpressService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Maatwebsite\Excel\Excel;
use Milon\Barcode\DNS1D;

class BoxController extends Controller
{
    public function __construct()
    {

    }

    public function index(Request $request)
    {
        $packagesQuery = BoxPackage::query()
            ->with(['box']);

        $packagesQuery->when($request->filled('code'), function ($query) use ($request) {
            return $query->where('tracking', 'like', '%' . $request->get('code') . '%');
        });

        return view('admin.hub.index', [
            'packages' => $packagesQuery->paginate(25),
            'total_packages' => $packagesQuery->count(),
        ]);
    }

    public function boxes(Request $request)
    {
        $rowsQuery = Box::query()
            ->with(['creator'])
            ->withCount(['parcels'])
            ->whereNotNull('user_id');

        $rowsQuery = $rowsQuery->when($request->filled('status'), function ($query) use ($request) {
            if($request->get('status') == AzeriExpressOrder::STATUSES['WAITING']){
                return $query->whereIn('status', [AzeriExpressOrder::STATUSES['WAITING'], AzeriExpressOrder::STATUSES['SENDING']]);
            }
            return $query->where('status', $request->get('status'));
        }, function ($query) {
            return $query->whereIn('status', [AzeriExpressOrder::STATUSES['WAITING'], AzeriExpressOrder::STATUSES['SENDING']]);
        });

        $rowsQuery = $rowsQuery->when(
            $request->filled('date-from') && $request->filled('date-to'),
            function ($query) use ($request) {
                $dateFrom = Carbon::parse($request->get('date-from'))->startOfDay();
                $dateTo = Carbon::parse($request->get('date-to'))->endOfDay();

                if($request->filled('status') && $request->get('status') == AzeriExpressOrder::STATUSES['SENT']){
                    return $query->where('sent_at', '>=', $dateFrom)
                        ->where('sent_at', '<=', $dateTo);
                }
                return $query->where('updated_at', '>=', $dateFrom)
                    ->where('updated_at', '<=', $dateTo);
            }
        );

        $totalPackagesCount  = BoxPackage::query()->whereIn('box_id', $rowsQuery->pluck('id'))->count();

        $rows = $rowsQuery
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.hub.boxes', compact('rows', 'totalPackagesCount'));
    }

    public function store(Request $request): RedirectResponse
    {
        $authId = Auth::user()->id;

        Box::query()->create([
            'name' => $request->input('name'),
            'user_id' => $authId,
            'carrier' => $request->input('carrier'),
            'status' => Box::STATUS_ACTIVE,
            'barcode' => (new AzeriExpressService())->generateNewBarcode(),
            'created_at' => now()
        ]);

        return back()->with('success', $request->input('name') . ' yaradıldı!');
    }//+

    public function show(Request $request, $id)
    {
        $box = Box::where('id', $id)->first();
        if (!$box) {
            return back()->withErrors('Qutu tapılmadı!');
        }

        $packagesCount = BoxPackage::query()->where('box_id', $id)->count();

        $packages = BoxPackage::query()->where('box_id', $id)
            ->with(['box']);

        if ($request->has('export')) {
            $date = $packages->first()->container->created_at;
            $b = $date ? (clone $date->startOfDay()) : now()->startOfDay();
            $e = $date ? (clone $date->endOfDay()) : now()->endOfDay();
            $_containers = AzeriExpressOrder::query()->with(['packages.package.user', 'packages.track.customer'])->whereBetween('created_at', [$b, $e])->get();
//            $packages = $packages->orderBy('status')->get();
            $excel = app()->make(Excel::class);
            return $excel->download(new AzeriexpressExport($_containers), 'azeriexpress_container_' . $id . '.xlsx');
        }

        $packages = $packages->paginate(100);

        return view('admin.hub.box', compact('box', 'packages', 'packagesCount'));
    }//+

    public function print($id, Request $request)
    {
        $order = AzeriExpressOrder::query()->withCount(['sentPackages'])->findOrFail($id);

        $dns1d = new DNS1D();
        $barcode = $dns1d->getBarcodeSvg($order->barcode, "C128", 2.5, 80);
        $view = view('panel.exports.189_print', compact('order', 'barcode'));
        return $view->render();
    }

    public function export()
    {
        
    }

    public function delete($id)
    {
        $box = Box::where('id', $id)->first();
        if (!$box) {
            return back()->withErrors('Qutu tapılmadı!');
        }

        BoxPackage::query()
            ->where('box_id', $box->id)
            ->delete();
        $box->delete();

        return back()->withSuccess('Qutu silindi!');
    }//+

    public function update(Request $request)
    {
        $box = Box::findOrFail($request->id);
        $data = [
            'name' => $request->name ?? $box->name,
            'status' => $request->filled('status') ? $request->status : $box->status,
        ];
        $box->update($data);

        return back()->withSuccess('Qutu yeniləndi!');
    }//+
}
