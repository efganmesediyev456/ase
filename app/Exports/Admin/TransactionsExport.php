<?php

namespace App\Exports\Admin;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TransactionsExport implements FromView, ShouldAutoSize
{
    protected $items;


    public function __construct($items)
    {
        $this->items = $items;
    }

    public function view(): View
    {
        $transactions = $this->items;

        $types = ['TOTAL' => 0,'TOTAL_90' =>  0];

        foreach ($transactions as $transaction) {
            if (isset($types[$transaction->paid_by])) {
                $types[$transaction->paid_by] += $transaction->amount;
            } else {
                $types[$transaction->paid_by] = $transaction->amount;
            }

            $types['TOTAL'] += $transaction->amount * config('ase.attributes.transaction.by.' . $transaction->paid_by);
            $types['TOTAL_90'] += $transaction->amount_90 * config('ase.attributes.transaction.by.' . $transaction->paid_by);
        }
        return view('admin.exports.transactions', [
            'transactions' => $transactions,
            'types' => $types
        ]);
    }
}
