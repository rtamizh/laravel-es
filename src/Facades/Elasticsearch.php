<?php

namespace Tamizh\Phpes\Facades;

use Illuminate\Support\Facades\Facade;

class Elasticsearch extends Facade
{
    /**
     * Get the binding in the IoC container
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Tamizh\Phpes\Elasticsearch'; // the IoC binding.
    }
}
