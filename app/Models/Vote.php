<?php

namespace App\Models;

use Database\Factories\VoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $matchup_id
 * @property int $contestant_id
 * @property string $voter_hash
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['matchup_id', 'contestant_id', 'voter_hash', 'user_id'])]
class Vote extends Model
{
    /** @use HasFactory<VoteFactory> */
    use HasFactory;

    /** @return BelongsTo<Contestant, $this> */
    public function contestant(): BelongsTo
    {
        return $this->belongsTo(Contestant::class);
    }

    /** @return BelongsTo<Matchup, $this> */
    public function matchup(): BelongsTo
    {
        return $this->belongsTo(Matchup::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
