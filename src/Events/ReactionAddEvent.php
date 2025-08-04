<?php

namespace JobMetric\Reaction\Events;

use JobMetric\Reaction\Models\Reaction;

class ReactionAddEvent
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
