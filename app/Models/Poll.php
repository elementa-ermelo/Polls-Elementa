<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Poll extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'question',
        'type',
        'status',
        'opens_at',
        'closes_at',
        'is_public',
        'access_code',
        'requires_confirmation',
        'anonymous',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
            'is_public' => 'boolean',
            'requires_confirmation' => 'boolean',
            'anonymous' => 'boolean',
        ];
    }

    /**
     * URL's gebruiken de slug (/polls/centrum-enquete) in plaats van het nummer.
     * Een slug wordt gemaakt uit de titel en verandert mee met de titel zolang
     * de poll nog niet live is geweest en er geen antwoorden zijn; daarna ligt
     * hij vast, zodat verstuurde links blijven werken.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Oude links met een nummer (/polls/2) blijven ook werken. */
    public function resolveRouteBinding($value, $field = null)
    {
        $poll = $this->where($field ?? 'slug', $value)->first();

        if (! $poll && ctype_digit((string) $value)) {
            $poll = $this->whereKey((int) $value)->first();
        }

        return $poll;
    }

    protected static function booted(): void
    {
        static::creating(function (Poll $poll) {
            if (empty($poll->slug)) {
                $poll->slug = static::uniqueSlug($poll->title);
            }
        });

        static::updating(function (Poll $poll) {
            if ($poll->isDirty('title') && ! $poll->getOriginal('is_public') && ! $poll->votes()->exists()) {
                $poll->slug = static::uniqueSlug($poll->title, $poll->id);
            }
        });
    }

    public static function uniqueSlug(?string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug((string) $title) ?: 'poll';
        if (ctype_digit($base)) {
            $base = 'poll-'.$base; // een kaal getal zou met een oud nummer-link botsen
        }

        $slug = $base;
        $i = 2;
        while (static::query()->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Mede-beheerders: mogen deze poll beheren alsof hij van hen is. */
    public function editors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'poll_editors')->withTimestamps();
    }

    /** Eigenaar of admin: mag ook de poll zelf verwijderen. */
    public function isOwnedBy(?User $user): bool
    {
        return $user !== null && ($user->is_admin || (int) $this->user_id === (int) $user->id);
    }

    /** Mag deze gebruiker de poll beheren? (admin, eigenaar of mede-beheerder) */
    public function isManagedBy(?User $user): bool
    {
        return $this->isOwnedBy($user)
            || ($user !== null && $this->editors()->whereKey($user->id)->exists());
    }

    /** Polls die deze gebruiker in het overzicht ziet. */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->is_admin) {
            return $query;
        }

        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
              ->orWhereHas('editors', fn ($e) => $e->whereKey($user->id));
        });
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
