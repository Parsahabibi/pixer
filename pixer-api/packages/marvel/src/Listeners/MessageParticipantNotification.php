<?php

namespace Marvel\Listeners;

use Marvel\Events\MessageSent;
use Marvel\Database\Models\Participant;

class MessageParticipantNotification
{
    /**
     * Handle the event.
     *
     * @param  MessageSent  $event
     * @return void
     */
    public function handle(MessageSent $event)
    {
        // set participant
        Participant::create([
            'type'              => $event->type,
            'conversation_id'   => $event->conversation->id,
            'shop_id'           => $event->conversation->shop->id,
            'user_id'           => $event->conversation->user_id,
            'message_id'        => $event->message->id,
        ]);
    }
}
