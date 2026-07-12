<?php

namespace App\Models;

use App\Enums\BracketStatus;
use Database\Factories\BracketFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property int $size
 * @property BracketStatus $status
 * @property int $round_duration_hours
 * @property bool $is_unlisted
 * @property int|null $current_round
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'slug', 'description', 'size', 'status', 'round_duration_hours', 'is_unlisted', 'current_round', 'completed_at'])]
class Bracket extends Model
{
    /** @use HasFactory<BracketFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => BracketStatus::class,
            'is_unlisted' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    /** @return HasMany<Contestant, $this> */
    public function contestants(): HasMany
    {
        return $this->hasMany(Contestant::class)->orderBy('seed');
    }

    /** @return HasMany<Matchup, $this> */
    public function matchups(): HasMany
    {
        return $this->hasMany(Matchup::class)->orderBy('round')->orderBy('position');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function totalRounds(): int
    {
        return (int) log($this->size, 2);
    }
}
