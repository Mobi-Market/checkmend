<?php

declare(strict_types=1);

namespace Autumndev\Checkmend\Exceptions;

use Autumndev\Checkmend\Exceptions\CheckmendBaseException;

class CheckmendInvalidImeiException extends CheckmendBaseException
{
    public function __construct($message, $code = 0, ?\Throwable $previous = null)
    {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
}
