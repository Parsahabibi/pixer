<?php

namespace Marvel\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Marvel\Traits\TranslationTrait;

class PaymentGateway extends Model
{
    use SoftDeletes;
    use TranslationTrait;

    protected $table = 'payment_gateways';

    public $guarded = [];

    /**
     * @return HasMany
     */
    public function payment_methods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * @return BelongsTo
     */
    public function users(): BelongsTo
    {
        return $this->BelongsTo(User::class, 'user_id');
    }
}
