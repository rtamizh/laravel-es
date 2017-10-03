<?php
namespace Tamizh\LaravelEs;

/**
* Basic temoplate class for every Aggregation in elasticsearch query
*/
class AggregationClause
{
    /**
     * Aggregation type
     * @var string
     */
    protected $type;

    /**
     * Aggregation name
     * @var string
     */
    protected $name;

    /**
     * Aggregation field
     * @var string
     */
    protected $field;

    /**
     * Aggregation Size
     * @var string
     */
    protected $size;

    /**
     * Min document that need to be matched for aggregation
     * @var integer
     */
    protected $min_doc_count;

    /**
     * Time interval that used for date related aggregations
     * @var string
     */
    protected $interval;

    /**
     * Aggregation Array
     * @var array
     */
    protected $aggs_array = [];

    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * Term aggregation
     * @param  string  $field  Name of the field
     * @return Tamizh\LaravelEs\AggregationClause
     */
    public function terms($field)
    {
        $this->type = 'terms';
        $this->field = $field;
        $this->aggs_array[$this->type]['field'] = $this->field;
        return $this;
    }

    /**
     * Term aggregation
     * @return Tamizh\LaravelEs\AggregationClause
     */
    public function topHits()
    {
        $this->type = 'top_hits';
        $this->aggs_array[$this->type] = [];
        return $this;
    }

    /**
     * To get the distinct count of a field
     * @param  string $field Field Name
     * @return  Tamizh\LaravelEs\AggregationClause
     */
    public function cardinality($field)
    {
        $this->field = $field;
        $this->type = 'cardinality';
        $this->aggs_array[$this->type]['field'] = $this->field;
        return $this;
    }

    /**
     * To get the max value of the field
     * @param  string $field Field Name
     * @return  Tamizh\LaravelEs\AggregationClause
     */
    public function max($field)
    {
        $this->field = $field;
        $this->type = 'max';
        $this->aggs_array[$this->type]['field'] = $this->field;
        return $this;
    }

    /**
     * To get the min value of the field
     * @param  string $field Field Name
     * @return  Tamizh\LaravelEs\AggregationClause
     */
    public function min($field)
    {
        $this->field = $field;
        $this->type = 'min';
        $this->aggs_array[$this->type]['field'] = $this->field;
        return $this;
    }

    public function sumBucket($path)
    {
        $this->type = 'sum_bucket';
        $this->aggs_array[$this->type]['buckets_path'] = $path;
        return $this;
    }

    /**
     * Gives the sum value of a field
     * @param  string $field Field Name
     * @return  Tamizh\LaravelEs\AggregationClause
     */
    public function sum($field)
    {
        $this->field = $field;
        $this->type = "sum";
        $this->aggs_array[$this->type]['field'] = $this->field;
        return $this;
    }

    /**
     * Size of the aggregation
     * @param  integer  $size  Size of the aggregation result
     * @return Tamizh\LaravelEs\AggregationClause
     */
    public function size($size)
    {
        $this->size = $size;
        if ($this->type != null) {
            $this->aggs_array[$this->type]['size'] = $size;
        }
        return $this;
    }

    /**
     * Min document count needed for aggregation
     * @param  integer  $value  Min document count
     * @return  Tamizh\LaravelEs\AggregationClause
     */
    public function minDocCount($value)
    {
        $this->min_doc_count = $value;
        if ($this->type != null) {
            $this->aggs_array[$this->type]['min_doc_count'] = $this->min_doc_count;
        }
        return $this;
    }

    /**
     * Date Histogram aggregation (Ex - Monthly interval)
     * @param  string  $field  Name of the field
     * @return  Tamizh\LaravelEs\AggregationClause
     */
    public function dateHistogram($field)
    {
        $this->type = 'date_histogram';
        $this->field = $field;
        $this->aggs_array[$this->type]['field'] = $this->field;
        $this->aggs_array[$this->type]['interval'] = 'month';
        return $this;
    }

    /**
     * Set interval value that used for time related aggregations
     * @param  string  $value  interval strings (Ex - minute, hour, week, month)
     * @return  Tamizh\LaravelEs\AggregationClause
     */
    public function interval($value)
    {
        $this->interval = $value;
        if ($this->type != null) {
            $this->aggs_array[$this->type]['interval'] = $this->interval;
        }
        return $this;
    }

    /**
     * To add sub aggregation to current aggregation
     * @param  Closure  $closure  Function for aggregation
     * @param  string $name  Aggregation Name
     * @return Tamizh\LaravelEs\AggregationClause
     */
    public function aggs($closure, $name = "aggregation")
    {
        call_user_func($closure, $aggs = new AggregationClause($name));
        $this->aggs_array['aggs'][$name] = $aggs->getAggsArray();
        return $this;
    }

    /**
     * Returns the get aggregation array
     * @return array  Aggregation array
     */
    public function getAggsArray()
    {
        return $this->aggs_array;
    }

    public function order($aggs_field, $order)
    {
        if ($this->type != null) {
            $this->aggs_array[$this->type]['order'][$aggs_field] = $order;
        }
        return $this;
    }
}
