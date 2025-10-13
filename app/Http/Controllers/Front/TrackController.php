<?php

namespace App\Http\Controllers\Front;

use App\Models\Extra\Notification;
use App\Models\Package;
use App\Models\PayPhone;
use DB;
use Illuminate\Http\JsonResponse;
use Request;
use Response;
use Artesaos\SEOTools\Facades\SEOTools as SEO;
use App\Models\Track;
use PDF;
use Illuminate\Validation\Rule;
use Validator;

/**
 * Class ExtraController
 *
 * @package App\Http\Controllers\Front
 */
class TrackController extends MainController
{
    public function finGet($code)
    {
        $ldate = date('Y-m-d H:i:s');

        $breadTitle = $title = trans('front.menu.contact_us');
        $cover = $this->cover('contact');
        SEO::setTitle(__('front.menu.track'));
        $item = Track::where('custom_id', $code)->first();
        if (!$item) {
            abort(404, 'Track not found');
        }

        return view('front.track.fin', compact('breadTitle', 'title', 'cover', 'item'));
    }

    public function payGet($code)
    {
        $ldate = date('Y-m-d H:i:s');

        $breadTitle = $title = trans('front.menu.contact_us');
        $cover = $this->cover('contact');
        SEO::setTitle(__('front.menu.track'));
        $item = Track::where('custom_id', $code)->first();
        if (!$item) {
            abort(404, 'Track not found');
        }

        return view('front.track.pay', compact('breadTitle', 'title', 'cover', 'item'));
    }

    public function payGetPhone($id)
    {
        $ldate = date('Y-m-d H:i:s');

        $breadTitle = $title = trans('front.menu.contact_us');
        $cover = $this->cover('contact');
        SEO::setTitle(__('front.menu.track'));
        $item = PayPhone::query()->findOrFail($id);
        if (!$item) {
            abort(404, 'Track not found');
        }


        return view('front.pay_phone.pay', compact('breadTitle', 'title', 'cover', 'item'));
    }

    public function payment()
    {
        $ldate = date('Y-m-d H:i:s');

        $breadTitle = $title = trans('front.menu.contact_us');
        $cover = $this->cover('contact');
        SEO::setTitle(__('front.menu.track'));

        return view('front.payment.payment', compact('breadTitle', 'title', 'cover'));
    }


    public function payGetDebt($code)
    {

        $item = Track::where('custom_id', $code)->first();
        if (!$item) {
            abort(404, 'Track not found');
        }

        return view('front.track.pay-debt', compact('item'));

    }

    public function payGetBroker($code)
    {

        $item = Track::where('custom_id', $code)->first();
        if (!$item) {
            abort(404, 'Track not found');
        }

        return view('front.track.pay-broker', compact('item'));

    }

    public function payGetDebtPackage($code)
    {
        $item = Package::where('custom_id', $code)->first();
        if (!$item) {
            abort(404, 'Package not found');
        }

        return view('front.track.pay-debt-package', compact('item'));

    }
    public function payGetBrokerPackage($code)
    {
        $item = Package::where('custom_id', $code)->with('user')->first();
        if (!$item) {
            abort(404, 'Package not found');
        }

        return view('front.track.pay-broker-package', compact('item'));
    }


    public function payDebtPost($code)
    {
        $item = Track::where('custom_id', $code)->first();
        if (!$item) {
            abort(404, 'Track not found');
        }
        if ($item->debt_price > 0) {
            Notification::sendTrack($item->id, 'customs_storage_fee');
        }
        return back();
    }

    public function payBrokerPost($code)
    {
        $item = Track::where('custom_id', $code)->first();
        if (!$item) {
            abort(404, 'Track not found');
        }
        Notification::sendTrack($item->id, 'customs_broker_fee');

        return back();
    }

    public function payBrokerPackagePost($code)
    {
        $item = Package::where('custom_id', $code)->first();
        if (!$item) {
            abort(404, 'Package not found');
        }
        Notification::sendPackage($item->id, 'customs_broker_fee');

        return back();
    }

    public function finPost($code)
    {
        $ldate = date('Y-m-d H:i:s');
        $validator = Validator::make(\Request::all(), [
            'fin' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $fin = \Request::get('fin');
        if (!$fin || strlen($fin) < 5 || strlen($fin) > 7) {
            $validator->getMessageBag()->add('fin', 'Fin kodunun uzunluğu 5 ilə 7 simvol arasında olmalıdır.');
            return back()->withErrors($validator)->withInput();
        }
        if (preg_match('/[^abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ0-9]/', $fin) > 0) {
            $validator->getMessageBag()->add('fin', 'Yanlış fin kodu formatı');
            return back()->withErrors($validator)->withInput();
        }
        $item = Track::where('custom_id', $code)->first();
        if (!$item) {
            abort(404, 'Track not found');
        }
        $item->fin = $fin;
        $item->save();
        $customer = $item->customer;
        if ($customer) {
            $customer->fin = $fin;
            $customer->save();
        }
        $item->carrierReset();

        $breadTitle = $title = trans('front.menu.contact_us');
        $cover = $this->cover('contact');
        SEO::setTitle(__('front.menu.track'));

        return redirect()->route('track-fin', $code)->with(['success' => true]);
    }

    public function PDFLabel($tracking_code)
    {
        $track = Track::with(['customer', 'warehouse', 'country'])->where('tracking_code', $tracking_code)->first();
        if (!$track) {
            abort(404, 'Track not found');
        }

        $shipper = $track->warehouse_id ? $track->warehouse : ($track->country ? $track->country->warehouse : null);

        if ($shipper && !$shipper->country) {
            abort(400, "Warehouse doesn't have a country.");
        }


        if (request()->has('html'))
            return view('admin.widgets.track-label', compact('track', 'shipper'));

        $pdf = PDF::loadView('admin.widgets.pdf-track-label', compact('track', 'shipper'));
        return $pdf->setPaper('a4', 'landscape')->setWarnings(false)->stream($track->id . '_label.pdf');
    }
}
