<?php

namespace Marvel\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Marvel\Database\Models\Order;
use Marvel\Traits\TranslationTrait;

class PaymentIntent extends Model
{
    use SoftDeletes;
    use TranslationTrait;


    protected $table = 'payment_intents';

    public $guarded = [];

    protected $casts = [
        'payment_intent_info' => 'json',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * @return belongsTo
     */
    public function order(): belongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
