<?php
namespace Tamizh\Phpes;

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

    public function __construct($parentQuery, $type, $field, $condition)
    {
        $this->type = $type;
        $this->field = $field;
        $this->condition = $condition;

        parent::__construct(
            $parentQuery->getClient()
        );
    }
}
