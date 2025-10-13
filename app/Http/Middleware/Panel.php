<?php

namespace App\Http\Middleware;

use App\Models\CD;
use App\Models\Package;
use Auth;
use Cache;
use Closure;
use Illuminate\Http\Request;
use View;

class Panel
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $autoPrint = false;
        $autoPrintPackage = false;
        $autoPrintParcel = false;
        $autoPrintPackageInvoice = false;
        $autoPrintParcelInvoice = false;
        $showLabel = false;
        $showInvoice = false;
        $fakeInvoice = false;
        $_packages = false;
        $cellUpdate = false;
        $cities = [];
        $cds = [];
	$store_status = 0;
        $_createPackage = 'packages.create';
        if (Auth::guard('admin')->check()) {
            $panel = Auth::guard('admin')->user()->role->display_name;
            $view = 'admin';
            $avatar = Auth::guard('admin')->user()->avatar;
            $name = Auth::guard('admin')->user()->name;
            $url = route('admins.edit', Auth::guard('admin')->user()->id);
            $logged = Auth::guard('admin')->user();
            $cellUpdate = app('laratrust')->can('update-cells');
	    $store_status = auth()->guard('admin')->user()->store_status;


            $data = Cache::remember('mixed_data', 30 * 24 * 60, function () {

                $ready = Package::setEagerLoads([])->where('status', '!=', 3);
                $items = Package::setEagerLoads([])->where('status', '!=', 3);
                $requested = Package::setEagerLoads([])->where('status', 2)->whereNotNull('requested_at');

                /* Filter cities */
                $cities = auth()->guard('admin')->user()->cities->pluck('id')->all();
		$store_status = auth()->guard('admin')->user()->store_status;
                if ($cities) {

                    $requested->whereHas('user', function (
                        $query
                    ) use ($cities) {
                        $query->whereIn('city_id', $cities)->orWhere('city_id', null);
                    });

                    $items->whereHas('user', function (
                        $query
                    ) use ($cities) {
                        $query->whereIn('city_id', $cities)->orWhere('city_id', null);
                    });

                    $ready->whereHas('user', function (
                        $query
                    ) use ($cities) {
                        $query->whereIn('city_id', $cities)->orWhere('city_id', null);
                    });
                }
                $ready = $ready->ready();

                return [
                    'ready' => $ready->count(),
                    'requested' => $requested->count(),
                    'items' => $items->count(),
                ];
            });

            $requested = $data['requested'];
            $ready = $data['ready'];
            $items = $data['items'];


            $done = Cache::remember('all_done', 24 * 60, function () {
                return Package::setEagerLoads([])->where('status', 3)->count();
            });
            $paid = Cache::remember('paid', 24 * 60, function () {
                return Package::setEagerLoads([])->where('paid', 1)->where('status', '<>', 3)->count();
            });
            $unknown = Cache::remember('unknown', 24 * 60, function () {
                return Package::setEagerLoads([])->whereNull('user_id')->whereIn('status', [0, 6])->count();
            });

            $_packages = [
                'paid' => $paid,
                'done' => $done,
                'requested' => $requested,
                'active' => $items . "/" . $ready,
                'unknown' => $unknown,
            ];

            $new_cds = CD::whereNull('courier_id')->where('status', '!=', 6)->count();
            $cds = [
                'new' => $new_cds,
            ];
        } elseif (Auth::guard('worker')->check()) {
            $panel = 'Warehouse';
            $view = 'warehouse';
            $wUser = Auth::guard('worker')->user()->warehouse;
            $avatar = $wUser->country->flag;
            $name = Auth::guard('worker')->user()->name ?: $wUser->company_name;
            $url = route('my.edit', $wUser->id);
            $_createPackage = $wUser->package_processing ? 'w-process' : 'w-packages.create';
            $logged = $wUser;

            $autoPrintParcel = boolval($wUser->auto_print);
            $autoPrintPackage = boolval($wUser->auto_print_pp);
            $autoPrintParcelInvoice = boolval($wUser->auto_print_invoice);
            $autoPrintPackageInvoice = boolval($wUser->auto_print_pp_invoice);
            $autoPrint = $autoPrintParcel || $autoPrintPackage || $autoPrintParcelInvoice || $autoPrintPackageInvoice;
            $showInvoice = boolval($wUser->show_invoice);
            $fakeInvoice = ($wUser->label > 1);
            $showLabel = boolval($wUser->show_label);
        } elseif (Auth::guard('courier')->check()) {
            $panel = 'Cd';
            $avatar = '';
            $view = 'cd';
            $name = Auth::guard('courier')->user()->name;
            $logged = null;
            $url = null;
            $panel = null;
        } else {
            $avatar = '';
            $view = false;
            $name = 'UnKnow';
            $logged = null;
            $url = null;
            $panel = null;
        }

        View::share([
            '_viewDir' => $view,
            '_panelName' => $panel,
            '_name' => $name,
            '_avatar' => $avatar,
            '_profileUrl' => $url,
            '_logged' => $logged,
            '_autoPrint' => $autoPrint,
            '_autoPrintParcel' => $autoPrintParcel,
            '_autoPrintPackage' => $autoPrintPackage,
            '_autoPrintParcelInvoice' => $autoPrintParcelInvoice,
            '_autoPrintPackageInvoice' => $autoPrintPackageInvoice,
            '_showInvoice' => $showInvoice,
            '_fakeInvoice' => $fakeInvoice,
            '_showLabel' => $showLabel,
            '_packages' => $_packages,
            '_createPackage' => $_createPackage,
            '_cellUpdate' => $cellUpdate,
            '_cities' => $cities,
            '_cds' => $cds,
            '_store_status' => $store_status,
        ]);

        return $next($request);
    }
}
