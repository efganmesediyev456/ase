<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\ProcessCustomTrackAction;
use App\Models\Track;
use App\Services\Package\PackageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class BulkResendStatusController extends \App\Http\Controllers\Controller
{

    public function index()
    {
        return view('admin.bulk_resend.index');
    }

    public function store(Request $request)
    {

        $set_tracks = $request->requestText;

        if ($set_tracks) {
            $tracking_codes = preg_split("/[;:,\s]+/", trim($set_tracks));

            $tracks = Track::whereIn('tracking_code', $tracking_codes)->get();

            if ($tracks->isEmpty()) {
                return \Response::json(['message' => 'No tracks found for the provided codes']);
            }

            foreach ($tracks as $track) {
                if (!$track) {
                    return \Response::json(['message' => 'Track not found']);
                }
                 $response = (new PackageService())->updateStatus(
                    $track,
                     $track->status,
                    $request->has('date') ? $request->date : null
                );


            }
        }

        return back()->with('success', 'Əməliyyat arxa planda yerinə yetirilir. Nəticə bir neçə dəqiqə ərzində əks olunacaq.');
    }


}