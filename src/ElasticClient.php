<?php
namespace Tamizh\LaravelEs;

use Elasticsearch\ClientBuilder;

/**
* Elasticsearch client class
*/
class ElasticClient
{
    /**
     * The elasticsearch client builder configuration.
     * Fetched from config array of the laravel application
     * @var array
     */
    protected $config;

    /**
     * Elasticsearch client connection variable
     * @var Elasticsearch\ClientBuilder
     */
    public $client;

    public function __construct($config)
    {
        if ($config->has('elasticsearch')) {
            $this->config = $config->get('elasticsearch');
        } else {
            throw new Exception('No config found');
        }

        $logger = ClientBuilder::defaultLogger($this->config['log_path'] . "elasticsearch.log");
        $this->client = ClientBuilder::create()
            ->setHosts($this->config['hosts'])
            ->setLogger($logger)  // Set the logger with a default logger
            ->build();
    }

    public static function getClient()
    {
        return $this->client;
    }
}
