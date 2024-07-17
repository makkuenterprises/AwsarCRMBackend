<?php

namespace App\Notifications;


use Illuminate\Support\Facades\Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification; 
use Illuminate\Notifications\Messages\BroadcastMessage;

class CourseEnrollmentNotificationForAdmin extends Notification
{
    use Queueable; 

    protected $courseName;
    protected $enrollmentNo;
    protected $created_at;
    protected $name; 

    /**
     * Create a new notification instance.
     *
     * @param  mixed  $notice
     * @return void
     */
     public function __construct($courseName, $enrollmentNo, $created_at, $name)
    {
        $this->courseName = $courseName;
        $this->enrollmentNo = $enrollmentNo;
        $this->created_at = $created_at;
        $this->name = $name;
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
            'material_id' => $this->enrollmentNo, 
            'material_title' => $this->name . 'has been enrolled in ' . $this->courseName . 'Batch.',
            'material_from' => 'Course_Enrollment',
            'created_at' => $this->created_at,
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
            'material_id' => $this->enrollmentNo, 
            'material_title' => $this->courseName,
            'material_from' => 'Course_Enrollment',
            'created_at' => $this->created_at,
        ]);
    }
}
