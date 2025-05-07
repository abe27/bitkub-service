<?php

namespace Abe27\Bitkub\Facades;

use Illuminate\Support\Facades\Facade;

class Bitkub extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'bitkub';
    }
}
