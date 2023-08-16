<?php

namespace Marvel\Database\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Marvel\Traits\TranslationTrait;

class Coupon extends Model
{
    use SoftDeletes;
    use TranslationTrait;

    protected $table = 'coupons';

    public $guarded = [];

    // protected $appends = ['is_valid'];
    // TODO: use it latter
    protected $appends = ['is_valid', 'translated_languages'];

    protected $casts = [
        'image'   => 'json',
    ];

    protected static function boot()
    {
        parent::boot();
        // Order by updated_at desc
        static::addGlobalScope('order', function (Builder $builder) {
            $builder->orderBy('updated_at', 'desc');
        });
    }

    /**
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'coupon_id');
    }

    /**
     * @return bool
     */
    public function getIsValidAttribute()
    {
        return Carbon::now()->between($this->active_from, $this->expire_at);
    }
}
