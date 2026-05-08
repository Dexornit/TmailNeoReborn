<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Email extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_DISABLED = 'disabled';

    public const STATUS_REVOKED = 'revoked';

    /**
     * `user_id` is intentionally NOT in $fillable to avoid mass-assignment
     * spoofing of ownership. Set it explicitly via assignment.
     */
    protected $fillable = [
        'username',
        'domain',
        'email',
        'status',
        'is_protected',
        'last_used_at',
    ];

    protected $casts = [
        'is_protected' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeForOwner(Builder $query, ?int $userId): Builder
    {
        if ($userId === null) {
            return $query->whereNull('user_id');
        }

        return $query->where('user_id', $userId);
    }

    public function isAccessible(): bool
    {
        return $this->status === self::STATUS_ACTIVE && $this->deleted_at === null;
    }

    public function touchLastUsed(): void
    {
        $this->forceFill(['last_used_at' => now()])->save();
    }
}
