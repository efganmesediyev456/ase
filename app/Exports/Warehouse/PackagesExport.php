<?php

namespace App\Exports\Warehouse;

use App\Models\Package;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class PackagesExport implements FromView, ShouldAutoSize
{
    protected $items;
    protected $bags;


    public function __construct($items, $bags = null)
    {
        $this->items = $items;
        $this->bags = $bags;
    }

    public function view(): View
    {
        $warehouse = auth()->guard('worker')->user()->warehouse;

        if (is_array($this->items) and !empty($this->items)) {
            $packages = Package::whereIn('id', $this->items)->where('warehouse_id', $warehouse->id)->get();
        } else {
            $packages = $this->items;
        }

        $bags = $this->bags;

        return view('warehouse.exports.packages', [
            'packages' => $packages,
            'warehouse' => $warehouse,
            'bags' => $bags
        ]);
    }
}
