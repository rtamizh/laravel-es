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
        $this->aggs_array[$this->name][$this->type]['field'] = $this->field;
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
            $this->aggs_array[$this->name][$this->type]['size'] = $size;
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
            $this->aggs_array[$this->name][$this->type]['min_doc_count'] = $this->min_doc_count;
        }
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
}
