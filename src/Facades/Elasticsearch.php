<?php

namespace Tamizh\LaravelEs\Facades;

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
        return 'Tamizh\LaravelEs\Elasticsearch'; // the IoC binding.
    }
}
