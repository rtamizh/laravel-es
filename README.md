# laravel-es
The basic orm package for elasticsearch (search functionalities)

# Installation 

    ```
    composer require tamizh/laravel-es
    ```

or add the following line in composer.json line 

"tamizh/laravel-es" : "dev-master" and run ``` composer update ```

# Configuration

Add the service provider to your config/app.php file:

    ``` 
    'providers'     => array(
        //...
        Tamizh\LaravelEs\ElasticsearchServiceProvider::class,
    ), 
    ```

Add the facade to your config/app.php file:

    ``` 
    'aliases' => array(
        //...
        'Elasticsearch' => Tamizh\LaravelEs\Elasticsearch::class,
    ), 
    ```

Publish config file using ``` php artisan vendor:publish ```

Modifiy the config/elasticsearch.php.

Example

    ```
    return [
      'hosts' => [
        env('ES_HOSTS', 'localhost:9200')
      ],
      'log_path' => 'storage/logs/',
    ];
    ```

Instead of extends the Model class in your models extend the Elasticsearch to use the following functions.
    ```
    class Log extends Elasticsearch
    {
      public $_index = 'logs*';
      public $_type = 'log';
    }
    ```

# Available Functions

1. match & match_phrase - Returns the results that matches the text
    ```
    Log::match('field', 'text')->get()
    Log::matchPhrase('field', 'hello world')->get()
    ```
2. boolMust, boolMustNot, boolShould, boolShouldNot - Boolean queries (Equal to AND and OR in mysql)
    ```
    Log::boolMust(function($query){
        $query->match('field', 'text');
    })->get()
    ```
3. terms - Return the result that matches terms array
    ```
    Log::terms('field', array)->get()
    ```
4. aggs - Aggregate the result (sub aggregation not yet supported)
    ```
    Log::aggs(function($query){
        $query->terms('field')->size(10)->minDocCount(10);
    }, 'top_logs')
    ```
5. sort - Sort the query result
    ```
    Log::sort('field', 'desc')
    ```
    or
    ```
    Log::sort(function($query){
        $query->script('return doc['error'].value + doc['success'].value')
    })
    ```
 6. scroll - Get the Iterator Object to scroll.
    ```
    $results = Log::match('field', 'text')->size(100)->scroll();
    foreach($results as $result){
        // logic goes here
    }
    ```
7. size - Size of the result collection
    ```
    Log::match('field', 'text')->size(100)->get()
    ```
8. highlight - To highlight the selected text
    ```
    Log::match('field', $text)->highlight('field')->get()
    ```
9. first - To get first model
    ```
    Log::match('field', $text)->first()
    ```
10. save - To save the current model to the ES
    ```
    $log = Log::match('field', $text)->first();
    $log->count = $log->count + 1;
    $log->save();
    ```
11. delete - To delete the current model from ES
    ```
    $log = Log::match('field', $text)->first();
    $log->delete();
    ```
    or 
    ```
    Log::delete(1);
    ```
12. query_string - query string function in ES
    ```
    $log = Log::queryString(function($query){
        $query->query('tech*')
            ->fields(['errors', 'content']);
    })->get();
    ```
    in bool functions
    ```
    Log::boolMust(function($query){
        $query->queryString(function($query){
            $query->query('tech*')
                ->fields(['errors', 'content']);
        })
    })->get();
    ```

13. exists - exists condition functionality
    ```
    $log = Log::exists('field')->get();
    ```
14. index - index document in ES
    ```
    Log::index(['key' => 'value', 'key1' => 'value1'], id);
    ```
15. update - update document in ES
    ```
    Log::update(['new_key' => 'new_value', 'old_key' => 'new_value'], id);
    ```
16. removeKey - remove unwanted key from ES
    ```
    Log::removeKey('unwanted_key', id);
    ```
17. script - script functionality
    ```
    Log::script("doc['errors'].value > 10").get()
    ```

# Notes
1. Following field names are reserved - _id, _type, _index, _highlight

# TODO
1. Write test cases (stable version)
2. Adding More functionalities
3. Indexing functionalities
4. Mysql query format support

