<?php

namespace App\Exports\Admin;

use App\Models\PayPhone;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Http\Request;

class PayPhonesExport implements FromView
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $query = PayPhone::query();

        if ($this->request->filled('phone')) {
            $query->where('phone', 'like', '%' . $this->request->input('phone') . '%');
        }

        if ($this->request->filled('status')) {
            $query->where('status', $this->request->input('status'));
        }

        if ($this->request->filled('start_date') && $this->request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $this->request->input('start_date') . ' 00:00:00',
                $this->request->input('end_date') . ' 23:59:59'
            ]);
        }

        return view('admin.pay-phone.export', [
            'pay_phones' => $query->latest()->get()
        ]);
    }
}
