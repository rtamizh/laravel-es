<?php
namespace Tamizh\LaravelEs;

/**
* Basic temoplate class for every constraints in elasticsearch query
*/
class ConstraintClause extends QueryBuilder
{
    /**
     * constraint type
     * @var string
     */
    protected $type;

    /**
     * Constraint field
     * @var string
     */
    protected $field;

    /**
     * Constraint text
     * @var string
     */
    protected $condition;

    /**
     * Search query type [multi_match]
     * @var string
     */
    protected $search_clause_type;

    public function __construct($parentQuery, $type, $field, $condition, $search_clause_type)
    {
        $this->type = $type;
        $this->field = $field;
        $this->condition = $condition;
        $this->search_clause_type = $search_clause_type;

        parent::__construct(
            $parentQuery->getClient()
        );
    }
}
