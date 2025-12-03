<?php

namespace App\Exports\Admin;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class CdsExport implements FromView, ShouldAutoSize
{
    protected $items;


    public function __construct($items)
    {
        $this->items = $items->map(function ($item) {
            foreach ($item->getAttributes() as $key => $value) {
                if (is_string($value)) {
                    $clean = preg_replace('/[\x00-\x1F\x7F]/u', '', $value);
                    $item->$key = $clean;
                }
            }
            return $item;
        });
    }

    public function view(): View
    {
        $cds = $this->items;

        return view('admin.exports.cds', [
            'cds' => $cds
        ]);
    }
}
