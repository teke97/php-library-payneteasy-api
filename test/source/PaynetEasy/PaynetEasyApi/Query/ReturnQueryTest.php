<?php

namespace PaynetEasy\PaynetEasyApi\Query;

use PaynetEasy\PaynetEasyApi\Query\Prototype\PaymentQueryTest;
use PaynetEasy\PaynetEasyApi\PaymentData\Payment;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2013-06-11 at 23:03:15.
 */
class ReturnQueryTest extends PaymentQueryTest
{
    /**
     * @var ReturnQuery
     */
    protected $object;

    protected $paymentStatus = Payment::STATUS_RETURN;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new ReturnQuery('_');
    }

    public function testCreateRequestProvider()
    {
        return array(array
        (
            sha1
            (
                self::LOGIN .
                self::CLIENT_ID .
                self::PAYNET_ID .
                 9910 .
                'EUR' .
                self::SIGNING_KEY
            )
        ));
    }

    /**
     * @expectedException \PaynetEasy\PaynetEasyApi\Exception\ValidationException
     * @expectedExceptionMessage Payment must be paid up to return funds
     */
    public function testCreateRequestWithFinishedTransaction()
    {
        $paymentTransaction = $this->getPaymentTransaction();
        $paymentTransaction->getPayment()->setStatus(Payment::STATUS_RETURN);

        $this->object->createRequest($paymentTransaction);
    }

    /**
     * {@inheritdoc}
     */
    protected function getPayment()
    {
        return new Payment(array
        (
            'client_id'             => self::CLIENT_ID,
            'paynet_id'             => self::PAYNET_ID,
            'amount'                => 99.1,
            'currency'              => 'EUR',
            'comment'               => 'cancel payment',
            'status'                => Payment::STATUS_CAPTURE
        ));
    }
}
