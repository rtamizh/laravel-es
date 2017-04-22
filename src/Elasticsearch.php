<?php
namespace Tamizh\Phpes;

use Elasticsearch\ClientBuilder;
use Tamizh\Phpes\ConstraintClause;
use Tamizh\Phpes\QueryBuilder as QueryBuilder;

/**
*
*/
abstract class Elasticsearch
{
    /**
     * Elasticsearch client object build with user configuration
     * @var Elasticsearch\CleientBuilder
     */
    protected static $client;

    /**
     * Current index of the model object
     * @var string
     * @todo index need to be set automatically by child class name
     */
    public $index;

    /**
     * Type of the current model
     * @var string
     */
    public $type;

    /**
     * Primary Id of the current model
     * @var string
     */
    public $id;

    public static function setClient($client_connection)
    {
        static::$client = $client_connection->client;
    }

    /**
     * Set the index externally while querying
     * @param  string  $string  Index name
     * @return App\Elasticsearch
     * @todo index name should be moved to normal db connection model
     *       to make it is generic
     */
    public static function index($string)
    {
        $this->index = $string;
        return $this;
    }

    /**
     * Return the current model index
     * @return string The current index
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $query = $this->newQuery();
        return call_user_func_array([$query, $method], $parameters);
    }

    /**
     * Return new Query object
     * @return Tamizh\Phpes\QueryBuilder
     */
    public function newQuery()
    {
        $query = new QueryBuilder(static::$client);
        $query->setModel($this);
        return $query;
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        $instance = new static;
        return call_user_func_array([$instance, $method], $parameters);
    }
}
