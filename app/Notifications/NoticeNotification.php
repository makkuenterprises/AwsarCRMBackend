<?php

namespace App\Notifications;

use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NoticeNotification extends Notification
{
   use Queueable;

    protected $notice;

    /**
     * Create a new notification instance.
     *
     * @param  mixed  $notice
     * @return void
     */
    public function __construct($notice)
    {
        $this->notice = $notice;
        Log::info('notice notification created', ['notice' => $notice]);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        Log::info('Notification via method called', ['notifiable' => $notifiable]);
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
        Log::info('Notification toArray method called', ['notifiable' => $notifiable]);

        return [
            'material_id' => $this->notice->id,
            'material_title' => $this->notice->title,
            'created_at' => $this->notice->created_at,
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
        Log::info('Notification toBroadcast method called', ['notifiable' => $notifiable]);

        return new BroadcastMessage([
            'material_id' => $this->notice->id,
            'material_title' => $this->notice->title,
            'material_from' => 'notice',
            'created_at' => $this->notice->created_at,
        ]);
    }
}
