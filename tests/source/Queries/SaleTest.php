<?php
namespace PaynetEasy\Paynet\Queries;

use \PaynetEasy\Paynet\Data\Order;
use \PaynetEasy\Paynet\Data\Customer;
use \PaynetEasy\Paynet\Data\Card;
use \PaynetEasy\Paynet\Data\RecurrentCard;

/**
 * Test class for Sale.
 * Generated by PHPUnit on 2012-06-14 at 11:50:13.
 */
class SaleTest extends QueryTest
{
    /**
     * Test class
     * @var string
     */
    protected $class            = 'Sale';

    public function getTestData()
    {
        $customer               = new Customer
        (
            array
            (
                'first_name'    => 'Vasya',
                'last_name'     => 'Pupkin',
                'email'         => 'vass.pupkin@example.com',
                'address'       => '2704 Colonial Drive',
                'birthday'      => '112681',
                'city'          => 'Houston',
                'state'         => 'TX',
                'zip_code'      => '1235',
                'country'       => 'US',
                'phone'         => '660-485-6353',
                'cell_phone'    => '660-485-6353'
            )
        );

        $card                   = new Card
        (
            array
            (
                'card_printed_name'         => 'Vasya Pupkin',
                'credit_card_number'        => '4485 9408 2237 9130',
                'expire_month'              => '12',
                'expire_year'               => '14',
                'cvv2'                      => '084'
            )
        );

        $order                  = new Order
        (
            array
            (
                'order_code'                => 'CLIENT-112233',
                'desc'                      => 'This is test order',
                'amount'                    => 0.99,
                'currency'                  => 'USD',
                'ipaddress'                 => '127.0.0.1',
                'site_url'                  => 'http://example.com'
            )
        );

        return array($customer, $card, $order);
    }

    /**
     * Checking the output parameters
     */
    public function testRequest()
    {
        list($customer, $card, $order) = $this->getTestData();

        $this->query->setOrder($order);
        $this->query->setCustomer($customer);

        if($card instanceof RecurrentCard)
        {
            $this->query->setRecurrentCard($card);
        }
        else
        {
            $this->query->setCard($card);
        }

        $this->transport->response  = array
        (
            'type'              => 'async-response',
            'status'            => 'processing',
            'paynet-order-id'   => 'PAYNET-112233',
            'merchant-order-id' => 'CLIENT-112233',
            'serial-number'     => md5(time())
        );

        $this->query->process();

        $request                = array
        (
            'client_orderid'    => 'CLIENT-112233',
            'order_desc'        => 'This is test order',
            'card_printed_name' => 'Vasya Pupkin',
            'first_name'        => 'Vasya',
            'last_name'         => 'Pupkin',
            'birthday'          => '112681',
            'address1'          => '2704 Colonial Drive',
            'city'              => 'Houston',
            'state'             => 'TX',
            'zip_code'          => '1235',
            'country'           => 'US',
            'phone'             => '660-485-6353',
            'cell_phone'        => '660-485-6353',
            'email'             => 'vass.pupkin@example.com',
            'amount'            => 0.99,
            'currency'          => 'USD',
            'credit_card_number' => '4485940822379130',
            'expire_month'      => '12',
            'expire_year'       => '14',
            'cvv2'              => '084',
            'ipaddress'         => '127.0.0.1',
            'site_url'          => 'http://example.com',
            'control'           => sha1
            (
                $this->config['end_point'].
                'CLIENT-112233'.
                '99'.
                'vass.pupkin@example.com'.
                $this->config['control']
            ),
            'redirect_url'      => $this->config['redirect_url'],
            'server_callback_url' => $this->config['server_callback_url']
        );

        if($card instanceof RecurrentCard)
        {
            $request['cardrefid']   = $card->cardrefid();

            $request['control']     = sha1
            (
                $this->config['end_point'].
                'CLIENT-112233'.
                '99'.
                $card->cardrefid().
                $this->config['control']
            );

            unset($request['card_printed_name']);
            unset($request['cvv2']);
            unset($request['credit_card_number']);
            unset($request['expire_month']);
            unset($request['expire_year']);
            unset($request['first_name']);
            unset($request['last_name']);
            unset($request['birthday']);
            unset($request['address1']);
            unset($request['city']);
            unset($request['state']);
            unset($request['country']);
            unset($request['zip_code']);
            unset($request['email']);
            unset($request['phone']);
            unset($request['cell_phone']);
        }

        foreach($request as $key => $value)
        {
            $this->assertTrue(!empty($this->transport->request), 'Request property no exists: '.$key);
            $this->assertEquals($value, $this->transport->request[$key], "$key not equal '$value'");
        }
    }

    public function providerProcess()
    {
        list($customer, $card, $order) = $this->getTestData();

        $dataset                = array();

        // PROCESSING
        $response               = array
        (
            'type'              => 'async-response',
            'status'            => 'processing',
            'paynet-order-id'   => 'PAYNET-112233',
            'merchant-order-id' => 'CLIENT-112233',
            'serial-number'     => md5(time())
        );

        $assert                 = array
        (
            'state'             => Query::STATE_PROCESSING,
            'status'            => null
        );

        $dataset[]              = array($order, $customer, $card, $response, $assert);

        // VALIDATION-ERROR
        $response               = array
        (
            'type'              => 'validation-error',
            'serial-number'     => md5(time()),
            'error-message'     => 'validation-error message',
            'error-code'        => '1000'
        );

        $assert                 = array
        (
            'state'             => Query::STATE_END,
            'status'            => Query::STATUS_ERROR,
            'error_message'     => $response['error-message'],
            'error_code'        => $response['error-code'],
            'exception'         => true
        );

        $dataset[]              = array($order, $customer, $card, $response, $assert);

        // FILTERED
        $response               = array
        (
            'type'              => 'status-response',
            'status'            => 'filtered',
            'paynet-order-id'   => 'PAYNET-112233',
            'merchant-order-id' => 'CLIENT-112233',
            'serial-number'     => md5(time()),
            'error-message'     => 'test filtered message',
            'error-code'        => '8876'
        );

        $assert                 = array
        (
            'state'             => Query::STATE_END,
            'status'            => Query::STATUS_DECLINED,
            'error_message'     => $response['error-message'],
            'error_code'        => $response['error-code']
        );

        $dataset[]              = array($order, $customer, $card, $response, $assert);

        // Type = error
        $response               = array
        (
            'type'              => 'error',
            'error_message'     => 'test type error message',
            'error_code'        => '5'
        );

        $assert                 = array
        (
            'state'             => Query::STATE_END,
            'status'            => Query::STATUS_ERROR,
            'error_message'     => $response['error_message'],
            'error_code'        => $response['error_code'],
            'exception'         => true
        );

        $dataset[]              = array($order, $customer, $card, $response, $assert);

        // ERROR in STATUS
        $response               = array
        (
            'type'              => 'async-response',
            'status'            => 'error',
            'paynet-order-id'   => 'PAYNET-112233',
            'merchant-order-id' => 'CLIENT-112233',
            'serial-number'     => md5(time()),
            'error-message'     => 'test error message',
            'error-code'        => '2'
        );

        $assert                 = array
        (
            'state'             => Query::STATE_END,
            'status'            => Query::STATUS_ERROR,
            'error_message'     => $response['error-message'],
            'error_code'        => $response['error-code'],
            'exception'         => true
        );

        $dataset[]              = array($order, $customer, $card, $response, $assert);

        return $dataset;
    }

    /**
     * @dataProvider providerProcess
     */
    public function testProcess($order, $customer = null, $card = null, $server_response = null, $assert = null)
    {
        $this->transport->response  = $server_response;

        $this->query->setOrder($order);
        $this->query->setCustomer($customer);
        if($card instanceof RecurrentCard)
        {
            $this->query->setRecurrentCard($card);
        }
        else
        {
            $this->query->setCard($card);
        }

        parent::testProcess($assert);
    }

    /**
     * @dataProvider \PaynetEasy\Paynet\Queries\StatusTest::providerProcess
     */
    public function testStatus($order, $server_response, $assert)
    {
        $this->transport->response          = array
        (
            'type'              => 'async-response',
            'status'            => 'processing',
            'paynet-order-id'   => 'PAYNET-112233',
            'merchant-order-id' => 'CLIENT-112233',
            'serial-number'     => md5(time())
        );

        list($customer, $card, $order_full) = $this->getTestData();

        $this->query->setOrder($order_full);
        $this->query->setCustomer($customer);
        if($card instanceof RecurrentCard)
        {
            $this->query->setRecurrentCard($card);
        }
        else
        {
            $this->query->setCard($card);
        }

        $this->query->process();

        $this->transport->response          = $server_response;

        parent::testProcess($assert);
    }

    public function providerCallback()
    {
        list($customer, $card, $order) = $this->getTestData();

        $dataset                = array();

        // SALE
        $response               = array
        (
            'type'              => 'sale',
            'status'            => 'approved',
            'orderid'           => 'PAYNET-112233',
            'merchant_order'    => 'CLIENT-112233',
            'client_orderid'    => 'CLIENT-112233',
            'descriptor'        => 'http://descriptor.example.com/',
            // status + orderid + client_orderid + merchant-control
            'control'           => sha1
            (
                'approved'.
                'PAYNET-112233'.
                'CLIENT-112233'.
                self::CONTROL_CODE
            )
        );

        $assert                 = array
        (
            'state'             => Query::STATE_END,
            'status'            => Query::STATUS_APPROVED
        );

        $dataset[]              = array($order, $customer, $card, $response, $assert);

        // PROCESSING
        $response               = array
        (
            'type'              => 'sale',
            'status'            => 'processing',
            'orderid'           => 'PAYNET-112233',
            'merchant_order'    => 'CLIENT-112233',
            'client_orderid'    => 'CLIENT-112233',
            'descriptor'        => 'http://descriptor.example.com/',
            // status + orderid + client_orderid + merchant-control
            'control'           => sha1
            (
                'processing'.
                'PAYNET-112233'.
                'CLIENT-112233'.
                self::CONTROL_CODE
            )
        );

        $assert                 = array
        (
            'state'             => Query::STATE_PROCESSING,
            'status'            => null
        );

        $dataset[]              = array($order, $customer, $card, $response, $assert);

        // DECLINE
        $response               = array
        (
            'type'              => 'sale',
            'status'            => 'declined',
            'orderid'           => 'PAYNET-112233',
            'merchant_order'    => 'CLIENT-112233',
            'client_orderid'    => 'CLIENT-112233',
            'descriptor'        => 'http://descriptor.example.com/',
            'error_message'     => 'decline message',
            'error_code'        => '1000000',
            'control'           => sha1
            (
                'declined'.
                'PAYNET-112233'.
                'CLIENT-112233'.
                self::CONTROL_CODE
            )
        );

        $assert                 = array
        (
            'state'             => Query::STATE_END,
            'status'            => Query::STATUS_DECLINED,
            'error_message'     => 'decline message',
            'error_code'        => '1000000'
        );

        $dataset[]              = array($order, $customer, $card, $response, $assert);

        // FILTERED
        $response               = array
        (
            'type'              => 'sale',
            'status'            => 'filtered',
            'orderid'           => 'PAYNET-112233',
            'merchant_order'    => 'CLIENT-112233',
            'client_orderid'    => 'CLIENT-112233',
            'descriptor'        => 'http://descriptor.example.com/',
            'error_message'     => 'filtered message',
            'error_code'        => '1000000',
            'control'           => sha1
            (
                'filtered'.
                'PAYNET-112233'.
                'CLIENT-112233'.
                self::CONTROL_CODE
            )
        );

        $assert                 = array
        (
            'state'             => Query::STATE_END,
            'status'            => Query::STATUS_DECLINED,
            'error_message'     => 'filtered message',
            'error_code'        => '1000000'
        );

        $dataset[]              = array($order, $customer, $card, $response, $assert);

        // Error
        $response               = array
        (
            'type'              => 'sale',
            'status'            => 'error',
            'orderid'           => 'PAYNET-112233',
            'merchant_order'    => 'CLIENT-112233',
            'client_orderid'    => 'CLIENT-112233',
            'descriptor'        => 'http://descriptor.example.com/',
            'error_message'     => 'error message',
            'error_code'        => '1',
            'control'           => sha1
            (
                'error'.
                'PAYNET-112233'.
                'CLIENT-112233'.
                self::CONTROL_CODE
            )
        );

        $assert                 = array
        (
            'state'             => Query::STATE_END,
            'status'            => Query::STATUS_ERROR,
            'error_message'     => 'error message',
            'error_code'        => '1',
            'exception'         => true
        );

        $dataset[]              = array($order, $customer, $card, $response, $assert);

        return $dataset;
    }

    /**
     * @dataProvider providerCallback
     */
    public function testCallback($order, $customer, $card, $callback, $assert)
    {
        $this->transport->response          = array
        (
            'type'              => 'async-response',
            'status'            => 'processing',
            'paynet-order-id'   => 'PAYNET-112233',
            'merchant-order-id' => 'CLIENT-112233',
            'serial-number'     => md5(time())
        );

        $this->query->setOrder($order);
        $this->query->setCustomer($customer);
        if($card instanceof RecurrentCard)
        {
            $this->query->setRecurrentCard($card);
        }
        else
        {
            $this->query->setCard($card);
        }

        $this->query->process();

        $this->transport->response          = array
        (
            'type'              => 'status-response',
            'status'            => 'processing',
            'html'              => '<HTML>',
            'paynet-order-id'   => $order->getPaynetOrderId(),
            'serial-number'     => md5(time())
        );

        $this->query->process();

        parent::testProcess($assert, $callback);
    }
}

?>