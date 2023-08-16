<?php

namespace Marvel\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use Marvel\Enums\Permission;

class StoreNotice extends Model
{
    use SoftDeletes;

    public $guarded = [];
    public $with = ['creator', 'users', 'shops', 'read_status'];
    protected $table = 'store_notices';
    protected $appends = [
        'is_read', 'creator_role'
    ];

    /**
     * parent boot menu from parent model
     *
     * @return void
     */
    public static function boot(): void
    {
        Parent::boot();
        static::creating(function ($storeNotice) {
            $storeNotice->created_by = Auth::id();
        });
        static::updating(function ($storeNotice) {
            $storeNotice->updated_by = Auth::id();
        });
    }

    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    /**
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * @return BelongsToMany
     */
    public function read_status(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_notice_read')->withPivot('is_read');
    }

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'store_notice_user');
    }

    /**
     * @return BelongsToMany
     */
    public function shops(): BelongsToMany
    {
        return $this->belongsToMany(Shop::class, 'store_notice_shop');
    }

    /**
     * @return string
     */
    public function getCreatorRoleAttribute(): string
    {
        $permissionArr = $this->creator->permissions->pluck('name')->toArray();
        if (in_array(Permission::SUPER_ADMIN, $permissionArr)) {
            return ucfirst(str_replace('_', ' ', Permission::SUPER_ADMIN));
        }
        return ucfirst(str_replace('_', ' ', Permission::STORE_OWNER));
    }

    /**
     * @return bool
     */
    public function getIsReadAttribute(): bool
    {
        $readStatusArr = $this->read_status;
        foreach ($readStatusArr as $readStatus) {
            if ($readStatus->id === Auth::id() && $readStatus->pivot->is_read) {
                return true;
            }
        }
        return false;
    }
}
