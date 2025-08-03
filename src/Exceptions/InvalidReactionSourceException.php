<?php

namespace JobMetric\Reaction\Exceptions;

use Exception;
use Throwable;

class InvalidReactionSourceException extends Exception
{
    public function __construct(int $code = 400, ?Throwable $previous = null)
    {
        parent::__construct(trans('reaction::base.exceptions.invalid_reaction_source'), $code, $previous);
    }
}
