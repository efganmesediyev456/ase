<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CD;
use App\Models\Extra\SMS;
use App\Models\Transaction;
use App\Services\KapitalBank\KapitalBankTxpgService;
use Illuminate\Http\Request;
use App\Models\PayPhone;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Admin\PayPhonesExport;
class PayPhoneController extends Controller
{
    public function index(Request $request)
    {
        $query = PayPhone::query();

        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request->input('phone') . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }


        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->input('start_date') . ' 00:00:00',
                $request->input('end_date') . ' 23:59:59'
            ]);
        }

        $pay_phones = $query->latest()->paginate(20);
        $pay_phone = new \stdClass();
        $pay_phone->total = $query->count();

        return view('admin.pay-phone.index', compact('pay_phones', 'pay_phone'));
    }
    public function export(Request $request)
    {
        return Excel::download(new PayPhonesExport($request), 'pay_phones.xlsx');
    }
    public function create()
    {
        return view('admin.pay-phone.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:30',
            'amount' => 'required|numeric|min:0'
        ]);

        $py_phone = PayPhone::create([
            'phone' => $request->phone,
            'amount' => $request->amount,
        ]);

        $py_phone->redirect_url =  'https://aseshop.az/payment/pay/' . $py_phone->id;
        $py_phone->save();

        $sms = new SMS();

        $data = [
            'web_site' => 'https://aseshop.az/payment/pay/' . $py_phone->id
        ];

        $sms->sendByNumber($request->phone, $data,'payment_link');


        return redirect()->route('pay-phone.index')->with('success', 'Ödəniş əlavə olundu.');
    }

    public function paymentLink(Request $request,$id)
    {
        $pay_phone = PayPhone::where('id', $id)->withTrashed()->first();

        $price = $pay_phone->amount;

        $body = [
            'order' => [
                'typeRid' => 'Order_SMS',
                'amount' => number_format(($price), 2, ".", ""),
                'currency' => 'AZN',
                'language' => 'en',
                'description' => 'AseShop',
                'hppRedirectUrl' => 'https://aseshop.az/kapital-bank/callback',
                'hppCofCapturePurposes' => ['Cit']
            ]
        ];
        $kapitalBankTxpgService = new KapitalBankTxpgService();

        $kapitalResponse = $kapitalBankTxpgService->createOrder($body);
        $OrderID = $kapitalResponse['order_id'];
        $password = $kapitalResponse['password'];
        $redirectUrl = $kapitalResponse['redirectUrl'];

//        Transaction::create([
//            'user_id' => $courierDelivery->user_id,
//            'custom_id' => $courierDelivery->id,
//            'paid_by' => 'KAPITAL',
//            'amount' => $price,
//            'source_id' => $OrderID,
//            'type' => 'PENDING',
//            'paid_for' => 'COURIER_DELIVERY',
//        ]);

        return redirect($redirectUrl);
    }

    public function edit($id)
    {
        $pay_phone = PayPhone::findOrFail($id);
        return view('admin.pay-phone.edit', compact('pay_phone'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,success,failed',
        ]);

        $pay_phone = PayPhone::findOrFail($id);
        $pay_phone->update([
            'phone' => $request->phone,
            'amount' => $request->amount,
            'status' => $request->status,
        ]);

        return redirect()->route('pay-phone.index')->with('success', 'Ödəniş məlumatı yeniləndi.');
    }


    public function destroy($id)
    {
        $payPhone = PayPhone::findOrFail($id);
        $payPhone->delete();

        return redirect()->route('pay-phone.index')->with('success', 'Ödəniş silindi.');
    }
}
