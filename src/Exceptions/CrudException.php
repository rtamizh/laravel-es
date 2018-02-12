<?php

namespace Tamizh\LaravelEs\Exceptions;

use Exception;

/**
* Exception Class for CRUD based operations
*/
class CrudException extends Exception
{
    public static function indexWithSpecialCharaceters($index)
    {
        return new static("The given index name [$index] contains wildcard characters. Give specific index name to proceed");
    }
}
