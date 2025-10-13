<?php

namespace App\Http\Controllers\Admin\Hub;

use App\Http\Controllers\Controller;
use App\Models\Hub\Box;
use App\Models\Hub\BoxPackage;
use Illuminate\Http\Request;
use Log;
use Milon\Barcode\DNS1D;

class BoxPackageController extends Controller
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

        return view('admin.hub.packages', [
            'packages' => $packagesQuery->paginate(25),
            'total_packages' => $packagesQuery->count(),
        ]);
    }

    public function store(Request $request, $id)
    {
        $box = Box::query()->where('id', $id)->first();
        if (!$box) {
            return back()->withErrors('Qutu movcud deyil!');
        }

        if (!$box->status) {
            return back()->withErrors('Bağlama əlavə edilmədi, Qutunun statusu bağlıdır!');
        }

        if (!$request->filled('tracking')) {
            return back()->withErrors('Tracking daxil edilməyib!');
        }

        $tracking = $request->input('tracking');

        $class = Box::CARRIERS_MAP[$box->carrier];
        $parcel = (new $class)->query()->where('barcode', $tracking)->first();
        if (!$parcel) {
            return back()->withErrors('Bağlama mövcud deyil!');
        }

        $preventDuplicate = BoxPackage::query()
            ->where([
                'tracking' => $tracking,
                'parcel_type' => $parcel->type,
                'parcel_id' => $parcel->id,
                'box_id' => $box->id,
            ])
            ->first();

        if ($preventDuplicate) {
            return back()->withErrors($tracking . ' artıq əlavə edilib!');
        }

        BoxPackage::query()->updateOrCreate([
            'tracking' => $tracking,
            'parcel_type' => $parcel->type,
            'parcel_id' => $parcel->id,
        ], [
            'box_id' => $box->id,
            'user_id' => auth()->user()->id,
        ]);

        $compact['success'] = $tracking . ' uğurla əlavə edildi!';

        return back()->with($compact);
    }

    public function print($id, Request $request)
    {
        $box = Box::where('id', $id)->first();
        if (!$box) {
            return back()->withErrors('Qutu tapılmadı!');
        }

        $packages = BoxPackage::query()
            ->where('box_id', $id)
            ->with(['box'])
            ->get();

        return view('admin.hub.packages', compact('box', 'packages'));
    }

    public function delete($boxId, $parcelId)
    {
        $parcel = BoxPackage::find($parcelId);
        if (!$parcel) {
            return back()->withErrors('Bağlama tapılmadı');
        }

        $parcel->delete();

        return back()->withSuccess('Uğurla silindi!');
    }

}
