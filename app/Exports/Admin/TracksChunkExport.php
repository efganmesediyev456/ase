<?php

namespace App\Exports\Admin;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;

class TracksChunkExport implements FromView, ShouldAutoSize
{
    protected $itemIds;

    public function __construct($itemIds)
    {
        $this->itemIds = $itemIds;
    }

    public function view(): View
    {
        $tracks = collect([]);

        // ID-ləri 1000-lik hissələrə bölüb sorğu edirik
        foreach (array_chunk($this->itemIds, 1000) as $chunk) {
            $chunkTracks = DB::table('tracks')
                ->whereIn('courier_delivery_id', $chunk)
                ->get();

            $tracks = $tracks->merge($chunkTracks);
        }

        return view('admin.exports.tracks', [
            'tracks' => $tracks
        ]);
    }
}