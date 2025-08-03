<?php

namespace JobMetric\Reaction\Events;

use JobMetric\Reaction\Models\Reaction;

class RemovedReactionEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Reaction $reaction
    )
    {
    }
}
