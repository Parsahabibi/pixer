<?php

namespace Marvel\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Marvel\Database\Models\Participant;

class MessageReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public $participant;

    /**
     * Create a new notification instance.
     *
     * @param $participant
     * @return void
     */
    public function __construct($participant)
    {
        $this->participant = $participant;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $prefix = ($this->participant->type === 'user') ? 'message' : 'shop-message';
        $url = config('shop.dashboard_url') . '/'.$prefix.'/' . $this->participant->conversation_id;

        return (new MailMessage)
            ->markdown('emails.message.reminder', ['participant' => $this->participant, 'url' => $url]);
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
            //
        ];
    }
}
