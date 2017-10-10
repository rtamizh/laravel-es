<?php
namespace Tamizh\LaravelEs;

use Exception;
use Tamizh\LaravelEs\Traits\ElasticQueryTrait;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
* Convert Elasticsearch model object query array
*/
class QueryBuilder
{
    use ElasticQueryTrait;
    /**
     * Formed query using elasticsearch model
     * @var array
     */
    protected $query;

    /**
     * Model that need to be converted as query array
     * @var Tamizh\LaravelEs\Elasticsearch
     */
    protected $model;

    /**
     * Elasticsearch client object
     * @var Elasticsearch\ClientBuilder
     */
    public $client;

    /**
     * The main query part in the query array
     * @var array
     */
    protected $internal_query;

    /**
     * The must constraints of the bool query
     * @var array
     */
    protected $musts;

    /**
     * The must not constraint of the bool query
     * @var array
     */
    protected $must_nots;

    /**
     * The should constraints of the bool query
     * @var array
     */
    protected $shoulds;

    /**
     * The should not constraints of the bool query
     * @var array
     */
    protected $should_nots;

    /**
     * The match constraint of the query
     * @var array
     */
    public $constraints = array();

    /**
     * The aggregations of the query
     * @var array
     */
    protected $aggs = [];

    /**
     * The sort constraint of the query
     * @var array
     */
    protected $sort;

    /**
     * Bools array for the query builder
     * @var array
     */
    public $bools = [];

    /**
     * Script array for the query builder
     * @var array
     */
    protected $script;

    /**
     * Limit to clear the scroll memory
     * @var string
     */
    protected $scroll_param;

    /**
     * Result of the current buider
     * @var array
     */
    protected $result;

    /**
     * Collection result of the builder
     * @var Illuminate\Support\Collection
     */
    protected $collection;

    /**
     * Hightlight Array
     * @var array
     */
    protected $highlight;

    /**
     * Filter Array
     * @var array
     */
    protected $filter;

    /**
     * query string functionality
     * @var Tamizh\LaravelES\QueryStringClause
     */
    protected $query_string;

    /**
     * search from a specific type functionality
     * @var Tamizh\LaravelES\QueryStringClause
     */
    protected $fromType;

    /**
     * search from specific indices functionality
     * @var Tamizh\LaravelES\QueryStringClause
     */
    protected $fromIndex;

    /**
     * Initialize the query builder
     * @param Tamizh\LaravelEs\Elasticsearch  $model  Elasticsearch Model
     */
    public function __construct($client)
    {
        $this->client = $client;
        $this->query['body'] = [
            'query' => []
        ];
    }

    /**
     * Compile Constrint clause for multi_match
     * @param  Tamizh\LaravelEs\ConstraintClause $constraint
     * @return constraint array
     */
    public function compileMultiMatch($constraint)
    {
        $condition = array();
        $condition['fields'] = $constraint->field;
        $condition['query'] = $constraint->condition;
        $condition['type'] = $constraint->search_clause_type;
        return $condition;
    }
    

    /**
     * Compile Constrint clause for match
     * @param  Tamizh\LaravelEs\ConstraintClause $constraint
     * @return constraint array
     */
    public function compileMatch($constraint)
    {
        $condition = array();
        $condition[$constraint->field] = $constraint->condition;
        return $condition;
    }

    /**
     * Compile Constrint clause for match_phrase
     * @param  Tamizh\LaravelEs\ConstraintClause $constraint
     * @return constraint array
     */
    public function compileMatchPhrase($constraint)
    {
        $condition = array();
        $condition[$constraint->field] = $constraint->condition;
        return $condition;
    }

    /**
     * Compile the constraint clause for match_phrase_prefix
     * @param  Tamizh\LaravelEs\ConstraintClause $constraint  $constraint
     * @return  constraint array
     */
    public function compileMatchPhrasePrefix($constraint)
    {
        $condition = array();
        $condition[$constraint->field] = $constraint->condition;
        return $condition;
    }

    /**
     * Compile terms constraint clause
     * @param  Tamizh\LaravelEs\ConstraintClause  $constraint  Constraint Clause
     * @return  array  condition array
     */
    public function compileTerms($constraint)
    {
        $condition = array();
        $condition[$constraint->field] = $constraint->condition;
        return $condition;
    }

    public function compileExists($constraint)
    {
        $condition = array();
        $condition['field'] = $constraint->condition;
        return $condition;
    }

    public function compileRange($constraint)
    {
        $condition = array();
        $condition[$constraint->field] = $constraint->condition;
        return $condition;
    }


    /**
     * Form and return the query from elasticsearch model
     * @return array  Elasticsearch query array
     */
    public function get()
    {
        return $this->getCollection($this->getRaw());
    }

    /**
     * Return First model from the current query collection
     * @return mixed  Model or null
     */
    public function first()
    {
        $collection = $this->get();
        return count($collection) ? $collection[0] : null;
    }

    public function paginate($perPage, $page = 1)
    {
        // set the size and from parameters as per the pagination
        $this->size($perPage);
        $this->from($perPage)->from(($page - 1) * $perPage);
        $raw = $this->getRaw();
        return new LengthAwarePaginator($this->getCollection($raw)->forPage(1, $perPage), $raw['hits']['total'], $perPage, $page);
    }

    /**
     * Create collection by the current query result
     * @param  array  $result  Array of result
     * @return  Illuminate\Support\Collection
     */
    protected function getCollection($result)
    {
        $collection = new Collection();
        foreach ($this->generateModels($result) as $model) {
            $collection->push($model);
        }
        return $collection;
    }

    /**
     * Generate Models based on the current result
     * @param  array  $result  Raw result of current query
     * @return  array  Array of models
     */
    protected function generateModels($result)
    {
        $model_array = [];
        foreach ($result['hits']['hits'] as $hit) {
            $model = new $this->model;
            $model->_index = $hit['_index'];
            $model->_type = $hit['_type'];
            $model->_id = $hit['_id'];
            $this->assignModelVariable($hit['_source'], $model);
            if (isset($hit['highlight'])) {
                $model->_highlight = $hit['highlight'];
            }
            array_push($model_array, $model);
        }
        return $model_array;
    }

    public static function isAssoc(array $arr)
    {
        if (array() === $arr) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    public function assignModelVariable($array, $obj = null)
    {
        if ($obj == null) {
            $obj = new \stdClass();
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $obj->$key = $this->isAssoc($value) ? $this->assignModelVariable($value) : $value;
            } else {
                $obj->$key = $value;
            }
        }
        return $obj;
    }

    /**
     * Get raw ouptut of the current query
     * @return  Tamizh\LaravelEs\QueryBuilder
     */
    public function getRaw()
    {
        return $this->client->search($this->compile());
    }

    public function searchRaw($array)
    {
        return $this->client->search($array);
    }

    public function search($array)
    {
        return $this->getCollection($this->client->search($array));
    }

    /**
     * Get the count of documents for the current query
     * @return int  Document count
     */
    public function count()
    {
        return $this->client->count($this->compile())['count'];
    }

    /**
     * Compile the query object parameter and construct the query array
     * @return  array  query array
     */
    public function compile()
    {
        // set every condition in the query array
        $this->query['body']['query'] = [];
        foreach ($this->constraints as $constraint) {
            $this->query['body']['query'] = array_merge($this->query['body']['query'], $this->compileConstraint($constraint));
        }

        foreach ($this->bools as $bool) {
            if (array_key_exists('constraint', $bool)) {
                $this->query['body']['query']['bool'][$bool['type']][] = $this->compileConstraint($bool['constraint']);
            }
            if (array_key_exists('query_string', $bool)) {
                $this->query['body']['query']['bool'][$bool['type']][] = $bool['query_string'];
            }
            if (array_key_exists('script', $bool)) {
                $this->query['body']['query']['bool'][$bool['type']][] = $bool['script'];
            }
            if (array_key_exists('bool', $bool)) {
                $this->query['body']['query']['bool'][$bool['type']][] = array('bool' => $bool['bool']);
            }
        }
        if (!empty($this->aggs)) {
            $this->query['body']['aggs'] = $this->aggs;
        }
        if ($this->sort) {
            $this->query['body']['sort'] = $this->sort;
        }
        if ($this->scroll_param) {
            $this->query['scroll'] = $this->scroll_param;
        }
        if ($this->highlight) {
            $this->query['body']['highlight'] = $this->highlight;
        }
        if ($this->filter) {
            $this->query['body']['query']['bool']['filter'] = $this->filter;
        }
        if ($this->query_string) {
            $this->query['body']['query']['query_string'] = $this->query_string;
        }
        if ($this->script) {
            $this->query['body']['query']['script']['script'] = $this->script;
        }
        if ($this->fromType) {
            $this->query['type'] = $this->fromType;
        }
        if ($this->fromIndex) {
            $this->query['index'] = $this->fromIndex;
        } elseif (isset($this->model) && $this->model) {
            $this->query["index"] = $this->model->getIndex();
        }
        return $this->query;
    }

    /**
     * Compile the constraint clause
     * @param  Tamizh\LaravelEs\ConstraintClause  $constraint  Constraint Clause
     * @return array  Constraint array
     */
    protected function compileConstraint($constraint)
    {
        $method = 'compile';
        foreach (explode("_", $constraint->type) as $type) {
            $method .= ucfirst($type);
        }
        $condition = [$constraint->type => $this->$method($constraint)];
        return $condition;
    }

    /**
     * Set the size of the result from elasticsearch server
     * @param  integer $size Size of the result
     * @return $this
     */
    public function size($size)
    {
        $this->query['body']['size'] = $size;
        return $this;
    }

    /**
     * Set the from point of the result set [Mysql Offset]
     * [only applicable for 10000 result window, scroll is encouraged for bigger pagination]
     * @param  integer  $from  From point
     * @return  $this
     */
    public function from($from)
    {
        $this->query['body']['from'] = $from;
        return $this;
    }

    /**
     * Get elasticsearch client
     * @return Elasticsearch\ClientBuilder  Elasticsearch client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set model for the query builder object
     * @param Tamizh\LaravelEs\Elasticsearch $model Elasticseach model
     */
    public function setModel($model)
    {
        $this->model = $model;
        $this->query['index'] = $this->model->getIndex();
    }

    /**
     * Get Elasticsearch model
     * @return Tamizh\LaravelEs\Elasticsearch
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Post the current scroll query
     * @param  string  $scroll_id  Scroll id of the current query
     * @return array
     */
    protected function postScroll($scroll_id)
    {
        return $this->getCollection($this->client->scroll([
            'scroll_id' => $scroll_id,
            'scroll' => $this->scroll_param
        ]));
    }

    /**
     * Set the Index of the current Model
     * @param string  $index  Index of the Model
     */
    public function setIndex($index)
    {
        $this->model->_index = $index;
        $this->query['index'] = $this->model->getIndex();
        return $this;
    }

    /**
     * Set the Type of the current Model
     * @param string  $type  Type of model
     */
    public function setType($type)
    {
        $this->model->_type = $type;
        return $this;
    }

    /**
     * Update the ES document
     * @param  array  $doc  key value pair
     * @param  string  $id   Id of the document
     * @return boolean
     */
    public function update($doc = [], $id = null)
    {
        $params['index'] = $this->model->getIndex();
        $params['type'] = $this->model->getType();
        if ($this->model->_id != null || $id != null) {
            $params['id'] = $id ? $id : $this->model->_id;
        } else {
            throw new Exception("Error Processing Request", 1);
        }
        if (count($doc)) {
            $params['body']['doc'] = $doc;
        }
        return $this->client->update($params) ? true : false;
    }

    /**
     * Remove a key value from ES document
     * @param  string  $key  key name
     * @param  string  $id   Document Id
     * @return  boolean
     */
    public function removeKey($key, $id = null)
    {
        $params['index'] = $this->model->getIndex();
        $params['type'] = $this->model->getType();
        if ($this->model->_id == null) {
            $params['id'] = $id;
        }
        $params['body']['script'] = "ctx._source.remove(\"".$key."\")";
        return $this->client->update($params) ? true : false;
    }

    /**
     * Index a new document to ES
     * @param  array  $doc document
     * @param  string  $id  Document Id (optional)
     * @return  Tamizh\LaravelES\Elasticsearch
     */
    public function index($doc = [], $id = null)
    {
        $params['index'] = $this->model->getIndex();
        $params['type'] = $this->model->getType();
        if ($id) {
            $params['id'] = $id;
        }
        $params['body'] = $doc;
        $result = $this->client->index($params);
        if ($result['created'] == true) {
            $model = new $this->model;
            $model->_index = $result['_index'];
            $model->_type = $result['_type'];
            $model->_id = $result['_id'];
            foreach ($doc as $key => $value) {
                $model->$key = $value;
            }
            return $model;
        }
        return null;
    }

    /**
     * Delete Document from ES
     * @param  string  $id  Document ID
     * @return  boolean
     */
    public function delete($id = null)
    {
        $params = [
            'index' => $this->model->_index,
            'type' => $this->model->_type
        ];
        if ($id != null || $this->model->_id != null) {
            $params['id'] = $id ? $id : $this->model->_id;
        } else {
            throw new Exception("Error Processing Request", 1);
        }
        return $this->client->delete($params);
    }

    /**
     * Bulk Document indexing to ES
     * @param  array  $array  array of documents
     * @return  boolean
     */
    public function bulk($docs = [])
    {
        return $this->client->bulk($docs);
    }
}
