<?php

namespace App\Http\Controllers\Admin\AzeriExpress;

use App\Exports\Admin\AzeriexpressExport;
use App\Exports\Admin\Reports\ReportsExport;
use App\Http\Controllers\Controller;
use App\Imports\BarcodesImport;
use App\Models\Admin;
use App\Models\AzeriExpress\AzeriExpressOffice;
use App\Models\AzeriExpress\AzeriExpressOrder;
use App\Models\AzeriExpress\AzeriExpressPackage;
use App\Models\Package;
use App\Models\Partner;
use App\Models\Track;
use App\Services\AzeriExpress\AzeriExpressService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Log;
use Maatwebsite\Excel\Excel;
use Milon\Barcode\DNS1D;

class AzeriExpressCourierContainerController extends Controller
{
    public function __construct()
    {

    }

    public function containers(Request $request)
    {
        $offices = AzeriExpressOffice::all();
        $statuses = AzeriExpressOrder::STATUSES;

        $rowsQuery = AzeriExpressOrder::query()
            ->where('for_courier', true)
            ->with(['sender', 'creator'])
            ->withCount(['packages'])
            ->whereNotNull('user_id');

        $rowsQuery = $rowsQuery->when($request->filled('status'), function ($query) use ($request) {
            if ($request->get('status') == AzeriExpressOrder::STATUSES['WAITING']) {
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

                if ($request->filled('status') && $request->get('status') == AzeriExpressOrder::STATUSES['SENT']) {
                    return $query->where('sent_at', '>=', $dateFrom)
                        ->where('sent_at', '<=', $dateTo);
                }
                return $query->where('updated_at', '>=', $dateFrom)
                    ->where('updated_at', '<=', $dateTo);
            }
        );

        $totalPackagesCount = AzeriExpressPackage::query()->whereIn('azeri_express_order_id', $rowsQuery->pluck('id'))->count();

        $rows = $rowsQuery
            ->withCount([
                // Count NOT_SENT packages
                'packages as not_sent_count' => function ($query) {
                    $query->whereIn('status', [
                        AzeriExpressPackage::STATUSES['NOT_SENT'],
                        AzeriExpressPackage::STATUSES['HAS_PROBLEM'],
                    ]);
                },
                // Count SEND packages
                'packages as sent_count' => function ($query) {
                    $query->where('status', AzeriExpressPackage::STATUSES['SENT']);
                },
                // Count of not accepted
                'packages as not_accepted_count' => function ($query) {
                    $query->whereIn('status', [
                        AzeriExpressPackage::STATUSES['NOT_SENT'],
                        AzeriExpressPackage::STATUSES['HAS_PROBLEM'],
                        AzeriExpressPackage::STATUSES['SENT'],
                        AzeriExpressPackage::STATUSES['WAITING'],
                    ]);
                },
            ])
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.azeriexpress.courier-containers', compact('rows', 'offices', 'statuses', 'totalPackagesCount'));
    }


    public function editContainer(Request $request, $id)
    {
        $container = AzeriExpressOrder::find($id);
        if (!$container) {
            return back()->withErrors('Azeriexpress container not found');
        }

        $partners = Partner::orderBy('name')->get();
        $admins = Admin::all();
        $packagesQuery = AzeriExpressPackage::query()
            ->where('azeri_express_order_id', $id)
            ->with(['container', 'package.user', 'track.customer']);

        if ($request->filled('status')) {
            $packagesQuery->where('status', $request->status);
        }

        if ($request->filled('admin_id')) {
            $packagesQuery->where('added_by', $request->admin_id);
        }

        if($request->filled('start_date') && $request->filled('end_date')) {
            $dateFrom = Carbon::parse($request->get('start_date'))->startOfDay();
            $dateTo = Carbon::parse($request->get('end_date'))->endOfDay();
            $packagesQuery->whereBetween('created_at', [$dateFrom, $dateTo]);
        }

        if ($request->filled('partner_id')) {
            $packagesQuery->whereHas('track.partner', function ($q) use ($request) {
                $q->where('id', $request->partner_id);
            });
        }

        if($request->filled('package_number')) {
            $packagesQuery->whereHas('track', function ($q) use ($request) {
                $q->where('tracking_code', $request->package_number);
            });
        }

        $packagesCount = AzeriExpressPackage::where('azeri_express_order_id', $id)->count();
        $sentPackagesCount = AzeriExpressPackage::where('azeri_express_order_id', $id)
            ->whereNotIn('status', [
                AzeriExpressPackage::STATUSES['HAS_PROBLEM'],
                AzeriExpressPackage::STATUSES['NOT_SENT']
            ])->count();
        $notSentPackagesCount = AzeriExpressPackage::where('azeri_express_order_id', $id)
            ->where('status', AzeriExpressPackage::STATUSES['NOT_SENT'])->count();
        $packagesProblemCount = AzeriExpressPackage::where([
            'azeri_express_order_id' => $id,
            'status' => AzeriExpressPackage::STATUSES['HAS_PROBLEM']
        ])->count();

        $packages = $packagesQuery->orderByRaw('CAST(status AS UNSIGNED) ASC')->paginate(100)->
        appends(request()->query());

        return view('admin.azeriexpress.courier-container', compact('id','admins',
            'container', 'packages', 'packagesCount', 'packagesProblemCount',
            'sentPackagesCount', 'notSentPackagesCount', 'partners'
        ));
    }

}
