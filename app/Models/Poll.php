<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'question',
        'type',
        'status',
        'opens_at',
        'closes_at',
        'is_public',
        'access_code',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'is_public' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(PollQuestion::class)->orderBy('position');
    }

    public function options(): HasMany
    {
        return $this->hasMany(PollOption::class)->orderBy('position');
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function unconfirmedVotes(): HasMany
    {
        return $this->hasMany(Vote::class)->whereNull('confirmed_at');
    }

    public function confirmedVotes(): HasMany
    {
        return $this->hasMany(Vote::class)->whereNotNull('confirmed_at');
    }

    public function isOpen(): bool
    {
        $now = now();

        if ($this->status !== 'active') {
            return false;
        }

        if ($this->opens_at && $now->lt($this->opens_at)) {
            return false;
        }

        if ($this->closes_at && $now->gt($this->closes_at)) {
            return false;
        }

        return true;
    }

    public static function archiveExpiredPolls(): int
    {
        return self::query()
            ->where('status', 'active')
            ->whereNotNull('closes_at')
            ->where('closes_at', '<', now())
            ->update([
                'status' => 'archived',
                'is_public' => false,
            ]);
    }
}
