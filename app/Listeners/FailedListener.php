<?php

namespace App\Listeners;

class FailedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        if (env('APP_ENV') != 'local') {
            file_put_contents('/var/log/ase_login.log', date('Y-m-d H:i:s') . " FAILED " . json_encode($event->credentials) . " \n", FILE_APPEND);
        }
    }
}
