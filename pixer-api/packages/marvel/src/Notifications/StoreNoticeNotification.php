<?php

namespace Marvel\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Marvel\Database\Models\StoreNotice;
use Marvel\Enums\Permission;

class StoreNoticeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $storeNotice;
    protected $action;

    /**
     * Create a new notification instance.
     * @param \Marvel\Database\Models\StoreNotice $storeNotice
     * @param null|string $action
     * @return void
     */
    public function __construct(StoreNotice $storeNotice, ?string $action)
    {
        $this->storeNotice = $storeNotice;
        $this->action = $action;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        if ($this->storeNotice->creator->hasPermissionTo(Permission::SUPER_ADMIN)) {
            $role = "Admin";
        } else {
            $role = "Shop Owner";
        }

        return (new MailMessage)
            ->subject('Notice From ' . $role . '.')
            ->markdown('emails.storeNotice.storeNotice', [
                'notice' => $this->storeNotice,
                'action' => $this->action,
                'role'   => $role,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
