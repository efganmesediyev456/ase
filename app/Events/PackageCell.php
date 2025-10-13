<?php

namespace App\Events;

use App\Models\Package;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Pusher\Pusher;
use Auth;

class PackageCell
{
    use Dispatchable, InteractsWithSockets, SerializesModels;


    public function __construct($type = 'find', $id = 0)
    {
        $package = Package::find($id);
        $city = $package && $package->user ? $package->user->city_id : null;
	$admin = Auth::guard('admin')->user();

        $pusher = new Pusher(env('PUSHER_APP_KEY'), env('PUSHER_APP_SECRET'), env('PUSHER_APP_ID'), ['cluster' => 'eu']);

        //$trigger = ['package' => route('cells.index') . "?requested=1", 'city_id' => $city,'track'=>0, 'status'=>$package->status];
        $trigger = ['package' => route('cells.index') . "?requested=1", 'city_id' => $city,'track'=>0, 'status'=>$admin->store_status];
        if ($type == 'done') {
            //$trigger = ['success' => "The package is done. Good job!", 'city_id' => $city,'track'=>0, 'status'=>$package->status];
            $trigger = ['success' => "The package is done. Good job!", 'city_id' => $city,'track'=>0, 'status'=>$admin->store_status];
        }

        $pusher->trigger('my-channel', 'my-event', $trigger);
    }
}
