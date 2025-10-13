<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\ProcessCustomTrackAction;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class BulkCustomController extends \App\Http\Controllers\Controller
{

    public function index()
    {
        return view('admin.bulk_custom.index');
    }


    public function store(Request $request)
    {
        $set_tracks = $request->requestText;
        $type = $request->action_type;
        $results = [];

        if ($set_tracks) {
            $tracking_codes = preg_split("/[;:,\s]+/", trim($set_tracks));
            $tracks = Track::whereIn('tracking_code', $tracking_codes)->get();

            foreach ($tracks as $track) {
                if ($type === 'check') {

                    Artisan::call('depesh', [
                        'package' => 5,
                        'parcel_id' => $track->id,
                        'checkonly' => 1,
                        'htmlformat' => 1,
                        'user_id' => auth()->guard('admin')->user()->id
                    ]);

                    $results[] = [
                        'track_code' => $track->tracking_code,
                        'output' => Artisan::output()
                    ];
                    sleep(1);
                }
                if (in_array($type, ['reset', 'delete'])) {
                    ProcessCustomTrackAction::dispatch($track->id, $type);
                }
            }
        }

        if ($type === 'check') {
            return view('admin.bulk_custom.check-custom', compact('results'));
        }

        return back()->with('success', 'Əməliyyat arxa planda yerinə yetirilir. Nəticə bir neçə dəqiqə ərzində əks olunacaq.');

    }


}