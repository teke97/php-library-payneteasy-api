<?php

namespace PaynetEasy\PaynetEasyApi\Query;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-06-11 at 17:21:40.
 */
class QueryFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var QueryFactory
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new QueryFactory;
    }

    public function testGetQuery()
    {
        $config = array
        (
            'login'     => '_',
            'end_point' => '_',
            'control'   => '_'
        );

        $this->assertInstanceOf('PaynetEasy\PaynetEasyApi\Query\CreateCardRefQuery',
                                $this->object->getQuery('create-card-ref', $config));

        $this->assertInstanceOf('PaynetEasy\PaynetEasyApi\Query\ReturnQuery',
                                $this->object->getQuery('return', $config));

        $formQuery = $this->object->getQuery('sale-form', $config);

        $this->assertInstanceOf('PaynetEasy\PaynetEasyApi\Query\FormQuery', $formQuery);
        $this->assertEquals('sale-form', $this->readAttribute($formQuery, 'apiMethod'));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unknown query class 'PaynetEasy\PaynetEasyApi\Query\UnknownQuery' for query with name 'unknown'
     */
    public function testGetQueryWithException()
    {
        $this->object->getQuery('unknown', array());
    }
}