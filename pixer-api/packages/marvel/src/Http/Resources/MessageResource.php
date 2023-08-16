<?php

namespace Marvel\Http\Resources;

use Illuminate\Http\Request;

class MessageResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'conversation_id' => $this->conversation_id,
            'conversation' => $this->when($this->needToInclude($request, 'message.conversation'), function () {
                return new ConversationResource($this->conversation);
            }),
            'user_id' => $this->user_id,
            'user' => $this->when($this->needToInclude($request, 'message.user'), function () {
                return new UserResource($this->user);
            }),
            'body' => $this->body,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
