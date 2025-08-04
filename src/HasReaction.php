<?php

namespace JobMetric\Reaction;

use DB;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use JobMetric\Reaction\Events\ReactionAddEvent;
use JobMetric\Reaction\Events\ReactionRemovedEvent;
use JobMetric\Reaction\Events\ReactionRemovingEvent;
use JobMetric\Reaction\Models\Reaction;

/**
 * Trait HasReaction
 *
 * Provides reaction (like, love, etc.) functionality to Eloquent models.
 *
 * @mixin Model
 */
trait HasReaction
{
    /**
     * Define a polymorphic one-to-many relationship to Reaction.
     *
     * @return MorphMany
     */
    public function reactions(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    /**
     * Add a reaction to the model by a reactor or device.
     *
     * @param string $reaction
     * @param Model|null $reactor
     * @param array $options
     *
     * @return Reaction
     */
    public function addReaction(string $reaction, ?Model $reactor = null, array $options = []): Reaction
    {
        $data = [
            'reaction' => $reaction,
            'ip' => $options['ip'] ?? request()->ip(),
            'device_id' => $options['device_id'] ?? null,
            'source' => $options['source'] ?? null,
        ];

        if ($reactor instanceof Model) {
            $data['reactor_type'] = $reactor::class;
            $data['reactor_id'] = $reactor->getKey();
        }

        // Try to find existing reaction (by reactor or device, not both)
        $query = $this->reactions()->where(function ($q) use ($data) {
            if (isset($data['reactor_type'], $data['reactor_id'])) {
                $q->where('reactor_type', $data['reactor_type'])
                    ->where('reactor_id', $data['reactor_id']);
            } elseif (!empty($data['device_id'])) {
                $q->where('device_id', $data['device_id']);
            }
        });

        /** @var Reaction|null $existing */
        $existing = $query->first();

        if ($existing) {
            // Same reaction already exists → skip update
            if ($existing->reaction === $reaction) {
                return $existing;
            }

            // Update reaction type if different
            $existing->update([
                'reaction' => $reaction,
                'ip' => $data['ip'],
                'device_id' => $data['device_id'],
                'source' => $data['source'],
            ]);

            return $existing;
        }

        // No existing reaction found, create new
        $reaction = $this->reactions()->create($data);

        event(new ReactionAddEvent($reaction));

        return $reaction;
    }

    /**
     * Remove a specific reaction from the model.
     *
     * @param string $reaction
     * @param Model|null $reactor
     * @param string|null $device_id
     *
     * @return bool
     */
    public function removeReaction(string $reaction, ?Model $reactor = null, ?string $device_id = null): bool
    {
        /**
         * @var Reaction $reactionModel
         */
        $reactionModel = $this->findReaction($reaction, $reactor, $device_id)->firstOrFail();

        event(new ReactionRemovingEvent($reactionModel));

        $deleted = $reactionModel->delete();

        event(new ReactionRemovedEvent($reactionModel));

        return $deleted > 0;
    }

    /**
     * Toggle a reaction.
     *
     * @param string $reaction
     * @param Model|null $reactor
     * @param array $options
     *
     * @return Reaction|bool
     */
    public function toggleReaction(string $reaction, ?Model $reactor = null, array $options = []): Reaction|bool
    {
        $device_id = $options['device_id'] ?? null;

        if ($this->hasReaction($reaction, $reactor, $device_id)) {
            return $this->removeReaction($reaction, $reactor, $device_id);
        }

        return $this->addReaction($reaction, $reactor, $options);
    }

    /**
     * Remove all reactions by a reactor or device.
     *
     * @param Model|null $reactor
     * @param string|null $device_id
     * @param bool $force
     *
     * @return int
     */
    public function removeAllReactions(?Model $reactor = null, ?string $device_id = null, bool $force = false): int
    {
        $query = $this->reactions();

        $query->where(function ($q) use ($reactor, $device_id) {
            if ($reactor instanceof Model) {
                $q->where([
                    'reactor_type' => $reactor::class,
                    'reactor_id' => $reactor->getKey(),
                ]);
            }

            if ($device_id) {
                $q->orWhere('device_id', $device_id);
            }
        });

        $items = $query->get();

        foreach ($items as $reaction) {
            $force ? $reaction->forceDelete() : $reaction->delete();
        }

        return $items->count();
    }

    /**
     * Restore a soft-deleted reaction.
     *
     * @param string $reaction
     * @param Model|null $reactor
     * @param string|null $device_id
     *
     * @return bool
     */
    public function restoreReaction(string $reaction, ?Model $reactor = null, ?string $device_id = null): bool
    {
        $model = $this->findReaction($reaction, $reactor, $device_id, true)->onlyTrashed()->first();

        return $model ? $model->restore() : false;
    }

    /**
     * Update the reaction type.
     *
     * @param string $from
     * @param string $to
     * @param Model|null $reactor
     * @param string|null $device_id
     *
     * @return Reaction
     */
    public function updateReaction(string $from, string $to, ?Model $reactor = null, ?string $device_id = null): Reaction
    {
        /**
         * @var Reaction|null $existing
         */
        $existing = $this->findReaction($from, $reactor, $device_id)->first();

        if ($existing) {
            $existing->update(['reaction' => $to]);

            return $existing;
        }

        return $this->addReaction($to, $reactor, ['device_id' => $device_id]);
    }

    /**
     * Check if a specific reaction exists.
     *
     * @param string $reaction
     * @param Model|null $reactor
     * @param string|null $device_id
     * @param bool $withTrashed
     *
     * @return bool
     */
    public function hasReaction(string $reaction, ?Model $reactor = null, ?string $device_id = null, bool $withTrashed = false): bool
    {
        return $this->findReaction($reaction, $reactor, $device_id, $withTrashed)->exists();
    }

    /**
     * Count all reactions.
     *
     * @param bool $withTrashed
     *
     * @return int
     */
    public function totalReactions(bool $withTrashed = false): int
    {
        $query = $this->reactions();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->count();
    }

    /**
     * Count reactions of a specific type.
     *
     * @param string $reaction
     *
     * @return int
     */
    public function countReactions(string $reaction): int
    {
        return $this->reactions()->where('reaction', $reaction)->count();
    }

    /**
     * Get summary of all reaction types.
     *
     * @param bool $withTrashed
     *
     * @return Collection
     */
    public function reactionSummary(bool $withTrashed = false): Collection
    {
        $query = $this->reactions();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query
            ->select('reaction', DB::raw('count(*) as total'))
            ->groupBy('reaction')
            ->pluck('total', 'reaction');
    }

    /**
     * Get the latest reactions.
     *
     * @param int $limit
     * @param bool $withTrashed
     *
     * @return EloquentCollection
     */
    public function latestReactions(int $limit = 5, bool $withTrashed = false): EloquentCollection
    {
        $query = $this->reactions();

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->latest()->take($limit)->get();
    }

    /**
     * Internal query builder for a specific reaction.
     *
     * @param string $reaction
     * @param Model|null $reactor
     * @param string|null $device_id
     * @param bool $withTrashed
     *
     * @return MorphMany
     */
    private function findReaction(string $reaction, ?Model $reactor = null, ?string $device_id = null, bool $withTrashed = false): MorphMany
    {
        $query = $this->reactions();

        if ($withTrashed) {
            $query->withTrashed();
        }

        $query->where('reaction', $reaction);

        $query->where(function ($q) use ($reactor, $device_id) {
            if ($reactor instanceof Model) {
                $q->where([
                    'reactor_type' => $reactor::class,
                    'reactor_id' => $reactor->getKey(),
                ]);
            }

            if ($device_id) {
                $q->orWhere('device_id', $device_id);
            }
        });

        return $query;
    }
}
