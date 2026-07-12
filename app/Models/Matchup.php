<?php

namespace App\Models;

use Database\Factories\MatchupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $bracket_id
 * @property int $round
 * @property int $position
 * @property int|null $contestant_one_id
 * @property int|null $contestant_two_id
 * @property int|null $winner_id
 * @property bool $decided_by_coin_flip
 * @property Carbon|null $opens_at
 * @property Carbon|null $closes_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['round', 'position', 'contestant_one_id', 'contestant_two_id', 'winner_id', 'decided_by_coin_flip', 'opens_at', 'closes_at'])]
class Matchup extends Model
{
    /** @use HasFactory<MatchupFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'decided_by_coin_flip' => 'boolean',
            'opens_at' => 'datetime',
            'closes_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Bracket, $this> */
    public function bracket(): BelongsTo
    {
        return $this->belongsTo(Bracket::class);
    }

    /** @return BelongsTo<Contestant, $this> */
    public function contestantOne(): BelongsTo
    {
        return $this->belongsTo(Contestant::class, 'contestant_one_id');
    }

    /** @return BelongsTo<Contestant, $this> */
    public function contestantTwo(): BelongsTo
    {
        return $this->belongsTo(Contestant::class, 'contestant_two_id');
    }

    /** @return HasMany<Vote, $this> */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /** @return BelongsTo<Contestant, $this> */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Contestant::class, 'winner_id');
    }

    public function isOpen(): bool
    {
        return $this->winner_id === null
            && $this->opens_at !== null
            && $this->closes_at !== null
            && $this->opens_at->lte(now())
            && $this->closes_at->gt(now());
    }
}
