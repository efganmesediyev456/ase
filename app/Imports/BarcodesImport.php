<?php

namespace App\Imports;

use App\Jobs\ProcessBarcodesJob;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use Log;

class BarcodesImport implements ToCollection, WithHeadingRow
{
    /**
     * Handle each row of the collection.
     */
    public function collection(Collection $rows)
    {
        $rows->shift();
        $data = [];
        foreach ($rows as $row) {
            $data[] =  [
                'channel' => 'job',
                'level' => 'debug',
                'message' => "Import worked",
                'context' => json_encode($row),
            ];
            ProcessBarcodesJob::dispatch($row->toArray());
        }
        \App\Models\Log::insert($data);
    }
}
