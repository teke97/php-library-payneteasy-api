<?php

namespace PaynetEasy\Paynet\Query;

use PaynetEasy\Paynet\Transport\Response;
use PaynetEasy\Paynet\OrderData\Order;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-06-12 at 16:43:22.
 */
class CreateCardRefQueryTest extends SaleQueryTest
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new CreateCardRefQuery($this->getConfig());
    }

    public function testCreateRequestProvider()
    {
        return array(array
        (
            sha1
            (
                self::LOGIN .
                self::CLIENT_ORDER_ID .
                self::PAYNET_ORDER_ID .
                self::SIGN_KEY
            ),
            'recurrent'
        ));
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage card-ref-id undefined
     */
    public function testProcessResponseWithException()
    {
        $order = $this->getOrder();

        $this->object->processResponse($order, new Response(array
        (
            'type'              => 'create-card-ref-response',
            'status'            => 'processing',
            'paynet-order-id'   =>  self::PAYNET_ORDER_ID,
            'merchant-order-id' =>  self::CLIENT_ORDER_ID,
            'serial-number'     => md5(time())
        )));
    }

    /**
     * @dataProvider testProcessResponseApprovedProvider
     */
    public function testProcessResponseApproved(array $response)
    {
        $order = $this->getOrder();

        $this->object->processResponse($order, new Response($response));

        $this->assertTrue($order->hasRecurrentCardFrom());
        $this->assertInstanceOf('\PaynetEasy\Paynet\OrderData\RecurrentCard', $order->getRecurrentCardFrom());
        $this->assertOrderStates($order, Order::STATE_END, Order::STATUS_APPROVED);
        $this->assertFalse($order->hasErrors());
    }

    public function testProcessResponseApprovedProvider()
    {
        return array(array(array
        (
            'type'              => 'create-card-ref-response',
            'status'            => 'approved',
            'card-ref-id'       =>  self::RECURRENT_CARD_FROM_ID,
            'serial-number'     =>  md5(time())
        )));
    }

    public function testProcessResponseFilteredProvider()
    {
        return array(array(array
        (
            'type'              => 'create-card-ref-response',
            'status'            => 'filtered',
            'card-ref-id'       =>  self::RECURRENT_CARD_FROM_ID,
            'serial-number'     =>  md5(time()),
            'error-message'     => 'test filtered message',
            'error-code'        => '8876'
        )));
    }

    public function testProcessResponseProcessingProvider()
    {
        return array(array(array
        (
            'type'              => 'create-card-ref-response',
            'status'            => 'processing',
            'card-ref-id'       =>  self::RECURRENT_CARD_FROM_ID,
            'serial-number'     => md5(time())
        )));
    }

    public function testProcessResponseErrorProvider()
    {
        return array(array(
        array
        (
            'type'              => 'create-card-ref-response',
            'status'            => 'error',
            'card-ref-id'       =>  self::RECURRENT_CARD_FROM_ID,
            'serial-number'     =>  md5(time()),
            'error-message'     => 'test error message',
            'error-code'        => '2'
        ),
        array
        (
            'type'              => 'validation-error',
            'serial-number'     =>  md5(time()),
            'error-message'     => 'validation-error message',
            'error-code'        => '1000'
        ),
        array
        (
            'type'              => 'error',
            'error_message'     => 'test type error message',
            'error_code'        => '5'
        )));
    }

    /**
     * {@inheritdoc}
     */
    protected function getOrder()
    {
        return parent::getOrder()
            ->setState(Order::STATE_END)
            ->setStatus(Order::STATUS_APPROVED);
    }
}
