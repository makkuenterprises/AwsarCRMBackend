<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class StudyMaterial extends Notification
{
  use Queueable;

    protected $studyMaterial;

    /**
     * Create a new notification instance.
     *
     * @param  mixed  $studyMaterial
     * @return void
     */
    public function __construct($studyMaterial)
    {
        $this->studyMaterial = $studyMaterial;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'material_id' => $this->studyMaterial->id,
            'material_title' => $this->studyMaterial->title,
            'created_at' => $this->studyMaterial->created_at,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     * 
     * @param  mixed  $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'material_id' => $this->studyMaterial->id,
            'material_title' => $this->studyMaterial->title,
            'created_at' => $this->studyMaterial->created_at,
        ]);
    }
}
