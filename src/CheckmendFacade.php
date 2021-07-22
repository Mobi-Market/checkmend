<?php

declare(strict_types=1);

namespace Autumndev\Checkmend;

use Illuminate\Support\Facades\Facade;

class CheckmendFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'checkmend';
    }
}
