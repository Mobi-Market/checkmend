<?php

namespace Autumndev\Checkmend;

use Autumndev\Checkmend\CheckmendBaseException;

class CheckmendInvalidImeiException extends CheckmendBaseException 
{
    public function __construct($message, $code = 0, Exception $previous = null) {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }
}