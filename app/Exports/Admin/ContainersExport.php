<?php

namespace App\Exports\Admin;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ContainersExport implements FromView, ShouldAutoSize
{
    protected $items;


    public function __construct($items)
    {
        $this->items = $items;
    }

    public function view(): View
    {
        $items = $this->items;
        return view('admin.exports.containers', [
            'items' => $items
        ]);
    }
}
