<?php

namespace App\Events;

use App\Models\Track;
use Auth;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Pusher\Pusher;

class TrackCell
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public function __construct($type = 'find', $id = 0)
    {
        $track = Track::find($id);
        $city = null;
        if ($track && $track->city_id) $city = $track->city_id;
        else if ($track && $track->customer && $track->customer->city_id) $city = $track->customer->city_id;
        $admin = Auth::guard('admin')->user();

        $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), ['cluster' => 'eu']);

        $trigger = ['package' => route('cells.index') . "?requested=1", 'city_id' => $city, 'track' => 1, 'status' => $admin->store_status];
        if ($type == 'done') {
            $trigger = ['success' => "The package is done. Good job!", 'city_id' => $city, 'track' => 1, 'status' => $admin->store_status];
        }

        $pusher->trigger('my-channel', 'my-event', $trigger);
    }
}
