<?php

namespace JobMetric\Reaction\Events;

use JobMetric\Reaction\Models\Reaction;

class RemovingReactionEvent
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
