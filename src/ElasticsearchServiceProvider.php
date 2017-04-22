<?php

namespace Tamizh\LaravelEs;

use Illuminate\Support\ServiceProvider;
use Tamizh\LaravelEs\Elasticsearch;
use Tamizh\LaravelEs\ElasticClient;

class ElasticsearchServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Elasticsearch::setClient($this->app['elastic_client']);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app ?: app();
        
        $this->publishes([
            __DIR__.'/../config/elasticsearch.php' => base_path('config/elasticsearch.php'),
        ]);

        $this->app->singleton('elastic_client', function () use ($app)
        {
            return new ElasticClient($app['config']);
        });
    }
}
