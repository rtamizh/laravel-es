<?php

namespace Tamizh\Phpes\Traits;

use Tamizh\Phpes\ConstraintClause;
use Tamizh\Phpes\AggregationClause;
use Tamizh\Phpes\Scroller;

/**
* All query related to elasticsearch format is going to be formed here
*/
trait ElasticQueryTrait
{
    /**
     * Add the match constraint to constraints array
     * @param  string $field Name of the field
     * @param  string $text  Constraint string
     * @return Tamizh\Phpes\QueryBuilder
     */
    public function match($field, $text, $bool = false)
    {
        array_push($this->constraints, new ConstraintClause($this, 'match', $field, $text));
        return $this;
    }

    /**
     * All bool query callback function handling here.
     * @param  Closure  $closure  closure function of the bool query
     * @param  string  $type  type of the bool
     * @return Tamizh\Phpes\QueryBuilder
     */
    public function bool($closure, $type)
    {
        call_user_func($closure, $query = new static($this->getClient()));
        foreach ($query->constraints as $constraint) {
            $this->bools[] = compact('constraint', 'type');
        }
        return $this;
    }

    /**
     * Must type of bool query
     * @param  Closure  $closure  closure function of the bool query
     * @return Tamizh\Phpes\QueryBuilder
     */
    public function boolMust($closure)
    {
        return $this->bool($closure, 'must');
    }

    /**
     * Must Not type of bool query
     * @param  Closure  $closure  closure function of the bool query
     * @return Tamizh\Phpes\QueryBuilder
     */
    public function boolMustNot($closure)
    {
        return $this->bool($closure, 'must_not');
    }

    /**
     * Should type of bool query
     * @param  Closure  $closure  closure function of the bool query
     * @return Tamizh\Phpes\QueryBuilder
     */
    public function boolShould($closure)
    {
        return $this->bool($closure, 'should');
    }

    /**
     * Should Not type of bool query
     * @param  Closure  $closure  closure function of the bool query
     * @return Tamizh\Phpes\QueryBuilder
     */
    public function boolShouldNot($closure)
    {
        return $this->bool($closure, 'should_not');
    }

    /**
     * Add the terms constraint clause to the constraint array
     * @param  string  $field  Name of the field
     * @param  array  $array  array of values
     * @return Tamizh\Phpes\QueryBuilder
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
     * @return Tamizh\Phpes\QueryBuilder
     */
    public function aggs($closure, $name = 'aggregation')
    {
        call_user_func($closure, $aggs = new AggregationClause($name));
        $this->aggs = $aggs->getAggsArray();
        return $this;
    }

    public function script($script, $lang = "painless")
    {
        $this->script = [
            "lang" => "painless",
            "inline" => $script
        ];
    }

    /**
     * Sort functionality of the query
     * @param  mixed  $field
     * @param  string  $order  order of the sort
     * @param  string  $type   type of the script
     * @return Tamizh\Phpes\QueryBuilder
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
        if ($field instanceof str) {
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
}
