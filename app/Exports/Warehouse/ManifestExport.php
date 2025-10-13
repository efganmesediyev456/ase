<?php

namespace App\Exports\Warehouse;

use App\Models\Package;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ManifestExport implements FromView, ShouldAutoSize
{
    protected $items;
    protected $warehouse;
    protected $ext;


    public function __construct($items, $warehouse = null, $ext = 'Xlsx')
    {
        $this->items = $items;
        $this->ext = $ext;
        $this->warehouse = $warehouse ?: auth()->guard('worker')->user()->warehouse;
    }

    public function view(): View
    {
        $warehouse = $this->warehouse;

        if (is_array($this->items) and !empty($this->items)) {
            $packages = Package::whereIn('id', $this->items)->where('warehouse_id', $warehouse->id)->get();
        } else {
            $packages = $this->items;
        }

        return view('warehouse.exports.manifest.' . strtolower($warehouse->country->code), [
            'packages' => $packages,
            'warehouse' => $warehouse,
            'ext' => $this->ext,
            'span' => $this->ext == 'Xlsx' ? 11 : 10,
        ]);
    }

}
