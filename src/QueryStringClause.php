<?php
namespace Tamizh\LaravelEs;

/**
* Basic temoplate class for every Aggregation in elasticsearch query
*/
class QueryStringClause
{
    /**
     * The query term
     * @var string
     */
    public $query = "";

    /**
     * Array of fields
     * @var array
     */
    public $fields = [];

    /**
     * Flag for whether the wildcard need to be analyzed or not
     * @var boolean
     */
    public $analyze_wildcard = false;

    /**
     * Add query parameter to the clause
     * @param  string  $query  querying term
     * @return  Tamizh\LaravelEs\QueryStringClause
     */
    public function query($query)
    {
        $this->query = $query;
        return $this;
    }

    /**
     * Add fields to the clause
     * @param  array  $fields  array of fields
     * @return  Tamizh\LaravelEs\QueryStringClause
     */
    public function fields($fields)
    {
        if (is_array($fields)) {
            $this->fields = $fields;
        } else {
            $this->fields[] = $fields;
        }
        return $this;
    }

    /**
     * Activate the analyze wildcard option in query string
     * @return  Tamizh\LaravelEs\QueryStringClause
     */
    public function analyzeWildcard()
    {
        $this->analyze_wildcard = true;
        return $this;
    }

    public function compile()
    {
        $query_string = array(
            'query' => $this->query,
            'fields' => $this->fields,
            'analyze_wildcard' => $this->analyze_wildcard
        );
        return $query_string;
    }
}
