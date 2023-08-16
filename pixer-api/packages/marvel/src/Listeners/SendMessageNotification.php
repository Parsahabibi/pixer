<?php

namespace Marvel\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Marvel\Database\Models\Participant;
use Marvel\Database\Models\Shop;
use Marvel\Database\Models\User;
use Marvel\Events\MessageSent;
use Marvel\Notifications\MessageReminder;

class SendMessageNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 900;

    /**
     * Handle the event.
     *
     * @param  MessageSent  $event
     * @return void
     */
    public function handle(MessageSent $event)
    {
        $participant = Participant::where('message_id', $event->message->id)->first();

        if(null === $participant->last_read) {
            if (0 === $participant->notify) {
                if('user' == $event->type) {
                    $user = User::findOrFail($event->conversation->user_id);
                    $notification = isset($user->profile->notifications) ?$user->profile->notifications : null;
                    if(empty($notification)) {
                        $notification['enable'] = 1;
                        $notification['email'] = $user->email;
                    }
                } else {
                    $shop = Shop::findOrFail($event->conversation->shop_id);
                    $notification = json_decode($shop->notifications, true);
                    if(empty($notification)) {
                        $notification['enable'] = 1;
                        $notification['email'] = $shop->owner->email;
                    }
                }
                if(1 == $notification["enable"]) {
                    Notification::route('mail', [
                        $notification["email"],
                    ])->notify(new MessageReminder($participant));

                    $participant->notify = 1;
                    $participant->save();
                }

            }
        }


    }
}
