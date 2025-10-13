<?php

namespace App\Exports;

use App\Models\Package;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ParcelExport implements FromView, ShouldAutoSize
{
    public function view(): View
    {
        $warehouse = auth()->guard('worker')->user()->warehouse;
        $packages = Package::whereIn('id', request()->get('items'))->get();

        return view('warehouse.exports.packages', [
            'packages' => $packages,
            'warehouse' => $warehouse
        ]);
    }
}
