<?php

namespace Autumndev\Checkmend\Exceptions;

use Autumndev\Checkmend\Exceptions\CheckmendBaseException;

class CheckmendInvalidRequestBody extends CheckmendBaseException 
{
    public function __construct($message, $code = 0, Exception $previous = null) {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
}