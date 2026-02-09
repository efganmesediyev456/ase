<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\HandleAzeriexpressCourierStatusUpdateJob;
use App\Jobs\HandleAzeriexpressPudoStatusUpdateJob4;
use App\Jobs\HandleAzeriexpressPudoStatusUpdateJob5;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;
use Milon\Barcode\DNS1D;

class AzeriExpressController extends Controller
{
    public function updateStatus(Request $request)
    {
        Log::channel('azeriexpress')->info("StatusLog: ", [$request->all()]);
        $request->validate([
            'track_number' => ['required'],
            'fedex' => ['required', 'in:2,4,5']
        ]);

        $tracking = $request->input('track_number');
        $status = $request->input('fedex');

        $ignore = DB::table('tracks_ignore_list')->where('tracking_code', $tracking)->first();
        if($ignore) {
            return response()->json(['status' => true, 'data' => []]);
        }
        HandleAzeriexpressPudoStatusUpdateJob5::dispatch($tracking, $status);

        return response()->json(['status' => true, 'data' => []]);
    }

    public function updateCourierStatus(Request $request)
    {
        $request->validate([
            'track_number' => ['required'],
            'fedex' => ['required', 'in:2,3,5,6,7,8'],
            'cause_id' => ['nullable']
        ]);

        $tracking = $request->input('track_number');
        $status = $request->input('fedex');
        $causeId = $request->input('cause_id');

        HandleAzeriexpressCourierStatusUpdateJob::dispatch($tracking, $status, $causeId);

        return response()->json(['status' => true, 'data' => []]);
    }
}
