<?php

namespace JobMetric\Reaction;

use DB;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use JobMetric\Reaction\Events\AddReactionEvent;
use JobMetric\Reaction\Events\RemovedReactionEvent;
use JobMetric\Reaction\Events\RemovingReactionEvent;
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
     * Add a reaction to the model by a liker or device.
     *
     * @param string $reaction The name/type of the reaction (e.g., 'like', 'love').
     * @param Model|null $liker The model that is reacting (e.g., a User).
     * @param array $options Optional values: ip, device_id, source.
     *
     * @return Reaction
     */
    public function addReaction(string $reaction, ?Model $liker = null, array $options = []): Reaction
    {
        $data = [
            'reaction' => $reaction,
            'ip' => $options['ip'] ?? request()->ip(),
            'device_id' => $options['device_id'] ?? null,
            'source' => $options['source'] ?? null,
        ];

        if ($liker instanceof Model) {
            $data['liker_type'] = $liker::class;
            $data['liker_id'] = $liker->getKey();
        }

        $reaction = $this->reactions()->create($data);

        event(new AddReactionEvent($reaction));

        return $reaction;
    }

    /**
     * Internal helper to build a reaction query for the given conditions.
     *
     * @param string $reaction
     * @param Model|null $liker
     * @param string|null $device_id
     * @param bool $withTrashed Include soft-deleted records if true.
     *
     * @return MorphMany
     */
    private function findReaction(string $reaction, ?Model $liker = null, ?string $device_id = null, bool $withTrashed = false): MorphMany
    {
        $query = $this->reactions();

        if ($withTrashed) {
            $query->withTrashed();
        }

        $query->where('reaction', $reaction);

        $query->where(function ($q) use ($liker, $device_id) {
            if ($liker instanceof Model) {
                $q->where([
                    'liker_type' => $liker::class,
                    'liker_id' => $liker->getKey(),
                ]);
            }

            if ($device_id) {
                $q->orWhere('device_id', $device_id);
            }
        });

        return $query;
    }

    /**
     * Remove a specific reaction from the model by a liker or device.
     *
     * @param string $reaction
     * @param Model|null $liker
     * @param string|null $device_id
     *
     * @return bool True if deletion was successful.
     */
    public function removeReaction(string $reaction, ?Model $liker = null, ?string $device_id = null): bool
    {
        /**
         * @var Reaction|null $reactionModel
         */
        $reactionModel = $this->findReaction($reaction, $liker, $device_id)->firstOrFail();

        event(new RemovingReactionEvent($reactionModel));

        $deleted = $reactionModel->delete();

        event(new RemovedReactionEvent($reactionModel));

        return $deleted > 0;
    }

    /**
     * Toggle a reaction: add if it doesn't exist, otherwise remove it.
     *
     * @param string $reaction
     * @param Model|null $liker
     * @param array $options Optional values: ip, device_id, source.
     *
     * @return Reaction|bool
     */
    public function toggleReaction(string $reaction, ?Model $liker = null, array $options = []): Reaction|bool
    {
        $device_id = $options['device_id'] ?? null;

        if ($this->hasReaction($reaction, $liker, $device_id)) {
            return $this->removeReaction($reaction, $liker, $device_id);
        }

        return $this->addReaction($reaction, $liker, $options);
    }

    /**
     * Remove all reactions by a liker or device. Supports force delete.
     *
     * @param Model|null $liker
     * @param string|null $device_id
     * @param bool $force If true, permanently delete.
     *
     * @return int Number of deleted rows.
     */
    public function removeAllReactions(?Model $liker = null, ?string $device_id = null, bool $force = false): int
    {
        $query = $this->reactions();

        if ($force) {
            return $query->forceDelete();
        }

        $query->where(function ($q) use ($liker, $device_id) {
            if ($liker instanceof Model) {
                $q->where([
                    'liker_type' => $liker::class,
                    'liker_id' => $liker->getKey(),
                ]);
            }

            if ($device_id) {
                $q->orWhere('device_id', $device_id);
            }
        });

        return $query->delete();
    }

    /**
     * Restore a soft-deleted reaction.
     *
     * @param string $reaction
     * @param Model|null $liker
     * @param string|null $device_id
     *
     * @return bool True if restored.
     */
    public function restoreReaction(string $reaction, ?Model $liker = null, ?string $device_id = null): bool
    {
        $model = $this->findReaction($reaction, $liker, $device_id, true)->onlyTrashed()->first();

        if (!$model) return false;

        return $model->restore();
    }

    /**
     * Update the reaction type from one to another.
     *
     * @param string $from Original reaction type.
     * @param string $to New reaction type.
     * @param Model|null $liker
     * @param string|null $device_id
     *
     * @return Reaction
     */
    public function updateReaction(string $from, string $to, ?Model $liker = null, ?string $device_id = null): Reaction
    {
        /**
         * @var Reaction|null $existing
         */
        $existing = $this->findReaction($from, $liker, $device_id)->first();

        if ($existing) {
            $existing->update(['reaction' => $to]);

            return $existing;
        }

        return $this->addReaction($to, $liker, ['device_id' => $device_id]);
    }

    /**
     * Check if the model has a specific reaction from a liker or device.
     *
     * @param string $reaction
     * @param Model|null $liker
     * @param string|null $device_id
     * @param bool $withTrashed Include soft-deleted records if true.
     *
     * @return bool
     */
    public function hasReaction(string $reaction, ?Model $liker = null, ?string $device_id = null, bool $withTrashed = false): bool
    {
        return $this->findReaction($reaction, $liker, $device_id, $withTrashed)->exists();
    }

    /**
     * Get the total number of all reactions for the model.
     *
     * @param bool $withTrashed Include soft-deleted records if true.
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
     * Get the number of reactions of a specific type.
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
     * Get a summary of all reaction types and their counts.
     *
     * @param bool $withTrashed Include soft-deleted records if true.
     *
     * @return Collection<string, int> A map of reaction name => count.
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
     * Get the latest reactions on the model.
     *
     * @param int $limit Maximum number of reactions to retrieve.
     * @param bool $withTrashed Include soft-deleted reactions if true.
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
}
