<?php

namespace App\Http\Controllers\Admin;
use App\Exports\Admin\DeliveryExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Illuminate\Support\Str;
class ExportDeliveryDateController extends \App\Http\Controllers\Controller
{
    public function index()
    {
        return view('admin.export_delivery_date.index');
    }


    public function exportDeliveryInfo(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|file',
            'type' => 'nullable|in:hd,pudo',
        ]);


        $collection = Excel::toCollection(null, $request->file('file'))[0];


        $trackingCodes = $collection->pluck(0)
            ->filter(function ($code) {
                return !empty($code);
            })
            ->map(function ($code) {
                return (string)$code;
            })
            ->unique()
            ->values();

        if ($trackingCodes->isEmpty()) {
            return back()->withErrors('Excel faylında heç bir tracking code tapılmadı.');
        }


        $status = null;
        if ($request->filled('type')) {
            $status = $request->type === 'hd' ? 17 : 16;
        }


        $orderCodes = $trackingCodes->map(function ($code) {
            return DB::getPdo()->quote($code);
        })->implode(',');


        $results = DB::table('tracks')
            ->select(
                'tracks.tracking_code',
                DB::raw('MAX(activities.created_at) as delivery_date')
            )
            ->leftJoin('activities', function ($join) use ($status) {
                $join->on('tracks.id', '=', 'activities.content_id')
                    ->where('activities.content_type', '=', 'App\\Models\\Track');

                if (!is_null($status)) {

                    $join->where(function ($query) use ($status) {
                        $query->where(DB::raw("JSON_EXTRACT(activities.details, '$.status')"), '=', $status)
                            ->orWhere(DB::raw("JSON_EXTRACT(activities.details, '$.status')"), '=', (string)$status);
                    });
                } else {
                    $status = 17;

                    $join->where(function ($query) use ($status) {
                        $query->where(DB::raw("JSON_EXTRACT(activities.details, '$.status')"), '=', $status);
                        $query->orwhere(DB::raw("CAST(JSON_EXTRACT(activities.details, '$.status') AS UNSIGNED)"), '=', $status);
                    });
                }
            })
            ->whereIn('tracks.tracking_code', $trackingCodes)
            ->groupBy('tracks.tracking_code')
            ->orderByRaw("FIELD(tracks.tracking_code, $orderCodes)")
            ->get();


        $exportData = collect([['Tracking Code', 'Delivery Date']]);
        foreach ($results as $row) {
            $exportData->push([$row->tracking_code, $row->delivery_date]);
        }

        $filename = 'export-' . ($request->type ? $request->type : 'all') . '-' . date('Ymd_His') . '.xlsx';

        return Excel::download(new DeliveryExport($exportData), $filename);
    }

}