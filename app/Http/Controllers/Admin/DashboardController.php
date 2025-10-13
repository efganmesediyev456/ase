<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Models\Extra\Cashback;
use App\Models\Order;
use App\Models\Package;
use App\Models\Page;
use App\Models\User;
use Auth;
use Carbon\Carbon;
use DB;

class DashboardController extends Controller
{
    protected $templateDir = 'admin.panel.dashboards.';

    public function main()
    {
        //if (auth()->guard('admin')->user() && auth()->guard('admin')->user()->role_id == 1) {
        if (Auth::user()->can('read-dashboard')) {

            $balance = Cashback::balance();
            //dd($balance);

            $lastNews = Page::news()->latest()->take(4)->get();

            $last30Days = Carbon::now()->subDays(30)->toDateTimeString();
            $last7Days = Carbon::now()->subDays(7)->toDateTimeString();

            $total30Orders = Order::where('created_at', '>=', $last30Days)->count();
            $total30Packages = Package::where('created_at', '>=', $last30Days)->setEagerLoads([])->count();
            $total30Users = User::where('created_at', '>=', $last30Days)->setEagerLoads([])->count();

            $total7Orders = Order::where('created_at', '>=', $last7Days)->setEagerLoads([])->count();
            $total7Packages = Package::where('created_at', '>=', $last7Days)->setEagerLoads([])->count();
            $total7Users = User::where('created_at', '>=', $last7Days)->setEagerLoads([])->count();

            $totalOrders = Order::setEagerLoads([])->count();
            $totalPackages = Package::setEagerLoads([])->count();
            $totalUsers = User::setEagerLoads([])->count();

            $ordersMyMonths = Package::setEagerLoads([])->select(DB::raw('DATE_FORMAT(created_at, "%Y - %m (%b)") AS month'), DB::raw('count(id) as total'))->where('created_at', '>=', Carbon::now()->subMonths(24))->whereIn('status', [
                1,
                2,
                3,
            ])->orderBy('month', 'asc')->groupBy('month')->get();

            $linksData = Package::setEagerLoads([])->where('created_at', '>=', $last30Days)->get();
            $links = [];

            foreach ($linksData as $link) {
                if ($link->website_name) {
                    $domainName = str_replace("&", "", getOnlyDomain($link->website_name));
                    $mDomain = md5($domainName);
                    if (isset($links[$mDomain])) {
                        $links[$mDomain]['count']++;
                    } else {
                        $links[$mDomain] = [
                            'domain' => $domainName,
                            'count' => 1,
                        ];
                    }
                }
            }
            foreach ($linksData as $link) {
                if ($link->url) {
                    $domainName = getDomain($link->url);
                    $mDomain = md5($domainName);
                    if (isset($links[$mDomain])) {
                        $links[$mDomain]['count']++;
                    } else {
                        $links[$mDomain] = [
                            'domain' => $domainName,
                            'count' => 1,
                        ];
                    }
                }
            }

            usort($links, function ($a, $b) {
                return $b['count'] - $a['count'];
            });

            $links = array_slice($links, 0, 10);

            /* Users */
            $orders = Package::setEagerLoads([])->select('user_id', DB::raw('count(id) as total'))->with('user')->groupBy('user_id')->orderBy('total', 'desc')->take(7)->get();

            $groupedUsers = [];

            foreach ($orders as $order) {
                $groupedUsers[] = [
                    'name' => $order->user ? $order->user->full_name : 'Deleted',
                    'total' => $order->total,
                ];
            }

            return view('admin.dashboard', compact('balance', 'lastNews', 'total30Orders', 'totalOrders', 'total30Users', 'totalUsers', 'totalPackages', 'total30Packages', 'total7Orders', 'total7Packages', 'total7Users', 'ordersMyMonths', 'links', 'groupedUsers'));
        }

        return view('admin.dashboard');
    }
}
