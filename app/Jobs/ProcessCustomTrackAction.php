<?php
namespace App\Jobs;

use App\Models\Track;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Artisan;

class ProcessCustomTrackAction implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $trackId;
    protected $type;

    public function __construct($trackId, $type)
    {
        $this->trackId = $trackId;
        $this->type = $type;
    }

    public function handle()
    {
        $track = Track::find($this->trackId);

        if (!$track) return;

        $params = [
            'package' => 1,
            'track_id' => $track->id,
            'checkonly' => 0,
            'htmlformat' => 0
        ];

        if ($this->type === 'delete') {
            $params['deleteonly'] = 1;
        }

        Artisan::call('carriers_track:update', $params);

        sleep(1);
    }
}
