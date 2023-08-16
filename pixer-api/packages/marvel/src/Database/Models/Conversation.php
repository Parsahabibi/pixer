<?php

namespace Marvel\Database\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;

class Conversation extends Model
{

    public $guarded = [];

    protected $appends = [
        'latest_message',
        'unseen',
    ];

    /**
     * @return belongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return belongsTo
     */
    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    /**
     * @return hasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    /**
     * Returns the latest message from a conversation.
     *
     * @return Message
     */
    public function getLatestMessageAttribute()
    {
        return $this->messages()->latest()->first();
    }

    /**
     * Get all of the conversations participants.
     */
    public function participants()
    {
        return $this->hasMany(Participant::class, 'conversation_id');
    }

    public function getUnseenAttribute()
    {
        $instance = $this->participants()->whereNull('last_read')->where('user_id', auth()->user()->id)->where('type', 'user')->count();

        if( 0 == $instance) {
            $instance = $this->participants()->whereNull('last_read')->whereIn('shop_id', auth()->user()->shops()->pluck('id'))->where('type', 'shop')->count();
        }

        return $instance;
    }

}
