<?php

use Tamizh\LaravelEs\QueryBuilder;

/**
* Elastic Query Trait Test Class
*/
class ElasticQueryTraitTest extends PHPUnit_Framework_TestCase
{
    protected $builder;

    public function __construct()
    {
        $this->builder = new QueryBuilder(null);
    }

    public function testMatch()
    {
        $this->builder->match('field', 'text');
        $this->assertEquals([
            'body' => [
                'query' => [
                    'match' => [
                        'field' => 'text'
                    ]
                ]
            ]
        ], $this->builder->compile());
    }

    public function testMatchPhrase()
    {
        $this->builder->matchPhrase('field', 'match texts');
        $this->assertEquals([
            'body' => [
                'query' => [
                    'match_phrase' => [
                        'field' => 'match texts'
                    ]
                ]
            ]
        ], $this->builder->compile());
    }

    public function testBoolMust()
    {
        $this->builder->boolMust(function ($query) {
            $query->match('field', 'text');
        });
        $this->assertEquals([
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'match' => [
                                    'field' => 'text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $this->builder->compile());
    }

    public function testBoolMustWithMultipleConstraint()
    {
        $this->builder->boolMust(function ($query) {
            $query->match('field', 'text')
                ->matchPhrase('field', 'match texts');
        });
        $this->assertEquals([
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'match' => [
                                    'field' => 'text'
                                ]
                            ],
                            [
                                'match_phrase' => [
                                    'field' => 'match texts'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $this->builder->compile());
    }

    public function testBoolMustNot()
    {
        $this->builder->boolMustNot(function ($query) {
            $query->match('field', 'text');
        });
        $this->assertEquals([
            'body' => [
                'query' => [
                    'bool' => [
                        'must_not' => [
                            [
                                'match' => [
                                    'field' => 'text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $this->builder->compile());
    }

    public function testBoolShould()
    {
        $this->builder->boolShould(function ($query) {
            $query->match('field', 'text');
        });
        $this->assertEquals([
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => [
                            [
                                'match' => [
                                    'field' => 'text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $this->builder->compile());
    }

    public function testBoolShouldNot()
    {
        $this->builder->boolShouldNot(function ($query) {
            $query->match('field', 'text');
        });
        $this->assertEquals([
            'body' => [
                'query' => [
                    'bool' => [
                        'should_not' => [
                            [
                                'match' => [
                                    'field' => 'text'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ], $this->builder->compile());
    }

    public function testTerms()
    {
        $this->builder->terms('field', ['match1', 'match2', 'match3']);
        $this->assertEquals([
            'body' => [
                'query' => [
                    'terms' => [
                        'field' => ['match1', 'match2', 'match3']
                    ]
                ]
            ]
        ], $this->builder->compile());
    }

    public function testAggsTerms()
    {
        $this->builder->aggs(function ($query) {
            $query->terms('field')
                ->size(200)
                ->minDocCount(10);
        });
        $this->assertEquals([
            'body' => [
                'query' => [
                ],
                'aggs' => [
                    'aggregation' => [
                        'terms' => [
                            'field' => 'field',
                            'size' => 200,
                            'min_doc_count' => 10
                        ]
                    ]
                ]
            ]
        ], $this->builder->compile());
    }

    public function testSort()
    {
        $this->builder->sort('field', 'desc');
        $this->assertEquals([
            'body' => [
                'query' => [],
                'sort' => [
                    'field' => [
                        'order' => 'desc'
                    ]
                ]
            ]
        ], $this->builder->compile());
    }

    public function testSortWithScript()
    {
        $this->builder->sort(function ($query) {
            $query->script('return doc["count"].value + doc["total"].value');
        });
        $this->assertEquals([
            'body' => [
                'query' => [],
                'sort' => [
                    '_script' => [
                        'type' => 'number',
                        'order' => 'desc',
                        'script' => [
                            'lang' => 'painless',
                            'inline' => 'return doc["count"].value + doc["total"].value'
                        ]
                    ]
                ]
            ]
        ], $this->builder->compile());
    }

    public function testHighlight()
    {
        $this->builder->highlight('fields', '<span class="highlight">', '</span>');
        $this->assertEquals([
            'body' => [
                'query' => [],
                'highlight' => [
                    'fields' => [
                        'fields' => new \stdClass()
                    ],
                    'pre_tags' => '<span class="highlight">',
                    'post_tags' => '</span>'
                ]
            ]
        ], $this->builder->compile());
    }
}
