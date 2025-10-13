<?php

namespace App\Http\Controllers\Front;

use Alert;
use App;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Career;
use App\Models\Country;
use App\Models\Faq;
use App\Models\Instagram;
use App\Models\Package;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Setting;
use App\Models\Store;
use App\Models\UkrExpressModel;
use App\Models\User;
use Artesaos\SEOTools\Facades\SEOTools as SEO;
use Auth;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PDF;
use Route;
use SEOMeta;
use Validator;

class MainController extends Controller
{
    protected $setting;

    protected $lang;

    public function __construct()
    {

        /*$this->setting = \Cache::remember('settings', 30 * 24* 60, function () {
            return Setting::find(1);
	});*/
        $this->setting = Setting::find(1);
        $route = Route::getCurrentRoute();
        $action = $route ? $route->getName() : 'shop';
        $cover = $this->cover($action);
        $this->lang = in_array(request()->segment(1), config('translatable.locales')) ? request()->segment(1) : config('translatable.fallback_locale');

        \View::share(['setting' => $this->setting, 'cover' => $cover]);
    }

    public function index()
    {
        $pageTitle = __('seo.homepage.title');
        SEO::setTitle($pageTitle);
        SEO::setDescription(__('seo.homepage.description'));
        SEO::addImages(asset('uploads/setting/' . $this->setting->header_logo));

        /*
            $stores = \Cache::remember('stores', 30 * 24* 60, function () {
                return Store::featured()->take(12)->latest()->get();
            });
            $countries = \Cache::remember('countries', 30 * 24* 60, function () {
                return Country::with(['warehouse'])->where('status', 1)->has('warehouse')->get();
            });
            $mainWarehouse = \Cache::remember('main_warehouse', 30 * 24* 60, function () {
                return (Country::with(['warehouse'])->where('status', 1)->has('warehouse')->orderBy('id', 'asc')->first())->warehouse;
        });*/
        $stores = Store::featured()->take(16)->latest()->get();
        $countries = Country::with(['warehouse'])->where('status', 1)->has('warehouse')->get();
        $mainWarehouse = (Country::with(['warehouse'])->where('status', 1)->has('warehouse')->orderBy('id', 'asc')->first())->warehouse;
        $news = Page::news()->latest()->paginate(3);
        $filials = \App\Models\Filial::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', '')
            ->where('longitude', '!=', '')
            ->where('deleted_at', '=', null)
            ->where('name', '!=', 'ecemi')
            ->where('name', 'NOT LIKE', '%tst%')->take(49)
            ->get();
        $instagrams = Instagram::query()->latest()->take(16)->latest()->get();
//        if(auth()->user()->id == 35035){
//
//            dd($filials);
//        }
        return view('front.new_main', compact('stores','instagrams', 'filials', 'countries', 'news', 'mainWarehouse'));
    }

    public function faq()
    {
        $faqs = Faq::all();

        $breadTitle = $title = trans('front.menu.faq');

        $cover = $this->cover('faq');
        SEO::setTitle(__('front.menu.faq'));

        return view('front.pages.faq', compact('faqs', 'title', 'breadTitle', 'cover'));
    }

    public function about()
    {
        $page = Page::whereKeyword('about')->first();
        if (!$page) {
            abort('404');
        }
        $breadTitle = $title = $page->translateOrDefault($this->lang)->title;
        $cover = $this->cover('about');

        SEO::setTitle(__('front.menu.about_us'));

        return view('front.pages.blank', compact('page', 'title', 'breadTitle', 'cover'));
    }

    public function contact()
    {
        $breadTitle = $title = trans('front.menu.contact_us');
        $cover = $this->cover('contact');
        SEO::setTitle(__('front.menu.contact'));

        return view('front.pages.contact', compact('breadTitle', 'title', 'cover'));
    }

    public function vacancy()
    {
        $jobs = Career::where('is_active', 1)->latest()->get();
        $breadTitle = $title = trans('front.menu.vacancy');
        SEO::setTitle(__('front.menu.vacancy'));
        return view('front.pages.vacancy', compact('jobs', 'breadTitle', 'title')
        );
    }

    public function tariffs()
    {
        $showSubButtons = [
            ['route' => 'tariffs', 'label' => 'front.menu.tariffs'],
            ['route' => 'calculator', 'label' => 'front.menu.calculator'],
        ];
        $breadTitle = $title = trans('front.menu.tariffs');
        $countries = Country::with(['warehouse', 'pages'])->where('status', 1)->has('warehouse')->get();
        $cover = $this->cover('tariffs');
        SEO::setTitle(__('seo.tariff.title'));
        SEO::setDescription(__('seo.tariff.description'));

        return view('front.pages.tariffs', compact('countries', 'showSubButtons', 'breadTitle', 'title', 'cover'));
    }

    public $uploadDir = 'uploads/stores/';

    public function apply(Request $request)
    {
        $application  = new Application();
        $application->vacancy_name = $request->vacancy_name;
        $application->name = $request->name;
        $application->phone = $request->phone;
        $application->email = $request->email;
        $application->surname = $request->surname;

        // Fayl yoxlanır
        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            $file = $request->file('file');

            // Unikal ad yaradılır
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();

            // Fayl `public/uploads/stores/` qovluğuna yazılır
            $file->move(public_path($this->uploadDir), $filename);

            // DB-də saxla
            $application->file = $this->uploadDir . $filename;
        }

        $application->save();

        return redirect()->back()->with('success', 'Müraciətiniz qeydə alındı');
    }


    public function calculator()
    {
        $user = Auth::user();
        $azerpoct = 0;
        $city_id = 0;
        if ($user) {
            $azerpoct = $user->azerpoct_send;
            $city_id = $user->city_id;
        }
        $showSubButtons = [
            ['route' => 'tariffs', 'label' => 'front.menu.tariffs'],
            ['route' => 'calculator', 'label' => 'front.menu.calculator'],
        ];
        $breadTitle = $title = trans('front.menu.calculator');
        $countries = Country::has('warehouse')->where('status', true)->get();
        $cover = $this->cover('calculator');
        SEO::setTitle(__('seo.calculator.title'));
        SEO::setDescription(__('seo.calculator.description'));
        $price = false;
        $result = null;

        if (request()->isMethod('post')) {
            $warehouse = (Country::with('warehouse')->find(request()->get('country')))->warehouse;
            if ($warehouse) {
                $result = $warehouse->calculateDeliveryPrice(request()->get('weight'), request()->get('weight_unit'), request()->get('width'), request()->get('height'), request()->get('length'), request()->get('length_unit'), false, 0, $azerpoct, $city_id);

                if (!$result) {
                    $result = trans('front.enter_weight'); // 'Enter weight or any size'
                } else {
                    $result .= ' ' . $warehouse->currency_with_label . ' (' . AZNWithLabel($result, $warehouse->currency) . ')';
                    $price = true;
                }
            } else {
                $result = trans('front.no_any_warehouse'); // 'No any warehouse for this country';
            }
        }

        return view('front.pages.calculator', compact('price', 'countries', 'showSubButtons', 'breadTitle', 'title', 'result', 'cover'));
    }

    public function news()
    {
        $validator = Validator::make(request()->all(), [
            'q' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            Alert::error('Unexpected variables!');

            return redirect()->back();
        }
        $cover = $this->cover('news');
        $news = Page::news()->latest()->paginate(12);
        $breadTitle = $title = trans('front.menu.news');
        SEO::setTitle(__('seo.news.title'));
        SEO::setDescription(__('seo.news.description'));

        return view('front.pages.news', compact('news', 'title', 'breadTitle', 'cover'));
    }

    public function single($slug)
    {
        $page = PageTranslation::findBySlug(strtolower($slug));

        if (!$page) {

            return abort(404);
        }

        $single = Page::find($page->page_id);

        if (!$single) {
            return abort(404);
        }
        $pageTitle = $single->translateOrDefault(App::getLocale())->meta_title ?: $single->translateOrDefault(App::getLocale())->title;
        $content = $single->translateOrDefault(App::getLocale())->meta_description ? $single->translateOrDefault(App::getLocale())->meta_description : __('seo.news.description');

        SEO::setTitle($pageTitle);
        SEO::setDescription($content);
        if ($single->translateOrDefault(App::getLocale())->meta_keywords) {
            SEOMeta::addKeyword(explode(",", $single->translateOrDefault(App::getLocale())->meta_keywords));
        }

        SEO::opengraph()->setUrl(request()->fullUrl());
        SEO::setCanonical(request()->fullUrl());
        SEO::addImages(asset($single->image));

        $breadTitle = $title = $single->translateOrDefault(App::getLocale())->title;
        $cover = $this->cover('news');

        return view('front.pages.single', compact('single', 'title', 'breadTitle', 'cover'));
    }

    public function page($slug, Request $request)
    {
        $page = PageTranslation::findBySlug(strtolower($slug));

        if (!$page) {

            return abort(404);
        }

        $page = Page::find($page->page_id);
        $breadTitle = $title = $page->translateOrDefault(App::getLocale())->title;
        $cover = $this->cover('news');
        $articles = Page::news()->latest()->take(3)->get();

        $pageTitle = $page->translateOrDefault(App::getLocale())->title;
        $content = $page->translateOrDefault(App::getLocale())->meta_description ? $page->translateOrDefault(App::getLocale())->meta_description : __('seo.news.description');
        SEO::setTitle($pageTitle);
        SEO::setDescription($content);
        SEO::addImages(asset($page->image));

        return view('front.pages.blank', compact('page', 'single', 'title', 'breadTitle', 'cover', 'articles', 'pageTitle'));
    }

    public function getTracking(Request $request)
    {
        $tracking = $request->input('tracking_code');
        $pageTitle = __('seo.homepage.title');
        SEO::setTitle($pageTitle);
        SEO::setDescription(__('seo.homepage.description'));
        SEO::addImages(asset('uploads/setting/' . $this->setting->header_logo));


        $stores = Store::featured()->take(12)->latest()->get();
        $countries = Country::with(['warehouse'])->where('status', 1)->has('warehouse')->get();
        $mainWarehouse = (Country::with(['warehouse'])->where('status', 1)->has('warehouse')->orderBy('id', 'asc')->first())->warehouse;
        $news = Page::news()->latest()->paginate(3);
        if (!$tracking) {
            return view('front.new_main', compact('stores', 'countries', 'news', 'mainWarehouse'));
        }

        $track = App\Models\Track::where('tracking_code', $tracking)->first();
        if (!$track) {
            // package and track not found
            return back()->withErrors('Axtardığınız tracking kod ile bağlama tapılmadı')->withInput();
        }
        $filials = \App\Models\Filial::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->where('latitude', '!=', '')
            ->where('longitude', '!=', '')
            ->where('deleted_at', '=', null)
            ->where('name', '!=', 'ecemi')
            ->where('name', 'NOT LIKE', '%tst%')->take(49)
            ->get();
        $instagrams = Instagram::query()->latest()->take(16)->latest()->get();
        return view('front.new_main', compact('stores', 'instagrams','filials', 'countries', 'news', 'mainWarehouse', 'track'));
    }

    public function cover($key)
    {
        $key .= "_cover";

        return $this->setting->{$key} ? asset('uploads/setting/' . $this->setting->{$key}) : asset('uploads/default/page-header.jpg');
    }


    public function calcPrice()
    {
        $user = Auth::user();
        $azerpoct = 0;
        $city_id = 0;
        if ($user) {
            $azerpoct = $user->azerpoct_send;
            $city_id = $user->city_id;
        }
        $country = Country::with('warehouse')->find((int)request()->get('country'));

        $warehouse = $country ? $country->warehouse : null;
        if ($warehouse) {
            $result = $warehouse->calculateDeliveryPrice((float)request()->get('weight'), 0, (float)request()->get('width'), (float)request()->get('height'), (float)request()->get('length'), 0, true, 0, $azerpoct, $city_id);

            if (!$result) {
                $result = "$0.00"; // 'Enter weight or any size'
            }
        } else {
            $result = "$0.00"; // 'No any warehouse for this country';
        }

        return $result;
    }

    /**
     * PDF Invoice for package
     *
     * @param $id
     * @return Factory|View
     */
    public function PDFInvoice($id)
    {
        $user = Auth::user();
        if (!$user) {
            return abort(404);
        }
        $user_id = $user->id;
        $item = Package::with(['user', 'warehouse', 'country'])->where('id', $id)->where('user_id', $user_id)->first();
        if (!$item) {
            return abort(404);
        }
        $shipper = $item->warehouse_id ? $item->warehouse : ($item->country ? $item->country->warehouse : null);

        if (!$item) {
            return abort(404);
        }
        if (request()->has('html'))
            return view('front.widgets.invoice', compact('item', 'shipper'));

        $pdf = PDF::loadView('front.widgets.invoice', compact('item', 'shipper'));

        return $pdf->setPaper('a4')->setWarnings(false)->stream($id . '_invoice.pdf');
    }

    public function photo($id)
    {
        $user = Auth::user();
        if (!$user) {
            return abort(404);
        }
        $user_id = $user->id;
//        $item = Package::with(['user', 'warehouse', 'country'])->where('id', $id)->where('user_id', $user_id)->where('warehouse_id', 11)->whereIn('status', [0, 7, 1, 2, 8, 4])->first();
        $item = Package::with(['user', 'warehouse', 'country'])->where('id', $id)->where('warehouse_id', 11)->whereIn('status', [0, 7, 1, 2, 8, 4])->first();
        if (!$item) {
            return abort(404);
        }

        $ue = new UkrExpressModel();
        $ue->doLog = false;
        $track = $ue->track_get($item->ukr_express_id, $user->ukr_express_id);
        if (!$item) {
            return abort(404);
        }
        if (!isset($track->photos_info) || $track->photos_info->has_any_photos <= 0) {
            return abort(404);
        }
        $photos = $ue->track_get_photos($track->id, $track->customer_id);
        return view('front.widgets.photos', compact('item', 'photos', 'track', 'user'));
    }

    public function PDFLabel($id)
    {
        $user = Auth::user();
        if (!$user) {
            return abort(404);
        }
        $user_id = $user->id;
        $item = Package::with(['user', 'warehouse', 'country'])->where('id', $id)->where('user_id', $user_id)->first();
        if (!$item) {
            return abort(404);
        }
        $shipper = $item->warehouse_id ? $item->warehouse : ($item->country ? $item->country->warehouse : null);

        if (request()->has('html'))
            return view('admin.widgets.pdf_label', compact('item', 'shipper'));

        $pdf = PDF::loadView('admin.widgets.pdf_label', compact('item', 'shipper'));
        return $pdf->setPaper('a4', 'landscape')->setWarnings(false)->stream($id . '_label.pdf');
    }


    public function fin()
    {
        die;
        $users = User::whereNull('fin')->whereNotNull('phone')->latest()->get();

        $content = "phone";

        foreach ($users as $user) {
            echo ($user->email) . "<br/>";
        }
        die;
    }

    public function getCountryTariffs($countryKey)
    {
        $country = Country::with(['warehouse', 'pages'])
            ->where('status', 1)
            ->has('warehouse')
            ->find($countryKey);

        return view('front.main.main_partials.tariffs-content', [
            'country' => $country
        ]);
    }
}
