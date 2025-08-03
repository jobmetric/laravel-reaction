<?php

namespace JobMetric\Reaction\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as BuilderQuery;
use JobMetric\Reaction\Exceptions\InvalidReactionSourceException;

/**
 * Class Reaction
 *
 * Represents a user's reaction (like, dislike, etc.) on any model.
 *
 * @package JobMetric\Reaction
 *
 * @property int $id
 * @property string|null $liker_type The class name of the reacting user (polymorphic).
 * @property int|null $liker_id The ID of the reacting user (polymorphic).
 * @property string $reactable_type The class name of the target model being reacted to.
 * @property int $reactable_id The ID of the target model being reacted to.
 * @property string $reaction The type of reaction (e.g. like, dislike, love, etc.).
 * @property string|null $ip The IP address from which the reaction was made.
 * @property string|null $device_id Optional device identifier.
 * @property string|null $source Optional source indicator (e.g. app, web, etc.).
 * @property Carbon|null $deleted_at Soft delete timestamp.
 * @property Carbon|null $created_at Timestamp of creation.
 * @property Carbon|null $updated_at Timestamp of last update.
 *
 * @property-read Model $liker The user or entity who made the reaction.
 * @property-read Model $reactable The model that was reacted to.
 *
 * @method static Builder|Reaction whereLikerType($value)
 * @method static Builder|Reaction whereLikerId($value)
 * @method static Builder|Reaction whereReactableType($value)
 * @method static Builder|Reaction whereReactableId($value)
 * @method static Builder|Reaction whereReaction($value)
 * @method static Builder|Reaction whereIp($value)
 * @method static Builder|Reaction whereDeviceId($value)
 * @method static Builder|Reaction whereSource($value)
 * @method static BuilderQuery|Reaction onlyTrashed()
 * @method static BuilderQuery|Reaction withTrashed()
 * @method static BuilderQuery|Reaction withoutTrashed()
 */
class Reaction extends Model
{
    use HasFactory, SoftDeletes, Prunable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'liker_type',
        'liker_id',
        'reactable_type',
        'reactable_id',
        'reaction',
        'ip',
        'device_id',
        'source',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'liker_type' => 'string',
        'liker_id' => 'integer',
        'reactable_type' => 'string',
        'reactable_id' => 'integer',
        'reaction' => 'string',
        'ip' => 'string',
        'device_id' => 'string',
        'source' => 'string',
    ];

    /**
     * Override the table name using config.
     *
     * @return string
     */
    public function getTable(): string
    {
        return config('reaction.tables.reaction', parent::getTable());
    }

    /**
     * Boot the model and set up event listeners.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function (Reaction $reaction) {
            $hasLiker = filled($reaction->liker_id) && filled($reaction->liker_type);
            $hasDevice = filled($reaction->device_id);

            if (!$hasLiker && !$hasDevice) {
                throw new InvalidReactionSourceException;
            }
        });
    }

    /**
     * Prune old reactions.
     *
     * @return Builder
     */
    public function prunable(): Builder
    {
        return static::where('deleted_at', '<=', now()->subDays(config('reaction.prune_days', 30)));
    }

    /**
     * Get the parent liker model (morph-to relation).
     *
     * @return MorphTo
     */
    public function liker(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the parent reactable model (morph-to relation).
     *
     * @return MorphTo
     */
    public function reactable(): MorphTo
    {
        return $this->morphTo();
    }
}
