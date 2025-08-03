<?php

namespace JobMetric\Reaction\Events;

use JobMetric\Reaction\Models\Reaction;

class AddReactionEvent
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
