<?php

namespace Tamizh\LaravelEs\Traits;

use Tamizh\LaravelEs\ConstraintClause;
use Tamizh\LaravelEs\AggregationClause;
use Tamizh\LaravelEs\QueryStringClause;
use Tamizh\LaravelEs\Scroller;

/**
* All query related to elasticsearch format is going to be formed here
*/
trait ElasticQueryTrait
{
    /**
     * Add the match constraint to constraints array
     * @param  string $field Name of the field
     * @param  string $text  Constraint string
     * @return Tamizh\LaravelEs\QueryBuilder
     */
    public function match($field, $text, $bool = false)
    {
        array_push($this->constraints, new ConstraintClause($this, 'match', $field, $text));
        return $this;
    }

    /**
     * Add the multi_match constraint to constraints array
     * @param  array $fields Name of the fields
     * @param  string $text  Constraint string
     * @param  string $type  Search type
     * @return Tamizh\LaravelEs\QueryBuilder
     */
    public function multiMatch($field, $text, $type = "best_fields")
    {
        array_push($this->constraints, new ConstraintClause($this, 'multi_match', $field, $text, $type));
        return $this;
    }

    /**
     * Add the match phrase constraint to constraints array
     * @param  string $field Name of the field
     * @param  string $text  Constraint string
     * @return Tamizh\LaravelEs\QueryBuilder
     */
    public function matchPhrase($field, $text)
    {
        array_push($this->constraints, new ConstraintClause($this, 'match_phrase', $field, $text));
        return $this;
    }

    /**
     * Add the match phrase prefix constraint to constraints array
     * @param  string $field Name of the field
     * @param  string $text  Constraint string
     * @return Tamizh\LaravelEs\QueryBuilder
     */
    public function matchPhrasePrefix($field, $text)
    {
        array_push($this->constraints, new ConstraintClause($this, 'match_phrase_prefix', $field, $text));
        return $this;
    }

    /**
     * All bool query callback function handling here.
     * @param  Closure  $closure  closure function of the bool query
     * @param  string  $type  type of the bool
     * @return Tamizh\LaravelEs\QueryBuilder
     */
    public function bool($closure, $type)
    {
        call_user_func($closure, $query = new static($this->getClient()));
        foreach ($query->constraints as $constraint) {
            $this->bools[] = compact('constraint', 'type');
        }
        if ($query->query_string) {
            $query_string = array(
                'query_string' => $query->query_string
            );
            $this->bools[] = compact('query_string', 'type');
        }
        if ($query->script) {
            $script = array(
                'script' => array(
                    'script' => $query->script
                )
            );
            $this->bools[] = compact('script', 'type');
        }
        if ($query->bools) {
            $bool = $query->compile()['body']['query']['bool'];
            $this->bools[] = compact('bool', 'type');
        }
        return $this;
    }

    /**
     * Must type of bool query
     * @param  Closure  $closure  closure function of the bool query
     * @return Tamizh\LaravelEs\QueryBuilder
     */
    public function boolMust($closure)
    {
        return $this->bool($closure, 'must');
    }

    /**
     * Must Not type of bool query
     * @param  Closure  $closure  closure function of the bool query
     * @return Tamizh\LaravelEs\QueryBuilder
     */
    public function boolMustNot($closure)
    {
        return $this->bool($closure, 'must_not');
    }

    /**
     * Should type of bool query
     * @param  Closure  $closure  closure function of the bool query
     * @return Tamizh\LaravelEs\QueryBuilder
     */
    public function boolShould($closure)
    {
        return $this->bool($closure, 'should');
    }

    /**
     * Should Not type of bool query
     * @param  Closure  $closure  closure function of the bool query
     * @return Tamizh\LaravelEs\QueryBuilder
     */
    public function boolShouldNot($closure)
    {
        return $this->bool($closure, 'should_not');
    }

    /**
     * Add the terms constraint clause to the constraint array
     * @param  string  $field  Name of the field
     * @param  array  $array  array of values
     * @return Tamizh\LaravelEs\QueryBuilder
     */
    public function terms($field, $array)
    {
        array_push($this->constraints, new ConstraintClause($this, 'terms', $field, $array));
        return $this;
    }

    /**
     * Aggregations query
     * @param  Closure  $closure  Function for aggregation
     * @param  string  $name  Name of the aggregation
     * @return Tamizh\LaravelEs\QueryBuilder
     */
    public function aggs($closure, $name = 'aggregation')
    {
        call_user_func($closure, $aggs = new AggregationClause($name));
        $this->aggs[$name] = $aggs->getAggsArray();
        return $this;
    }

    /**
     * Script functionality of elasticsearch
     * @param  string  $script  Script that need to be applied to query
     * @param  string  $lang    Language of the script [optional]
     * @return Tamizh\LaravelEs\QueryBuilder
     */
    public function script($script, $lang = "painless")
    {
        $this->script = [
            "lang" => "painless",
            "inline" => $script
        ];
        return $this;
    }

    /**
     * Sort functionality of the query
     * @param  mixed  $field
     * @param  string  $order  order of the sort
     * @param  string  $type   type of the script
     * @return Tamizh\LaravelEs\QueryBuilder
     */
    public function sort($field, $order = "desc", $type = "number")
    {
        if ($field instanceof \Closure) {
            call_user_func($field, $query = new static($this->client));
            $this->sort = [
                '_script' => [
                    'type' => $type,
                    'order' => $order,
                    'script' => $query->script
                ]
            ];
        }
        if (is_string($field)) {
            $this->sort = [
                $field => [
                    'order' => $order
                ]
            ];
        }
        return $this;
    }

    public function scroll($limit = '1m')
    {
        $this->scroll_param = $limit;
        return new Scroller($this);
    }

    /**
     * Highlight feature of elasticsearch
     * @param  string  $fields    Fields list that need to be highlighted (comma seperated)
     * @param  string  $pre_tags  Html tag that will be added before the highlighted word
     * @param  string  $post_tags Html tag that will be added before the highlighted word
     * @return Tamizh\LaravelEs\QueryBuilder
     */
    public function highlight($fields, $pre_tags = null, $post_tags = null)
    {
        $this->highlight['fields'][$fields] = new \stdClass();
        if ($pre_tags) {
            $this->highlight['pre_tags'] = $pre_tags;
        }
        if ($post_tags) {
            $this->highlight['post_tags'] = $post_tags;
        }
        return $this;
    }

    public function filter($closure)
    {
        call_user_func($closure, $query = new static($this->getClient()));
        $this->filter = $query->compile()['body']['query'];
        return $this;
    }

    /**
     * query string functionality
     * @param  closure  $closure  Query string closure
     * @return  Tamizh\LaravelEs\QueryBuilder
     */
    public function queryString($closure)
    {
        call_user_func($closure, $query_string = new QueryStringClause($name));
        $this->query_string = $query_string->compile();
        return $this;
    }

    public function exists($field)
    {
        array_push($this->constraints, new ConstraintClause($this, 'exists', 'field', $field));
        return $this;
    }

    public function range($field, $range)
    {
        array_push($this->constraints, new ConstraintClause($this, 'range', $field, $range));
        return $this;
    }

    public function rangeBetween($field, $min, $max)
    {
        array_push($this->constraints, new ConstraintClause($this, 'range', $field, ['gte'=>$min, 'lte'=>$max]));
        return $this;
    }

    public function fromType($type)
    {
        $this->fromType = $type;
        return $this;
    }

    public function fromIndex($index)
    {
        $this->fromIndex;
        return $this;
    }
}
