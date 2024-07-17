<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CourseEnrollmentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $courseName;
    protected $enrollmentNo;

    /**
     * Create a new notification instance.
     *
     * @param string $courseName
     * @param string $enrollmentNo
     */
    public function __construct($courseName, $enrollmentNo, $created_at)
    {
        $this->courseName = $courseName;
        $this->enrollmentNo = $enrollmentNo;
        $this->created_at = $created_at;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject('You are enrolled in a new course')
                    ->line("Dear {$notifiable->name},")
                    ->line("You have successfully enrolled in the course: {$this->courseName}.")
                    ->line("Your enrollment number is: {$this->enrollmentNo}.")
                    ->action('Go to Dashboard', url('/dashboard'))
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
  public function toArray($notifiable)
    {
        Log::info('Notification toArray method called', ['notifiable' => $notifiable]);

        return [
            'material_id' => $this->enrollmentNo, 
            'material_title' => $this->courseName,
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
