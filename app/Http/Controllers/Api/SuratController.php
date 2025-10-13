<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\HandleAzeriexpressCourierStatusUpdateJob;
use App\Jobs\HandleAzeriexpressPudoStatusUpdateJob;
use App\Jobs\HandleSuratStatusUpdateJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;
use Milon\Barcode\DNS1D;

class SuratController extends Controller
{
    public function updateStatus(Request $request)
    {
        Log::channel('surat')->info("StatusLog: ", [$request->all()]);
        $request->validate([
            'data' => ['required', 'array'],
            'data.*.tracking_code' => ['required'],
            'data.*.status' => ['required', 'in:2,4,5']
        ]);

        $data = $request->input('data');
        $trackings = $request->input('tracking_code');
        $statuses = $request->input('status');

//        HandleSuratStatusUpdateJob::dispatch($tracking, $status);
        foreach ($data as $item) {
            $ignore = DB::table('tracks_ignore_list')->where('tracking_code', $item['tracking_code'])->first();
            if($ignore) {
                continue;
            }
            $this->dispatch(new HandleSuratStatusUpdateJob($item['tracking_code'], $item['status']));
        }

        return response()->json(['status' => true, 'data' => []]);
    }

    public function updateCourierStatus(Request $request)
    {
        $request->validate([
            'track_number' => ['required'],
            'fedex' => ['required', 'in:3,5,6,7,8'],
            'cause_id' => ['nullable']
        ]);

        $tracking = $request->input('track_number');
        $status = $request->input('fedex');
        $causeId = $request->input('cause_id');

        HandleAzeriexpressCourierStatusUpdateJob::dispatch($tracking, $status, $causeId);

        return response()->json(['status' => true, 'data' => []]);
    }
}
