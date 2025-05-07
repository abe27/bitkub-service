<?php

namespace Abe27\BitkubService\Facades;

use Illuminate\Support\Facades\Facade;

class BitkubService extends Facade
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
