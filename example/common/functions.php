<?php

use PaynetEasy\PaynetEasyApi\PaymentData\Payment;
use PaynetEasy\PaynetEasyApi\PaymentData\QueryConfig;
use PaynetEasy\PaynetEasyApi\Transport\Response;
use PaynetEasy\PaynetEasyApi\PaymentProcessor;

/**
 * Функция возвращает конфигурацию для выполнения запросов
 *
 * @return array
 */
$getConfig = function()
{
    return new QueryConfig(array
    (
        /**
         * Точка входа для аккаунта мерчанта, выдается при подключении
         *
         * @see http://wiki.payneteasy.com/index.php/PnE:Introduction#PaynetEasy_Objects
         * @see http://wiki.payneteasy.com/index.php/PnE:Introduction#Endpoint
         */
        'end_point'                 =>  253,
        /**
         * Логин мерчанта, выдается при подключении
         *
         * @see http://wiki.payneteasy.com/index.php/PnE:Introduction#PaynetEasy_Users
         */
        'login'                     => 'rp-merchant1',
        /**
         * Ключ мерчанта для подписывания запросов, выдается при подключении
         */
        'control'                   => '3FD4E71A-D84E-411D-A613-40A0FB9DED3A',
        /**
         * URL на который пользователь будет перенаправлен после окончания запроса
         *
         * @see http://wiki.payneteasy.com/index.php/PnE:Sale_Transactions#3D_redirect
         * @see http://wiki.payneteasy.com/index.php/PnE:Payment_Form_integration#Payment_Form_final_redirect
         */
        'redirect_url'              => "http://{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}?stage=processCustomerReturn",
        /**
         * URL на который пользователь будет перенаправлен после окончания запроса
         *
         * @see http://wiki.payneteasy.com/index.php/PnE:Merchant_Callbacks
         */
        'server_callback_url'       => "http://{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}?stage=processPaynetEasyCallback",
        /**
         * Режим работы библиотеки: sandbox, production
         *
         * @see \PaynetEasy\PaynetEasyApi\PaymentData\QueryConfig::$gatewayMode
         * @see \PaynetEasy\PaynetEasyApi\PaymentData\QueryConfig::$allowedGatewayModes
         * @see \PaynetEasy\PaynetEasyApi\PaymentData\QueryConfig::setGatewayUrl()
         * @see \PaynetEasy\PaynetEasyApi\PaymentData\QueryConfig::getGatewayUrl()
         */
        'gateway_mode'              => QueryConfig::GATEWAY_MODE_SANDBOX,
        /**
         * Ссылка на шлюз PaynetEasy для режима работы sandbox
         *
         * @see \PaynetEasy\PaynetEasyApi\PaymentData\QueryConfig::getGatewayUrl()
         */
        'gateway_url_sandbox'       => 'https://sandbox.domain.com/paynet/api/v2/',
        /**
         * Ссылка на шлюз PaynetEasy для режима работы production
         *
         * @see \PaynetEasy\PaynetEasyApi\PaymentData\QueryConfig::getGatewayUrl()
         */
        'gateway_url_production'    => 'https://payment.domain.com/paynet/api/v2/'
    ));
};

/**
 * Функция загружает платеж из сессии
 *
 * @return      Payment        Платеж
 */
$loadPayment = function()
{
    if (!empty($_SESSION['payment']))
    {
        return unserialize($_SESSION['payment']);
    }
};

/**
 * Функция сохраняет платеж в сессию
 *
 * @param       Payment        $payment         Платеж
 */
$savePayment = function(Payment $payment)
{
    $_SESSION['payment'] = serialize($payment);
};

/**
 * Функция выводит страницу с текстов "платеж обрабатывается",
 * которая автоматически обновляется через определенное время
 *
 * @see ./common/waitPage.html
 */
$displayWaitPage = function()
{
    $formAction = "http://{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}?stage=updateStatus";
    include(__DIR__ . '/waitPage.php');
    exit;
};

/**
 * Функция выводит html, содержащийся в ответе от Paynet,
 * который переадресует пользователя на 3D-авторизацию
 *
 * @see http://wiki.payneteasy.com/index.php/PnE:Sale_Transactions#3D_Sale_transaction_diagram
 *
 * @param       Response       $response       Ответ от сервера Paynet
 */
$displayResponseHtml = function(Response $response)
{
    // выводим полученную форму для редиректа на 3D-авторизацию
    print $response->getHtml();
    exit;
};

/**
 * Функция переадресует пользователя на URL, содержащийся в ответе от Paynet.
 * который ведет на платежную форму на стороне Paynet
 *
 * @see http://wiki.payneteasy.com/index.php/PnE:Payment_Form_integration#General_Payment_Form_Process_Flow
 */
$redirectToResponseUrl = function(Response $response)
{
    // Переадресуем пользователя на платежную форму
    header("Location: {$response->getRedirectUrl()}");
    exit;
};

/**
 * Функция выводит статус платежа после того, как его обработка завершена
 *
 * @param       Payment        $payment         Платеж
 */
$displayEndedPayment = function(Payment $payment)
{
    // платеж завершен, выводим его статус
    print "<pre>";
    print "Payment processing finished.\n";
    print "Payment state: '{$payment->getProcessingStage()}'.\n";
    print "Payment status: '{$payment->getStatus()}'.\n";
    print "</pre>";

    session_destroy();
};

/**
 * Функция выводит сообщение и трейс пойманного исключения
 *
 * @param       Exception       $exception      Исключение
 */
$displayException = function(Exception $exception)
{
    // поймано исключение, выведем его сообщение и трейс
    print "<pre>";
    print "Exception catched.\n";
    print "Exception message: '{$exception->getMessage()}'.\n";
    print "Exception traceback: \n{$exception->getTraceAsString()}\n";
    print "</pre>";
};

/**
 * Метод создает сервис для обработки платежей
 * и назначает обработчики для разных событий, происходящих при обработке платежа
 *
 * @see ./common/functions.php
 * @see PaynetEasy\PaynetEasyApi\PaymentProcessor::executeQuery()
 * @see PaynetEasy\PaynetEasyApi\PaymentProcessor::executeCallback()
 */
$getPaymentProcessor = function() use ($displayException,
                                       $savePayment,
                                       $displayWaitPage,
                                       $redirectToResponseUrl,
                                       $displayResponseHtml,
                                       $displayEndedPayment)
{
    return new PaymentProcessor(array
    (
        PaymentProcessor::HANDLER_CATCH_EXCEPTION     => $displayException,
        PaymentProcessor::HANDLER_SAVE_PAYMENT        => $savePayment,
        PaymentProcessor::HANDLER_STATUS_UPDATE       => $displayWaitPage,
        PaymentProcessor::HANDLER_REDIRECT            => $redirectToResponseUrl,
        PaymentProcessor::HANDLER_SHOW_HTML           => $displayResponseHtml,
        PaymentProcessor::HANDLER_FINISH_PROCESSING   => $displayEndedPayment
    ));
};