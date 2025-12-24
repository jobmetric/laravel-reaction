<?php

namespace JobMetric\Reaction\Events;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;
use JobMetric\Reaction\Models\Reaction;

readonly class ReactionAddEvent implements DomainEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Reaction $reaction
    ) {
    }

    /**
     * Returns the stable technical key for the domain event.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'reaction.added';
    }

    /**
     * Returns the full metadata definition for this domain event.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'reaction::base.entity_names.reaction', 'reaction::base.events.reaction_added.title', 'reaction::base.events.reaction_added.description', 'fas fa-heart', [
            'reaction',
            'interaction',
            'social',
        ]);
    }
}
