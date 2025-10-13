<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Models\Courier;
use App\Models\CourierShelf;
use App\Models\CourierShelfLog;
use App\Models\Package;
use App\Models\Track;
use Illuminate\Http\Request;
use Auth;

class CourierController extends Controller
{
    protected $view = [
        'name' => "Courier",
	'search' => [
            [
                'name' => 'q',
                'type' => 'text',
                'attributes' => ['placeholder' => 'Search...'],
                'wrapperAttributes' => [
                    'class' => 'col-lg-3',
                ],
            ],
	],
    ];

    protected $list = [
        'id',
        'name',
        'phone',
        'email',
        'location_url2' => [
            'label' => 'Location',
        ],
    ];

    protected $fields = [
        [
            'name' => 'name',
            'label' => 'Name',
            'type' => 'text',
            'validation' => 'required|string',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
        ],
        [
            'name' => 'phone',
            'label' => 'Phone',
            'type' => 'text',
            'validation' => 'required|string|unique:couriers,phone',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
        ],
        [
            'name' => 'email',
            'label' => 'Email',
            'type' => 'email',
            'validation' => 'required|email|unique:couriers,email',
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
        ],
        [
            'name' => 'password',
            'label' => 'Password',
            'type' => 'password',
            'validation' => [
                'store' => 'required|string|min:6',
                'update' => 'nullable|string|min:6',
            ],
            'wrapperAttributes' => [
                'class' => 'col-md-4',
            ],
        ],
    ];

    protected $extraActions = [
        [
            'key' => 'id',
            'label' => 'Areas',
            'icon' => 'map',
            'route' => 'courier_areas.index',
            'color' => 'success',
        ],
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function indexObject()
    {
	$items = Courier::orderBy('name','asc');
        if (\Request::get('q') != null) {
            $q = strtolower ( trim(str_replace('"', '', \Request::get('q'))));
	    $items = $items->whereRaw("(lower(name) like '%" . $q . "%' or phone like '%" . $q . "%'  or lower(email) like '%" . $q . "%')");
	}
	$items = $items->paginate($this->limit);

        return $items;
    }


    public function courierShelfIndex()
    {
        $results = CourierShelf::where('delete',0)->get();

        foreach ($results as $result) {
            $result->package_count = Package::where('shelf_id', $result->id)->count();
            $result->track_count = Track::where('shelf_id', $result->id)->count();
            $result->total_products = $result->package_count + $result->track_count;
        }

        return view('admin.courier-shelf.index', compact('results'));
    }

    public function courierShelfDelete($id)
    {
        $shelf = CourierShelf::find($id);
        $package_count = Package::where('shelf_id', $shelf->id)->count();
        $track_count = Track::where('shelf_id', $shelf->id)->count();
        $total_products = $package_count + $track_count;

        if($total_products > 0){
            return redirect()->back()->with('error','There are currently products on the shelves.');
        }

        $shelf->delete = 1;
        $shelf->save();

        return redirect()->back()->with('success','Shelf delete success.');
    }

    public function courierShelfSticker($id)
    {
        $result = CourierShelf::find($id);
        return view('admin.courier-shelf.sticker', compact('result'));
    }

    public function courierShelfCreate()
    {
        $couriers = Courier::where('deleted_at',null)->get();
        return view('admin.courier-shelf.create')->with(['couriers' => $couriers]);
    }

    public function courierShelfCreatePost(Request $request)
    {
        $result = CourierShelf::where('barcode',$request->barcode)->where('delete',0)->first();

        if($result){
            return redirect()->back()->with('error','Shelf already available');
        }

        if (strlen($request->barcode) > 6) {
            return back()->withErrors(['barcode' => 'Barkod 6 simvoldan cox olabilmez.']);
        }

        $newShelf = new CourierShelf();
        $newShelf->name = $request->name;
        $newShelf->barcode = $request->barcode;
        $newShelf->courier_id = $request->courier_id;
        $newShelf->save();

        return redirect()->back()->with('success','Shelf create success');
    }

    public function courierShelfAddProduct()
    {
        return view('admin.courier-shelf.add-product');
    }

    public function courierShelfAddProductPost(Request $request)
    {
        $result = null;
        $type = null;
        $trackingCode = null;
        $operationType = null;
        $requestType = null;

        if(!$request->shelf_barcode){
            if(strlen($request->tracking_code) <= 6){
                $findShelf = CourierShelf::where('barcode',$request->tracking_code)->where('delete',0)->first();
                if($findShelf){
                    $request->merge(['shelf_barcode' => $findShelf->barcode]);
                    return redirect()->back()->withInput()->with('success', 'Ref tapildi mehsullari oxudun: ' . $findShelf->name);
                }else{
                    return redirect()->back()->withInput()->with('error', 'Ref tapilmadi');
                }
            }else{
                return redirect()->back()->withInput()->with('error', 'Ilk ref barkodu oxudulmalidir');
            }

        }

        if(strlen($request->tracking_code) <= 6){
            $findShelf = CourierShelf::where('barcode',$request->tracking_code)->where('delete',0)->first();
            if($findShelf){
                $request->merge(['shelf_barcode' => $findShelf->barcode]);
                return redirect()->back()->withInput()->with('success', 'Ref tapildi mehsullari oxudun: ' . $findShelf->name);
            }else{
                return redirect()->back()->withInput()->with('error', 'Ref tapilmadi');
            }
        }else{
            $findShelf = CourierShelf::where('barcode',$request->shelf_barcode)->where('delete',0)->first();
            $searchPackage = Package::where('custom_id',$request->tracking_code)->where('courier_delivery_id','!=',null)->where('deleted_at',null)->first();
            $searchTrack = Track::where('tracking_code',$request->tracking_code)->where('courier_delivery_id','!=',null)->where('deleted_at',null)->first();
        }


        if($searchPackage){
            $result = $searchPackage;
            $type = 'packages';
        }elseif($searchTrack){
            $result = $searchTrack;
            $type = 'tracks';
        }else{
            return redirect()->back()->withInput()->with('error', 'Product not found');
        }


        if(!$result->shelf_id){
            $findResultCourierId = $result->courierDelivery->courier_id ?? null;
            if($findResultCourierId){
                if($findResultCourierId != $findShelf->courier_id){
                    return redirect()->back()->withInput()->with('error', 'Refin kuryeri ile Baglamanin kuryer sifarisi eyni deyil');
                }
            }
        }



        if($type == 'packages'){
            $trackingCode = $searchPackage->custom_id;
        }elseif($type == 'tracks'){
            $trackingCode = $searchTrack->tracking_code;
        }

        if($result->shelf_id){
            $operationType = 'transfer';
        }else{
            $operationType = 'add';
        }

        if($result->shelf_id != $findShelf->id){
            $createLog = new CourierShelfLog();
            $createLog->custom_id = $result->id;
            $createLog->type = $type;
            $createLog->admin_id = Auth::guard('admin')->user()->id;
            $createLog->comment = $type.' '.$trackingCode.' '.$operationType.' shelf: '.$findShelf->name;
            $createLog->save();
        }else{

            if ($type === 'packages' && $searchPackage) {
                $searchPackage->status = 8; // in_kobia
                $searchPackage->save();
            }

            if ($type === 'tracks' && $searchTrack) {
                $searchTrack->status = 20; // in_kobia
                $searchTrack->save();
            }

            $operationType = 'same_shelf';
        }

        $result->shelf_id = $findShelf->id;
        $result->save();


        if ($operationType == 'add') {
            return redirect()->back()->withInput()->with('success', 'Product add shelf: ' . $findShelf->name);
        } elseif ($operationType == 'transfer') {
            return redirect()->back()->withInput()->with('success', 'Product transfer shelf: ' . $findShelf->name);
        } elseif ($operationType == 'same_shelf') {
            return redirect()->back()->withInput()->with('success', 'Product shelf: ' . $findShelf->name);
        }


    }


    public function courierShelfProducts($id)
    {
        $shelf = CourierShelf::find($id);
        $packages = Package::where('shelf_id',$id)->get();
        $tracks = Track::where('shelf_id',$id)->get();

        return view('admin.courier-shelf.products', compact('shelf','packages','tracks'));

    }

    public function courierShelfEdit($id)
    {
        $shelf = CourierShelf::find($id);
        $couriers = Courier::where('deleted_at',null)->get();
        return view('admin.courier-shelf.edit', compact('shelf','couriers'));
    }

    public function courierShelfEditPost(Request $request,$id)
    {
        $result = CourierShelf::find($id);
        $otherBarcod = CourierShelf::where('barcode',$request->barcode)->where('delete',0)->first();

        if($otherBarcod){
            return redirect()->back()->with('error','Barkod movcuddur');
        }

        if (strlen($request->barcode) > 6) {
            return back()->withErrors(['barcode' => 'Barkod 6 simvoldan cox olabilmez.']);
        }

        $result->name = $request->name;
        $result->barcode = $request->barcode;
        $result->courier_id = $request->courier_id;
        $result->save();

        return redirect()->back()->with('success','Shelf create success');
    }


}
