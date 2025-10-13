<?php

namespace App\Exports\Admin\Reports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReportsExport implements FromView, ShouldAutoSize
{
    protected $items;
    private $view;


    public function __construct($items, $view)
    {
        $this->items = $items;
        $this->view = $view;
    }

    public function view(): View
    {
        $packages = $this->items;

        return view('admin.exports.reports.' . $this->view, [
            'packages' => $packages
        ]);
    }
}
