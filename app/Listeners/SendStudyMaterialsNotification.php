<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendStudyMaterialsNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        //
        $Students = Students::whereHas('roles', function ($query) {
                $query->where('id', 1);
            })->get();

        Notification::send($Students, new StudyMaterial($event->user));
    }
}
 