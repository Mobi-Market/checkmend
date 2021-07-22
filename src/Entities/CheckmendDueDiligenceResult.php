<?php

declare(strict_types=1);

namespace Autumndev\Checkmend\Entities;

use StdClass;

class CheckmendDueDiligenceResult
{
    public $result;
    public $certid;

    public function __construct(StdClass $raw)
    {
        $this->result = $raw->result;
        $this->certid = $raw->certid;
    }
}
