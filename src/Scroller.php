<?php

namespace Tamizh\Phpes;

use \Iterator;
use Tamizh\Phpes\QueryBuilder;

class Scroller extends QueryBuilder implements Iterator
{
    /**
     * Scroll Id
     * @var string
     */
    protected $scroll_id;

    /**
     * Elastic Query Builder
     * @var Tamizh\Phpes\QueryBuilder
     */
    protected $builder;

    /**
     * iterator result
     * @var array
     */
    protected $result;

    public function __construct($builder)
    {
        $this->scroll_id = 0;
        $this->builder = $builder;
    }

    /**
     * Next function of the iterator
     * @return function [description]
     */
    public function next()
    {
    }

    /**
     * Current function of itereator
     * @return array  result array
     */
    public function current()
    {
        return $this->result;
    }

    /**
     * Return the current scroll id
     * @return string  scroll id
     */
    public function key()
    {
        return $this->scroll_id;
    }

    /**
     * Rewind function of iterator
     */
    public function rewind()
    {
        $this->scroll_id = null;
    }

    /**
     * Valid function of iterator
     * @return  Tamizh\Phpes\Scroller
     */
    public function valid()
    {
        if (!$this->scroll_id) {
            $result = $this->builder->getRaw();
            $this->scroll_id = $result['_scroll_id'];
            $this->result = $this->builder->getCollection($result);
        } else {
            $this->result = $this->builder->postScroll($this->scroll_id);
        }
        if (count($this->result) == 0) {
            return false;
        }
        return true;
    }
}
