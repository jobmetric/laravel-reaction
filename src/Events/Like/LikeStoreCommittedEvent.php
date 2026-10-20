<?php

namespace JobMetric\Like\Events\Like;

use JobMetric\EventSystem\Contracts\DomainEvent;
use JobMetric\EventSystem\Support\DomainEventDefinition;

/**
 * Describe a committed Like created outcome using allowlisted identifiers.
 */
readonly class LikeStoreCommittedEvent implements DomainEvent
{
    /**
     * Capture identifiers without models, input values or credentials.
     *
     * @param string $ownerType
     * @param int $ownerId
     * @param int $userId
     *
     * @return void
     */
    public function __construct(
        public string $ownerType,
        public int $ownerId,
        public int $userId
    ) {
    }

    /**
     * Return the stable key used by automation consumers.
     *
     * @return string
     */
    public static function key(): string
    {
        return 'like.like.stored.committed';
    }

    /**
     * Describe the outcome for translated discovery interfaces.
     *
     * @return DomainEventDefinition
     */
    public static function definition(): DomainEventDefinition
    {
        return new DomainEventDefinition(self::key(), 'like::automation.like_stored.group', 'like::automation.like_stored.title', 'like::automation.like_stored.description', 'fas fa-bolt', ['automation', 'committed', 'like']);
    }
}
