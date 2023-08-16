<?php

namespace Marvel\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Marvel\Database\Models\Conversation;
use Marvel\Database\Models\Message;
use Marvel\Database\Models\Review;

class MessageSent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public $conversation;

    public $type;

    /**
     * Create a new event instance.
     *
     * @param Message $message
     * @param Conversation $conversation
     * @param $type
     *
     */
    public function __construct(Message $message, Conversation $conversation, $type)
    {
        $this->message = $message;
        $this->conversation = $conversation;
        $this->type = $type;
    }

}
