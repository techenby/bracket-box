<?php

namespace App\Models;

use Database\Factories\ContestantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $bracket_id
 * @property string $name
 * @property string|null $image_path
 * @property int $seed
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'image_path', 'seed'])]
class Contestant extends Model
{
    /** @use HasFactory<ContestantFactory> */
    use HasFactory;

    /** @return BelongsTo<Bracket, $this> */
    public function bracket(): BelongsTo
    {
        return $this->belongsTo(Bracket::class);
    }

    /** @return HasMany<Vote, $this> */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path
            ? Storage::disk(config('filesystems.contestants_disk'))
                ->url($this->image_path)
            : null;
    }
}
