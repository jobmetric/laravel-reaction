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
use JobMetric\Reaction\Events\ReactionRemovedEvent;
use JobMetric\Reaction\Events\ReactionRemovingEvent;
use JobMetric\Reaction\Exceptions\InvalidReactionSourceException;

/**
 * Class Reaction
 *
 * Represents a user's reaction (like, dislike, love, etc.) on any reactable model.
 * This model supports polymorphic relations for both the reacting entity (reactor) and
 * the target entity (reactable), enabling flexible usage across the application.
 *
 * Reactions can be identified either by authenticated users (`reactor_id`) or anonymous devices (`device_id`).
 * The IP address and source (e.g. 'web', 'app') are also recorded for audit or analytic purposes.
 *
 * @package JobMetric\Reaction
 *
 * @property int $id
 * @property string|null $reactor_type The class name of the reacting user (polymorphic).
 * @property int|null $reactor_id The ID of the reacting user (polymorphic).
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
 * @property-read Model|null $reactor The user or entity who made the reaction.
 * @property-read Model $reactable The model that was reacted to.
 *
 * @method static Builder|Reaction whereReactorType($value)
 * @method static Builder|Reaction whereReactorId($value)
 * @method static Builder|Reaction whereReactableType($value)
 * @method static Builder|Reaction whereReactableId($value)
 * @method static Builder|Reaction whereReaction($value)
 * @method static Builder|Reaction whereIp($value)
 * @method static Builder|Reaction whereDeviceId($value)
 * @method static Builder|Reaction whereSource($value)
 * @method static BuilderQuery|Reaction onlyTrashed()
 * @method static BuilderQuery|Reaction withTrashed()
 * @method static BuilderQuery|Reaction withoutTrashed()
 *
 * @method static Builder|Reaction ofReactor(Model $reactor) Scope to filter reactions by the given reactor model.
 * @method static Builder|Reaction ofReactable(Model $reactable) Scope to filter reactions by the given reactable model.
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
        'reactor_type',
        'reactor_id',
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
        'reactor_type' => 'string',
        'reactor_id' => 'integer',
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
            if (blank($reaction->ip)) {
                $reaction->ip = request()->ip();
            }

            if (blank($reaction->device_id)) {
                $reaction->device_id = request()->header(config('reaction.headers.device_id'));
            }

            if (blank($reaction->source)) {
                $reaction->source = request()->header(config('reaction.headers.source'), 'web');
            }

            $hasReactor = filled($reaction->reactor_type) && filled($reaction->reactor_id);
            $hasDevice = filled($reaction->device_id);

            if (!$hasReactor && !$hasDevice) {
                throw new InvalidReactionSourceException;
            }
        });

        static::deleting(function (Reaction $reaction) {
            event(new ReactionRemovingEvent($reaction));
        });

        static::deleted(function (Reaction $reaction) {
            event(new ReactionRemovedEvent($reaction));
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
     * Get the parent reactor model (morph-to relation).
     *
     * @return MorphTo
     */
    public function reactor(): MorphTo
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

    /**
     * Scope a query to only include reactions by a specific reactor.
     *
     * @param Builder $query
     * @param Model $reactor
     *
     * @return Builder
     */
    public function scopeOfReactor(Builder $query, Model $reactor): Builder
    {
        return $query->where([
            'reactor_type' => get_class($reactor),
            'reactor_id' => $reactor->getKey(),
        ]);
    }

    /**
     * Scope a query to only include reactions for a specific reactable model.
     *
     * @param Builder $query
     * @param Model $reactable
     *
     * @return Builder
     */
    public function scopeOfReactable(Builder $query, Model $reactable): Builder
    {
        return $query->where([
            'reactable_type' => get_class($reactable),
            'reactable_id' => $reactable->getKey(),
        ]);
    }

    /**
     * Set the IP address of the reaction.
     *
     * If no value is provided explicitly, it will default to the current request's IP address.
     *
     * @param string|null $value
     * @return void
     */
    public function setIpAttribute(?string $value): void
    {
        $this->attributes['ip'] = $value ?? request()->ip();
    }

    /**
     * Set the device ID for the reaction.
     *
     * If no value is provided, it attempts to get the device ID from the request header
     * as defined in the config under `reaction.headers.device_id`.
     *
     * @param string|null $value
     * @return void
     */
    public function setDeviceIdAttribute(?string $value): void
    {
        $this->attributes['device_id'] = $value ?? request()->header(config('reaction.headers.device_id'));
    }

    /**
     * Set the source of the reaction (e.g. 'web', 'app', 'api').
     *
     * If no value is provided, it fetches it from the request header using the config key
     * `reaction.headers.source`, falling back to `'web'` by default.
     *
     * @param string|null $value
     * @return void
     */
    public function setSourceAttribute(?string $value): void
    {
        $this->attributes['source'] = $value ?? request()->header(config('reaction.headers.source'), 'web');
    }

    /**
     * Check if the reaction is of a specific type.
     *
     * @param string $type The type of reaction to check (e.g. 'like', 'dislike').
     *
     * @return bool
     */
    public function isReactedAs(string $type): bool
    {
        return $this->reaction === $type;
    }
}
