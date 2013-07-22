<?php

namespace PaynetEasy\PaynetEasyApi\Workflow;

use PaynetEasy\PaynetEasyApi\Transport\GatewayClient;
use PaynetEasy\PaynetEasyApi\Query\QueryFactory;
use PaynetEasy\PaynetEasyApi\Callback\CallbackFactory;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-06-15 at 16:43:36.
 */
class WorkflowFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var WorkflowFactory
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new WorkflowFactory(new GatewayClient('_'),
                                            new QueryFactory,
                                            new CallbackFactory);
    }

    public function testGetWorkflow()
    {
        $this->assertInstanceOf('PaynetEasy\PaynetEasyApi\Workflow\MakeRebillWorkflow',
                                $this->object->getWorkflow('make-rebill'));

        $this->assertInstanceOf('PaynetEasy\PaynetEasyApi\Workflow\SaleWorkflow',
                                $this->object->getWorkflow('sale'));

        $formWorflow = $this->object->getWorkflow('sale-form');

        $this->assertInstanceOf('PaynetEasy\PaynetEasyApi\Workflow\FormWorkflow', $formWorflow);
        $this->assertEquals('sale-form', $this->readAttribute($formWorflow, 'initialApiMethod'));
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unknown workflow class 'PaynetEasy\PaynetEasyApi\Workflow\UnknownWorkflow' for workflow with name 'unknown'
     */
    public function testGetWorkflowWithException()
    {
        $this->object->getWorkflow('unknown');
    }
}
