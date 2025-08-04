<?php

namespace JobMetric\Reaction;

use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use JobMetric\Reaction\Models\Reaction;
use Illuminate\Support\Collection;

/**
 * Trait CanReact
 *
 * Enables a model to perform reactions on other models that implement the `HasReaction` trait.
 * This trait is typically applied to user models or any actor that can "react" to content.
 *
 * Provides features such as:
 * - Tracking all reactions made by the model
 * - Checking for specific reactions to content
 * - Aggregating and summarizing reaction data
 * - Removing or updating reactions
 *
 * @mixin Model
 */
trait CanReact
{
    /**
     * Define a polymorphic one-to-many relationship to Reaction model.
     *
     * @return MorphMany
     */
    public function reactionsGiven(): MorphMany
    {
        return $this->morphMany(Reaction::class, 'reactor');
    }

    /**
     * Check if this reactor has reacted to a specific reactable model.
     *
     * @param Model $reactable The model being reacted to.
     * @param string|null $reaction Optional reaction type to filter (e.g. 'like').
     *
     * @return bool True if a reaction exists.
     */
    public function hasReactedTo(Model $reactable, ?string $reaction = null): bool
    {
        $query = $this->reactionsGiven()
            ->where('reactable_type', get_class($reactable))
            ->where('reactable_id', $reactable->getKey());

        if ($reaction) {
            $query->where('reaction', $reaction);
        }

        return $query->exists();
    }

    /**
     * Check if this reactor has reacted with a specific reaction to a specific reactable.
     *
     * @param string $reaction The type of reaction (e.g. 'like', 'love').
     * @param Model $reactable The target model.
     *
     * @return bool
     */
    public function reactedWithTo(string $reaction, Model $reactable): bool
    {
        return $this->reactionsGiven()
            ->where('reaction', $reaction)
            ->where('reactable_type', get_class($reactable))
            ->where('reactable_id', $reactable->getKey())
            ->exists();
    }

    /**
     * Get the reaction this reactor made to a specific reactable.
     *
     * @param Model $reactable The target model.
     *
     * @return Model|MorphMany The reaction model or null.
     */
    public function reactionTo(Model $reactable): Model|MorphMany
    {
        return $this->reactionsGiven()
            ->where('reactable_type', get_class($reactable))
            ->where('reactable_id', $reactable->getKey())
            ->latest()
            ->first();
    }

    /**
     * Remove a reaction from a specific reactable model.
     *
     * @param Model $reactable The target model.
     *
     * @return bool True if deletion was successful.
     */
    public function removeReactionFrom(Model $reactable): bool
    {
        return $this->reactionsGiven()
                ->where('reactable_type', get_class($reactable))
                ->where('reactable_id', $reactable->getKey())
                ->delete() > 0;
    }

    /**
     * Count how many times this reactor has used a specific reaction type.
     *
     * @param string $reaction The reaction type (e.g. 'like').
     *
     * @return int
     */
    public function countReactionMade(string $reaction): int
    {
        return $this->reactionsGiven()
            ->where('reaction', $reaction)
            ->count();
    }

    /**
     * Count the total number of reactions this model has made.
     *
     * @return int
     */
    public function totalReactionsGiven(): int
    {
        return $this->reactionsGiven()->count();
    }

    /**
     * Get a summary of all reaction types and their total counts.
     *
     * @return Collection<string, int> A map of reaction name => count.
     */
    public function reactionSummary(): Collection
    {
        return $this->reactionsGiven()
            ->select('reaction', DB::raw('count(*) as total'))
            ->groupBy('reaction')
            ->pluck('total', 'reaction');
    }

    /**
     * Get all reactable items this reactor has reacted to.
     *
     * @param string|null $reaction Optional reaction type.
     * @param string|null $reactableClass Optional reactable class filter.
     *
     * @return Collection<Model> A list of models reacted to.
     */
    public function reactedItems(?string $reaction = null, ?string $reactableClass = null): Collection
    {
        $query = $this->reactionsGiven();

        if ($reaction) {
            $query->where('reaction', $reaction);
        }

        if ($reactableClass) {
            $query->where('reactable_type', $reactableClass);
        }

        return $query->get()->map(fn(Reaction $r) => $r->reactable);
    }

    /**
     * Get all reactions this reactor made to a specific reactable type.
     *
     * @param string $reactableClass The reactable model class name.
     *
     * @return Collection<Reaction>
     */
    public function reactionsToType(string $reactableClass): Collection
    {
        return $this->reactionsGiven()
            ->where('reactable_type', $reactableClass)
            ->get();
    }

    /**
     * Get the latest reactions made by this model.
     *
     * @param int $limit The number of reactions to retrieve.
     *
     * @return Collection<Reaction>
     */
    public function latestReactionsGiven(int $limit = 5): Collection
    {
        return $this->reactionsGiven()->latest()->take($limit)->get();
    }
}
