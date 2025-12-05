<?php

namespace App\Http\Controllers\Front;

use App;
use App\Mail\OrderRequest;
use App\Models\CD;
use App\Models\City;
use App\Models\Country;
use App\Models\Kargomat\KargomatOffice;
use App\Models\Page;
use App\Models\CustomsType;
use App\Models\Extra\Notification;
use App\Models\Link;
use App\Models\Order;
use App\Models\Package;
use App\Models\Promo;
use App\Models\Surat\SuratOffice;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Azerpost\AzerpostOffice;
use App\Models\AzeriExpress\AzeriExpressOffice;
use App\Models\DeliveryPoint;
use App\Models\YeniPoct\YenipoctOffice;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Artisan;
use Mail;
use Validator;
use View;

//use App\Models\PackageType;

//use App\Models\RuType;

class UserController extends MainController
{
    public function generalShare()
    {

        $showSubButtons = [
            ['route' => 'addresses', 'label' => 'front.user.addresses'],
            ['route' => 'my-packages', 'label' => 'front.user.packages'],
            ['route' => 'cds', 'label' => 'front.user.courier_deliveries'],
            ['route' => 'my-orders', 'label' => 'front.user.orders'],
            ['route' => 'edit', 'label' => 'front.user.profile'],
        ];

        View::share([
            'showSubButtons' => $showSubButtons,
            'cover' => $this->cover('user'),
            'spending' => $this->spending(),
        ]);
    }

    public function addresses()
    {
        $this->generalShare();
        $countries = Country::with(['warehouses', 'pages'])->has('warehouse')->where('status', 1)->get();
        $breadTitle = $title = trans('front.user.addresses');
        $user = Auth::user();
        return view('front.user.addresses', compact('countries', 'title', 'breadTitle', 'user'));
    }

    public function orders()
    {
        $this->generalShare();
        $orders = Order::withCount('links')->whereUserId(Auth::user()->id)->orderBy('created_at', 'desc')->orderBy('status', 'ASC')->paginate(8);

        $breadTitle = $title = trans('front.user.orders');

        return view('front.user.orders', compact('orders', 'breadTitle', 'title'));
    }

    public function order($id)
    {
        $this->generalShare();
        $order = Order::with('links')->whereUserId(Auth::user()->id)->find($id);
        if (!$order) {
            return abort(404);
        }

        $breadTitle = $title = trans('front.user.orders');

        return view('front.user.order', compact('order', 'breadTitle', 'title', 'id'));
    }

    public function createOrder()
    {
        $this->generalShare();
        $countriesObj = Country::has('warehouse')->where('status', 1)->get();
        $countries = [];
        $breadTitle = $title = trans('front.create_order_title');
        foreach ($countriesObj as $country) {
            $countries[$country->id] = $country->translateOrDefault(App::getLocale())->name;
        }

        return view('front.user.create-order', compact('countries', 'breadTitle', 'title'));
    }

    public function storeOrder(Request $request)
    {
        $this->validate($request, [
            'country' => 'required|integer',
            'note' => 'nullable|string',
            'url.*.link' => 'nullable|url',
            'url.*.note' => 'nullable|string',
        ]);

        $hasUrl = false;

        foreach ($request->get('url') as $url) {
            if ($url['link']) {
                $hasUrl = true;
            }
        }

        if (!$hasUrl) {
            return back()->with(['error' => true]);
        }

        $order = new Order();
        $order->user_id = Auth::user()->id;
        $order->country_id = $request->get('country');
        $order->note = $request->get('note');
        $order->custom_id = uniqid();
        $order->save();

        $links = [];
        foreach ($request->get('url') as $url) {
            if ($url['link']) {
                $link = new Link();
                $link->order_id = $order->id;
                $link->url = $url['link'];
                $link->note = $url['note'];
                $link->save();
                $links[] = $link->id;
            }
        }

        $newOrder = Order::with(['country', 'links', 'user'])->find($order->id);

        Notification::sendOrder($order->id);

        $country = Country::find($request->get('country'));

        if ($country->emails) {
            $toAdmins = array_map('trim', explode(",", $country->emails));
            Mail::to($toAdmins)->send(new OrderRequest($newOrder));
        }

        /* Send notification */
        $message = null;
        $message .= "🔗 <b>" . auth()->user()->full_name . "</b> (" . auth()->user()->customer_id . ") ";
        $message .= $newOrder->country->name . " ölkəsi üzrə ";
        $message .= "<a href='https://admin." . env('DOMAIN_NAME') . "/orders/" . $newOrder->id . "/links'>" . count($links) . " ədəd link</a> sifariş etdi.";

        sendTGMessage($message);

        return redirect()->route('my-orders')->with(['success' => true]);
    }

    public function deleteOrder($id)
    {
        $order = Order::whereUserId(auth()->user()->id)->where('id', $id)->first();

        if ($order) {
            $order->delete();

            return redirect()->back()->with(['deleted' => true]);
        } else {
            return redirect()->back();
        }
    }

    public function deleteLink($id)
    {
        $link = Link::where('id', $id)->first();

        if ($link && $link->order->user_id == auth()->user()->id) {
            $link->delete();

            return redirect()->back()->with(['deleted' => true]);
        } else {
            return redirect()->back();
        }
    }

    public function packages($id = 0)
    {
        $this->generalShare();
        $breadTitle = $title = trans('front.user.packages');
        $user = User::find(Auth::user()->id);

        if (!$user) {
            return abort(404);
        }
        $warehousesArr = '[]';
        if ($id == 2) {
            $packages = Package::whereIn("user_id",$user->getIds())->whereIn("status", [2, 4]);
        } else {
            $packages = Package::with(['warehouse'])->whereIn("user_id",$user->getIds())->whereStatus($id);
            $warehouses = Warehouse::get();
            $warehousesArr = '{';
            foreach ($warehouses as $warehouse) {
                if ($warehousesArr == '{') $warehousesArr .= $warehouse->country_id . ":" . ($warehouse->no_invoice ? "1" : "0");
                else $warehousesArr .= ',' . $warehouse->country_id . ":" . ($warehouse->no_invoice ? "1" : "0");
            }
            $warehousesArr .= '}';
        }
        if (\Request::has('last_30_days')) {
            $startForExists = Carbon::now()->subDays(30)->format('Y-m-d h:i:s');
            $packages = $packages->where('created_at', '>=', $startForExists);
        }
        $packages = $packages->latest()->paginate(8);

//        dd($packages->get());

        $counts = (DB::table('packages')->select('status', DB::raw('count(*) as total'))->whereNull('deleted_at')->whereIn("user_id",$user->getIds())->groupBy('status')->pluck('total', 'status'))->all();
        if (array_key_exists(4, $counts)) {
            if (array_key_exists(2, $counts))
                $counts[2] += $counts[4];
            else
                $counts[2] = $counts[4];
        }

        return view('front.user.packages', compact('packages', 'breadTitle', 'title', 'id', 'counts', 'warehousesArr','user'));
    }

    public function declaration($id, $page = false)
    {
        $user = User::find(Auth::user()->id);
        if (!$user) {
            return abort(404);
        }
        $item = Package::whereIn("user_id",$user->getIds())->whereId($id)->first();
        if (!$item) {
            return abort(404);
        }
        //$categoriesObj = PackageType::where('id', '!=', env('OTHER_ID', 10))->get();
        //$other = PackageType::withTrashed()->whereId(env('OTHER_ID', 10))->first();
        $categories = [];
        //$ru_categories = [];
        $customs_categories = CustomsType::select('id', 'parent_id', 'name_' . App::getLocale())->orderBy('name_' . App::getLocale())->get();

        //['id','parent_id','name_'.\App::getLocale()]);
        /*foreach ($categoriesObj as $category) {
            $categories[$category->id] = $category->translateOrDefault(\App::getLocale())->name;
        }
	asort($categories);*/
        /*if($item->warehouse_id==12)
        {
                $ruCategoriesObj = RuType::get();
                foreach ($ruCategoriesObj as $ruCategory) {
                    $ru_categories[$ruCategory->id] = $ruCategory->name;
                }
               asort($ru_categories);
        }*/
        //$categories[$other->id] = $other->translateOrDefault(\App::getLocale())->name;
	$decPages=Page::whereRaw("keyword like 'declaration-%'")->orderBy('order_num','desc')->get();

        $view = $page ? 'front.user.declaration-page' : 'front.user.declaration';
	$item->pkg_goods='0';

        //return view($view, compact('item', 'categories', 'ru_categories','customs_categories'));
        return view($view, compact('item', 'categories', 'customs_categories','decPages'));
    }

    public function declarationUpdate($id)
    {

        $user = User::find(Auth::user()->id);
        if (!$user) {
            return abort(404);
        }
        $package = Package::with(['warehouse'])->whereIn("user_id",$user->getIds())->whereId($id)->first();
        $invoice = ($package->invoice || $package->warehouse_id == 12 || ($package->warehouse && $package->warehouse->no_invoice)) ? '' : 'required|';

        $validator = Validator::make(\Request::all(), [
            'shipping_amount_cur' => 'required|integer',
            'otp_code' => 'nullable|string',
            'website_name' => 'required|string|min:3',
            'invoice' => $invoice . 'mimes:jpeg,png,pdf,doc,docx,jpg,xls|max:8000',
        ]);

        if ($validator->fails()) {
            return redirect()->route('declaration.edit', [
                'id' => $id,
                'page' => 'page',
            ])->withErrors($validator)->withInput();
        }

        $package = Package::whereIn("user_id",$user->getIds())->whereId($id)->first();

        if (!$package || !in_array($package->status,[0,6])) {
            return abort(404);
        }

        $package->otp_code = \Request::get('otp_code');
        $package->shipping_amount_cur = \Request::get('shipping_amount_cur');
        $package->has_battery = \Request::get('has_battery');
        $package->do_use_goods = true;
        $package->website_name = \Request::get('website_name');
        $package->other_type = \Request::get('type_id') == env('OTHER_ID', 10) ? \Request::get('other_type') : null;
        $package->declaration = true;

        if (\Request::hasFile('invoice')) {
            $fileName = uniqid() . '.' . \Request::file('invoice')->getClientOriginalExtension();
            \Request::file('invoice')->move(public_path('uploads/packages/'), $fileName);
            $package->invoice = $fileName;
        }

        $package->save();
        $package->saveGoodsFromRequest(\request());

        if($package->status == 0){
            $package->status = '';
        }

        Artisan::call('carriers:update', ['package' => 1, 'package_id' => $id, 'checkonly' => 0, 'htmlformat' => 1]);
        if (ob_get_level()) {
            ob_end_clean();
        }
        return redirect()->route('my-packages', $package->status)->with(['success' => true]);
    }

    public function declarationDelete($id)
    {
        $user = User::find(Auth::user()->id);
        if (!$user) {
            return abort(404);
	}
        $package = Package::whereIn("user_id",$user->getIds())->whereId($id)->first();
        if (!$package || !in_array($package->status,[0,6])) {
            return abort(404);
        }
        /* Send notification */
        $message = null;
        $message .= "🛑 <b>" . auth()->user()->full_name . "</b> (" . auth()->user()->customer_id . ") ";
        $message .= "<a href='https://admin." . env('DOMAIN_NAME') . "/packages/" . $package->id . "/edit'>" . $package->tracking_code . "</a> tracking code ilə olan bəyannaməsini sildi!";
        sendTGMessage($message);

        $package->delete();

        return back();
    }

    public function declarationCreate()
    {
        //$categoriesObj = PackageType::where('id', '!=', env('OTHER_ID', 10))->get();
        //$ruCategoriesObj = RuType::get();
        //$other = PackageType::withTrashed()->whereId(env('OTHER_ID', 10))->first();
        $noDecCountries = [];
        $categories = [];
        //$ru_categories = [];
        $customs_categories = CustomsType::select('id', 'parent_id', 'name_' . App::getLocale())->orderBy('name_' . App::getLocale())->get();

        /*foreach ($categoriesObj as $category) {
            $categories[$category->id] = $category->translateOrDefault(\App::getLocale())->name;
        }
	asort($categories);*/
        /*foreach ($ruCategoriesObj as $ruCategory) {
            $ru_categories[$ruCategory->id] = $ruCategory->name;
        }
	asort($ru_categories);*/
        /*if ($other) {
            $categories[$other->id] = $other->translateOrDefault(\App::getLocale())->name;
	}*/

        $countriesObj = Country::whereHas('warehouses')->where('status', 1)->orderBy('allow_declaration', 'desc')->orderBy('id', 'desc')->get();
        $countries = [];
        $countries[''] = '-';
        foreach ($countriesObj as $country) {
            if (!$country->allow_declaration || $country->code == 'ru') {
                $noDecCountries[] = $country->id;
            }
            $countries[$country->id] = $country->translateOrDefault(App::getLocale())->name;
        }
	$decPages=Page::whereRaw("keyword like 'declaration-%'")->orderBy('order_num','desc')->get();

        $view = 'front.user.declaration-create';

        //return view($view, compact('categories', 'ru_categories', 'countries', 'noDecCountries','customs_categories'));
        return view($view, compact('categories', 'countries', 'noDecCountries', 'customs_categories','decPages'));
    }

    public function declarationStore()
    {
        $country = Country::find((int)\Request::get('country_id'));
        if (!$country || ($country && !$country->allow_declaration)) {
            return back()->withInput();
        }

        $validator = Validator::make(\Request::all(), [
            'tracking_code' => 'required|string|min:6',
            'country_id' => 'required|integer',
            'shipping_amount_cur' => 'required|integer',
            'other_type' => 'nullable|string',
            'otp_code' => 'nullable|string',
            'website_name' => 'required|string|min:3',
            'invoice' => 'nullable|mimes:jpeg,png,pdf,doc,docx,jpg,xls|max:8000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $code = \Request::get('tracking_code');

        $package = Package::where("tracking_code", "like", "%" . $code . "%")->first();

        if (!$package && strlen($code) >= 10) {
            $start = -1 * strlen($code) + 1;
            $cnt = 0;
            for ($i = $start; $i <= -8; $i++) {
                $code = substr($code, $i);
                $package = Package::where("tracking_code", "like", "%" . $code . "%")->first();
                if ($package) {
                    break;
                }
                $cnt++;
                if ($cnt >= 8) break;
                if (strlen($code) >= 10) break;
            }
        }

        if ($package && $package->user_id) {
            $validator->getMessageBag()->add('tracking_code', 'Bu tracking number ilə bağlama artıq var');
            return back()->withErrors($validator)->withInput();
        }

	if($package) {
		if(!in_array($package->status,[0,6])) {
        	   return redirect()->route('my-packages', 6)->with(['success' => false]);
		}
	}


        if (!$package) {
            $package = new Package();
            $package->warehouse_id = null;
            $package->tracking_code = \Request::get('tracking_code');
            $package->country_id = \Request::get('country_id');
            $package->status = 6;
        }

        $package->user_id = Auth::user()->id;
        $package->shipping_amount_cur = \Request::get('shipping_amount_cur');
        $package->do_use_goods = true;
        $package->has_battery = \Request::get('has_battery');

        $package->website_name = \Request::get('website_name');
        $package->user_comment = \Request::get('user_comment') ?: null;
        $package->other_type = \Request::get('type_id') == env('OTHER_ID', 10) ? \Request::get('other_type') : null;
        $package->declaration = true;
        $package->otp_code = \Request::get('otp_code');

        if (\Request::hasFile('invoice')) {
            $fileName = uniqid() . '.' . \Request::file('invoice')->getClientOriginalExtension();
            \Request::file('invoice')->move(public_path('uploads/packages/'), $fileName);
            $package->invoice = $fileName;
        }

        $package->save();
        $package->saveGoodsFromRequest(\request());

        /* Send notification */
        $message = null;
        $message .= "🥡 <b>" . auth()->user()->full_name . "</b> (" . auth()->user()->customer_id . ") ";
        $message .= "<a href='https://admin." . env('DOMAIN_NAME') . "/packages/" . $package->id . "/edit'>" . $package->tracking_code . "</a> tracking code ilə yeni bəyannamə yaratdı.";

        sendTGMessage($message);

        return redirect()->route('my-packages', 6)->with(['success' => true]);
    }

    public function edit($nulled = null)
    {

        $this->generalShare();

        $item = Auth::user();
        $promo_id = $item->promo_id;
        $item->promo = null;
        if ($promo_id) {
            $promo = Promo::where('id', $promo_id)->first();
            if ($promo) $item->promo = $promo->code;
        }
        $breadTitle = $title = trans('front.user.profile');
        $cities = [
            0 => trans('front.city'),
        ];

        $filials = [
        ];

        $zipcities = [];

        $citiesObj = City::select('cities.*')->join('city_translations', 'cities.id', '=', 'city_translations.city_id')->orderBy('city_translations.name')->where('city_translations.locale', 'az')->get();
        $zipcities = AzerpostOffice::orderBy('name')->get();
        $azeriexpressoffices = AzeriExpressOffice::whereNotIn('name', ['Sumgait', 'Sheki'])->orderBy('description')->get();
        $deliverypoints = DeliveryPoint::orderBy('description')->whereNotIn('id',[10,11,12,13,14,15,16,17,18,19])->get();
        $suratOffices = SuratOffice::orderBy('description')->get();
        $yenipoctOffices = YenipoctOffice::orderBy('description')->get();
        $kargomatOffices = KargomatOffice::orderBy('description')->get();

        foreach ($deliverypoints as $deliverypoint) {
            $filials['ase_' . $deliverypoint->id] = "ASE - " . $deliverypoint->description;
        }

        foreach ($azeriexpressoffices as $azexpressoffice) {
            $filials[$azexpressoffice->id] = "Azeriexpress - " . $azexpressoffice->description;
        }

        foreach ($suratOffices as $suratOffice) {
            $filials['surat_' . $suratOffice->id] = "Surat Kargo - " . $suratOffice->description;
        }

        foreach ($yenipoctOffices as $yenipoctOffice) {
            $filials['yp_' . $yenipoctOffice->id] = "Yeni poçt - " . $yenipoctOffice->description;
        }

        foreach ($kargomatOffices as $kargomatOffice) {
            $filials['kargomat_' . $kargomatOffice->id] = "Kargomat - " . $kargomatOffice->description;
        }

        foreach ($zipcities as $zipcode) {
            $filials['zip_' . $zipcode->name] = "Azerpoct - " . $zipcode->name;
        }


        foreach ($citiesObj as $city) {
            $cities[$city->id] = $city->name;
        }

        if (!($item->azeri_express_use && $item->azeri_express_office_id)) {
            if ($item->azerpoct_send && $item->zip_code) {
                $item->azeri_express_office_id = 'zip_' . $item->zip_code;
	    } else if ($item->surat_use && $item->surat_office_id) {
                $item->azeri_express_office_id = 'surat_' . $item->surat_office_id;
            } else if ($item->yenipoct_use && $item->yenipoct_office_id) {
                $item->azeri_express_office_id = 'yp_' . $item->yenipoct_office_id;
            }else if ($item->kargomat_use && $item->kargomat_office_id) {
                $item->azeri_express_office_id = 'kargomat_' . $item->kargomat_office_id;
            } else {
                $item->azeri_express_office_id = 'ase_' . $item->store_status;
            }
        }
        $checkDeclarations = App\Models\PackageCarrier::where('fin',$item->fin)->where('ecoM_REGNUMBER','!=',null)->count();
        return view('front.user.edit', compact('item','checkDeclarations', 'breadTitle', 'cities', 'zipcities', 'filials', 'deliverypoints', 'azeriexpressoffices','yenipoctOffices','kargomatOffices', 'suratOffices','nulled'));
    }

    public function update(Request $request)
    {

        $user = User::find(Auth::user()->id);
        $user_id = $user->id;
        $promo_id = $user->promo_id;
        $user_promo_code = '';
        if ($promo_id) {
            $promo = Promo::where('id', $promo_id)->first();
            if ($promo) $user_promo_code = $promo->code;
            else $promo_id = null;
        }
        $promo_code = $request->get('promo');

        $request->merge(['passport' => $request->get('passport_prefix') . '-' . $request->get('passport_number')]);
        $digits = 'digits:' . ($request->get('passport_prefix') == 'AZE' ? 8 : 7);
        $passporta = $user->passporta ? '' : 'required|';
        $passportb = $user->passportb ? '' : 'required|';
        $agreement = $user->agreement ? '' : 'required|';

        $this->validate($request, [
            'name' => 'required|string|max:30|regex:/(^([a-zA-Z]+)?$)/u',
            'surname' => 'required|string|max:30|regex:/(^([a-zA-Z]+)?$)/u',
            'phone'    => 'nullable|string',
            //'email'    => 'required|email|string|max:255|unique:users,email,' . \Auth::user()->id,
            'passport_prefix' => 'required|in:AZE,AA,DYI,MYI',
            'passport_number' => 'required|' . $digits,
            'passport' => 'required|string|max:255|unique:users,passport,' . Auth::user()->id,
            'password' => 'nullable|min:6|confirmed',
            //'fin'             => 'required|alpha_num|unique:users,fin,' . \Auth::user()->id,
            'fin' => 'required|alpha_num|min:5|max:7|unique:users,fin,' . Auth::user()->id,
            'company' => 'nullable|max:100|string',
            'birthday' => 'nullable|date',
            'address' => 'required|string|min:10',
            'city_id' => 'nullable|integer',
            //'zip_code' => 'nullable|string|not_in:0|min:4|required_if:azerpoct_send,==,on',
            //'azerpoct_send' => 'nullable|string',
            //'azeri_express_use' => 'nullable|string',
            //'azeri_express_office_id' => 'nullable|string',
            //'azeri_express_office_id' => 'required_if:azeri_express_use,==,on|integer',
            //   'passporta'    => $passporta . 'mimes:jpeg,png,pdf,jpg|max:4000',
            //   'passportb'    => $passportb . 'mimes:jpeg,png,pdf,jpg|max:4000',
            //   'agreement'    => $agreement . 'mimes:jpeg,png,pdf,jpg|max:4000',
            'passporta' => 'nullable|mimes:jpeg,png,pdf,jpg|max:4000',
            'passportb' => 'nullable|mimes:jpeg,png,pdf,jpg|max:4000',
            'agreement' => 'nullable|mimes:jpeg,png,pdf,jpg|max:4000',
            'promo' => 'nullable|string|min:4|max:8',
            'promo' => ['nullable', Rule::exists('promos', 'code')->where(function ($query) use ($promo_id, $user_id, $promo_code, $user_promo_code) {
                $ldate = date('Y-m-d H:i:s');
                $subQuery = "not exists (select packages.promo_id from packages";
                $subQuery .= " where packages.promo_id=promos.id and packages.user_id=" . $user_id;
                $subQuery .= " group by packages.promo_id,packages.user_id";
                $subQuery .= " having (promos.amount > 0 and sum(packages.promo_discount_amount_azn)>=promos.amount)";
                $subQuery .= " or (promos.weight > 0 and sum(packages.promo_discount_weight)>=promos.weight)";
                $subQuery .= ")";
                if ($promo_id && !empty($promo_code) && $promo_code == $user_promo_code) {
                    $query->where('is_active', 1)->where('activation', 1)
                        ->whereRaw("(((start_at is null) or (start_at<='" . $ldate . "')) and ((stop_at is null) or (stop_at>='" . $ldate . "')))")
                        ->whereRaw($subQuery);
                } else {
                    $query->where('is_active', 1)->where('activation', 1)->whereRaw('num_to_use > num_used')
                        ->whereRaw("(((start_at is null) or (start_at<='" . $ldate . "')) and ((stop_at is null) or (stop_at>='" . $ldate . "')))")
                        ->whereRaw($subQuery);
                }
            }),
            ]]);

        //DebugBar::info('User update');

        $user->name = $request->get('name');
        $user->surname = $request->get('surname');
        //$user->email = $request->get('email');
//        $user->phone = $request->get('phone');
        $user->passport = $request->get('passport');
        $user->fin = $request->get('fin');
        $user->company = $request->has('company') ? $request->get('company') : null;
        $user->birthday = $request->has('birthday') ? $request->get('birthday') : null;
        $user->address = $request->has('address') ? $request->get('address') : null;
        $user->city_id = $request->has('city_id') ? $request->get('city_id') : null;
        //$user->zip_code = $request->has('zip_code') ? $request->get('zip_code') : null;
	$user->warning_num = 1;
        /*if ($request->has('azerpoct_send'))
            $user->azerpoct_send = 1;
        else
		$user->azerpoct_send = 0;*/
        /*if ($request->has('azeri_express_use'))
            $user->azeri_express_use = 1;
        else
	    $user->azeri_express_use = 0;*/
	$user->zip_code = '';
//	if ($request->has('azeri_express_office_id') && !empty($request->get('azeri_express_office_id')) ) {
//	    $azeri_express_office_id=$request->get('azeri_express_office_id');
//	    if(substr($azeri_express_office_id,0,4) == 'ase_') {
//                $user->store_status = substr($azeri_express_office_id,4);
//                $user->azeri_express_use = 0;
//                $user->surat_use = 0;
//                $user->yenipoct_use = 0;
//                $user->azerpoct_send = 0;
//                $user->azeri_express_office_id = NULL;
//	    } else if(substr($azeri_express_office_id,0,4) == 'zip_') {
//                $user->zip_code = substr($azeri_express_office_id,4);
//                $user->azeri_express_use = 0;
//                $user->surat_use = 0;
//                $user->yenipoct_use = 0;
//                $user->azerpoct_send = 1;
//                $user->azeri_express_office_id = NULL;
//                $user->store_status = 1;
//	    } else if(substr($azeri_express_office_id,0,6) == 'surat_') {
//                $user->surat_office_id = substr($azeri_express_office_id,6);
//                $user->azeri_express_use = 0;
//                $user->surat_use = 1;
//                $user->yenipoct_use = 0;
//                $user->azerpoct_send = 0;
//                $user->azeri_express_office_id = NULL;
//                $user->store_status = 1;
//	    } else if(substr($azeri_express_office_id,0,3) == 'yp_') {
//            $user->yenipoct_office_id = substr($azeri_express_office_id,3);
//            $user->azeri_express_use = 0;
//            $user->surat_use = 0;
//            $user->yenipoct_use = 1;
//            $user->azerpoct_send = 0;
//            $user->azeri_express_office_id = NULL;
//            $user->store_status = 1;
//        }else {
//                $user->store_status = 1;
//                $user->azerpoct_send = 0;
//                $user->azeri_express_use = 1;
//                $user->surat_use = 0;
//                $user->yenipoct_use = 0;
//                $user->azeri_express_office_id = $azeri_express_office_id;
//	    }
//	} else {
//            $user->store_status = 1;
//            $user->surat_use = 0;
//            $user->yenipoct_use = 0;
//            $user->azerpoct_send = 0;
//            $user->azeri_express_use = 0;
//            $user->azeri_express_office_id = NULL;
//	}
	/*if( $user->azerpoct_send) {
            $user->store_status = 1;
            $user->azeri_express_use = 0;
            $user->azeri_express_office_id = NULL;
	} */
        if (!empty($promo_code)) {
            if ($promo_code != $user_promo_code) {
                $ldate = date('Y-m-d H:i:s');
                $subQuery = "not exists (select packages.promo_id from packages";
                $subQuery .= " where packages.promo_id=promos.id and packages.user_id=" . $user_id;
                $subQuery .= " group by packages.promo_id,packages.user_id";
                $subQuery .= " having (promos.amount > 0 and sum(packages.promo_discount_amount_azn)>=promos.amount)";
                $subQuery .= " or (promos.weight > 0 and sum(packages.promo_discount_weight)>=promos.weight)";
                $subQuery .= ")";
                $promo = Promo::where('code', $promo_code)->where('is_active', 1)->where('activation', 1)->whereRaw('num_to_use > num_used')
                    ->whereRaw("(((start_at is null) or (start_at<='" . $ldate . "')) and ((stop_at is null) or (stop_at>='" . $ldate . "')))")
                    ->whereRaw($subQuery)->first();
                if ($promo) {
                    $user->promo_id = $promo->id;
                    $promo->num_used++;
                    $promo->save();
                } else
                    $user->promo_id = null;

            }
        } else
            $user->promo_id = null;


        if ($request->hasFile('passporta')) {
            $fileName = uniqid() . '.' . $request->file('passporta')->getClientOriginalExtension();
            $request->file('passporta')->move(public_path('uploads/passport/'), $fileName);
            $user->passporta = $fileName;
        }

        if ($request->hasFile('passportb')) {
            $fileName = uniqid() . '.' . $request->file('passportb')->getClientOriginalExtension();
            $request->file('passportb')->move(public_path('uploads/passport/'), $fileName);
            $user->passportb = $fileName;
        }

        if ($request->hasFile('agreement')) {
            $fileName = uniqid() . '.' . $request->file('agreement')->getClientOriginalExtension();
            $request->file('agreement')->move(public_path('uploads/passport/'), $fileName);
            $user->agreement = $fileName;
        }


        $user->save();

        return redirect()->route('edit')->with(['success' => true]);
    }

    public function spending()
    {
        $startForExists = Carbon::now()->firstOfMonth()->format('Y-m-d H:i:s');

        $data = NULL;
        $user = Auth::user();
        if ($user->check_customs) {
            $data = Package::join('package_carriers', 'packages.id', '=', 'package_carriers.package_id')->where('user_id', $user->id)->whereIn('package_carriers.code', [200, 400])->whereRaw("(((package_carriers.inserT_DATE is not null) and (package_carriers.inserT_DATE >='" . $startForExists . "')) or ((package_carriers.inserT_DATE is null) and (package_carriers.created_at >='" . $startForExists . "')))")->get();

        } else {
            $data = Package::where('user_id', $user->id)->whereNotNull('sent_at')->where('sent_at', '>=', $startForExists)->get();
        }

        $counts = [
            'sum' => 0,
            'total' => 0,
        ];

        if ($data) {
            foreach ($data as $package) {
                $counts['sum'] += $package->total_price;
                $counts['total']++;
            }
        }

        return $counts;
    }

    public function banned()
    {
        if (!auth()->user()->is_banned) {
            return redirect()->route('my-packages');
        }
        $this->generalShare();
        $breadTitle = $title = 'Opps';

        return view('front.user.banned', compact('breadTitle', 'title'));
    }

    public function cds($id = 0)
    {
        $this->generalShare();
        $user = User::find(Auth::user()->id);
        if (!$user) {
            return abort(404);
        }
        $cds = null;
        if ($id == 1)
            $cds = CD::whereUserId($user->id)->whereRaw('status in (1,2)')->orderBy('created_at', 'desc')->orderBy('status', 'ASC')->paginate(8);
        else
            $cds = CD::whereUserId($user->id)->where('status', $id)->orderBy('created_at', 'desc')->orderBy('status', 'ASC')->paginate(8);

        $breadTitle = $title = trans('front.user.courier_deliveries');
        $counts = (DB::table('courier_deliveries')->select('status', DB::raw('count(*) as total'))->whereNull('deleted_at')->where('user_id', Auth::user()->id)->groupBy('status')->pluck('total', 'status'))->all();

        return view('front.user.courier_deliveries', compact('cds', 'breadTitle', 'title', 'id', 'counts','user'));
    }

    public function showCD($id)
    {
        $this->generalShare();
        $user = User::find(Auth::user()->id);
        if (!$user) {
            return abort(404);
        }
        $cd = CD::whereUserId($user->id)->find($id);
        if (!$cd) {
            return abort(404);
        }

        $breadTitle = $title = trans('front.user.courier_deliveries');

        return view('front.user.courier_delivery1', compact('cd', 'breadTitle', 'title', 'id', 'user'));
    }

    public function payCD($id)
    {
        $this->generalShare();
        $user = User::find(Auth::user()->id);
        if (!$user) {
            return abort(404);
        }
        $cd = CD::whereUserId($user->id)->withTrashed()->find($id);
        if (!$cd) {
            return abort(404);
        }
        $breadTitle = $title = trans('front.user.courier_deliveries');

        return view('front.user.courier_delivery1', compact('cd', 'breadTitle', 'title', 'id', 'user'));
    }

    public function editCD($id)
    {
        $this->generalShare();
        $user = User::find(Auth::user()->id);
        if (!$user) {
            return abort(404);
        }
        $cd = CD::whereUserId($user->id)->find($id);
        if (!$cd) {
            return abort(404);
        }

        $breadTitle = $title = trans('front.user.courier_deliveries');

        return view('front.user.courier_delivery', compact('cd', 'breadTitle', 'title', 'id', 'user'));
    }

    public function updateCD($id)
    {
        $this->generalShare();
        $request = \request();
        $user = User::find(Auth::user()->id);
        if (!$user) {
            return abort(404);
        }
        $cd = CD::whereUserId($user->id)->find($id);
        if (!$cd) {
            return abort(404);
        }
        $this->validate($request, [
            'user_comment' => 'nullable|string|max:500',
            'name' => 'required|string|min:3,max:50',
            'phone' => 'nullable|string|max:20',
            'packages' => 'required|array|min:1',
        ]);
        $cd->name = $request->get('name');
        $cd->phone = $request->get('phone');
        $cd->desired_time = $request->get('desired_time');
        $cd->address = $request->get('address');
        $cd->user_comment = $request->get('user_comment');
        $packages = $request->get('packages');
        $pkgs = NULL;
        if ($packages && is_array($packages) && count($packages) > 0) {
            $users = $user->children()->pluck('id')->all();
            $user_id = $user->id;
            $pkgs = Package::whereIn('id', array_values($packages))->where(function ($query) use ($user_id, $users) {
                $query->where('user_id', $user_id)->orWhereIn('user_id', $users);
            })->get();
            $pkgs_txt = '';
            if ($pkgs)
                foreach ($pkgs as $pkg) {
                    if (!empty($pkgs_txt)) $pkgs_txt .= ',';
                    $pkgs_txt .= $pkg->custom_id;
                }
            $cd->packages_txt = $pkgs_txt;
        } else {
            $cd->packages_txt = NULL;
        }
        $cd->save();
        DB::update('update packages set courier_delivery_id=NULL where courier_delivery_id=?', [$cd->id]);
        if ($pkgs)
            DB::update('update packages set courier_delivery_id=? where id in (' . implode(',', array_values($pkgs->pluck('id')->all())) . ')', [$cd->id]);
        return redirect()->route('cds.pay',['id'=>$id]);

    }

    public function createCD()
    {
        $this->generalShare();
        $breadTitle = $title = trans('front.user.courier_deliveries');

        $user = User::find(Auth::user()->id);
        if (!$user && $user->id!=35035) {
            return abort(404);
        }
	if(!(!$user->azeri_express_use && ($user->store_status == 1 ||  $user->store_status == 2) && !$user->azerpoct_send) and $user->id!=35035) {
            return abort(404);
        }

        $cd = new CD();
        $cd->delivery_price = 3;
        $cd->desired_time = date('Y-m-d H:i:s');

        $lcd = CD::where('user_id', $user->id)->where('status', 0)->orderBy('id', 'desc')->first();
        if ($lcd) {
            $cd->name = $lcd->name;
            $cd->address = $lcd->address;
            $cd->phone = $lcd->phone;
        } else {
            $cd->name = $user->full_name;
            $cd->address = $user->address;
            $cd->phone = $user->phone;
        }

        return view('front.user.create-courier_delivery', compact('cd', 'breadTitle', 'title', 'user'));
    }

    public function storeCD(Request $request)
    {
        //DebugBar::info('StoreCD 1');
        $user = User::find(Auth::user()->id);
        if (!$user) {
            return abort(404);
        }
        $this->validate($request, [
            'user_comment' => 'nullable|string|max:500',
            'name' => 'required|string|min:3,max:50',
            'phone' => 'nullable|string|max:20',
            'packages' => 'required|array|min:1',
        ]);

        $cd = new CD();
        $cd->user_id = $user->id;
        $cd->delivery_price = 3;
        $cd->status = 0;
        $cd->name = $request->get('name');
        $cd->phone = $request->get('phone');
        $cd->desired_time = $request->get('desired_time');
        $cd->address = $request->get('address');
        $cd->user_comment = $request->get('user_comment');
        $cd->custom_id = uniqid();
        $cd->deleted_at = date('Y-m-d H:i:s');
        $packages = $request->get('packages');
        $pkgs = NULL;
        if ($packages && is_array($packages) && count($packages) > 0) {
            $users = $user->children()->pluck('id')->all();
            $user_id = $user->id;
            $pkgs = Package::whereIn('id', array_values($packages))->where(function ($query) use ($user_id, $users) {
                $query->where('user_id', $user_id)->orWhereIn('user_id', $users);
            })->get();
            $pkgs_txt = '';
            if ($pkgs)
                foreach ($pkgs as $pkg) {
                    if (!empty($pkgs_txt)) $pkgs_txt .= ',';
                    $pkgs_txt .= $pkg->custom_id;
                }
            $cd->packages_txt = $pkgs_txt;
        } else {
            $cd->packages_txt = NULL;
        }
        $cd->save();
        DB::update('update packages set courier_delivery_id=NULL where courier_delivery_id=?', [$cd->id]);
        if ($pkgs)
            DB::update('update packages set courier_delivery_id=? where id in (' . implode(',', array_values($pkgs->pluck('id')->all())) . ')', [$cd->id]);

	/*
        $newCD = CD::with(['user'])->find($cd->id);

        //Notification::sendCD($cd->id);

        // Send notification
        $message = null;
        $message .= "🔗 <b>" . auth()->user()->full_name . "</b> (" . auth()->user()->customer_id . ") ";
        $message .= $newCD->name . " Kuryer çatdırılması ";
        $message .= "<a href='https://admin." . env('DOMAIN_NAME') . "/cds/" . $newCD->id . "'> sifariş etdi.";

        sendTGMessage($message);

	return redirect()->route('cds')->with(['success' => true, 'id' => $id]);
	*/
	return redirect()->route('cds.pay',['id' => $cd->id]);
    }

    public function restoreCD($id)
    {
        $user = User::find(Auth::user()->id);
        if (!$user) {
            return abort(404);
        }
        $cd = CD::whereUserId($user->id)->where('id', $id)->where('status', 5)->first();

        if ($cd) {
            $cd->status = 0;
            $cd->courier_id = NULL;
            $cd->save();

            return redirect()->back()->with(['restored' => true]);
        } else {
            return redirect()->back();
        }
    }

    public function cancelCD($id)
    {
        $user = User::find(Auth::user()->id);
        if (!$user) {
            return abort(404);
        }
        $cd = CD::whereUserId($user->id)->where('id', $id)->whereIn('status', [0, 1, 2])->first();

        if ($cd) {
            $cd->status = 5;
            $cd->courier_id = NULL;
            $cd->save();

            return redirect()->back()->with(['canceled' => true]);
        } else {
            return redirect()->back();
        }
    }

    public function deleteCD($id)
    {
        $user = User::find(Auth::user()->id);
        if (!$user) {
            return abort(404);
        }
        $cd = CD::whereUserId($user->id)->where('id', $id)->first();

        if ($cd) {
            $cd->delete();

            return redirect()->back()->with(['deleted' => true]);
        } else {
            return redirect()->back();
        }
    }

    public function query()
    {
        $check = User::where('sms_verification_code_queried_at', '>=', Carbon::now()->subMinutes(3))->first();
        if ($check) {
            $mes = "";
            $dif = strtotime($check->sms_verification_code_queried_at) + 180 - time();
            if ($dif >= 60) {
                $mes .= (int)($dif / 60) . " dəqiqə";
                if ($dif % 60 != 0) {
                    $mes .= " " . ($dif - ((int)($dif / 60) * 60)) . " saniyə";
                }
            } else {
                $mes .= $dif . " saniyə";
            }
            return response()->json(['success' => false, 'data' => $mes], 400);
        }

        $user = User::findOrFail(\Illuminate\Support\Facades\Auth::user()->id);
        $user->sms_verification_code_queried_at = now();
        $user->save();
        return response()->json(['success' => true, 'data' => 'Uğurlu əməliyyat!'], 200);
    }
}
