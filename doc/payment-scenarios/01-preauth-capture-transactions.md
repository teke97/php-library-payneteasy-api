# Preauth/Capture transactions

Список запросов сценария:
* [Запрос "preauth"](#preauth)
* [Запрос "capture"](#capture)
* [Запрос "status"](#status)

## Общие положения

* В данной статье описывается исключительно работа с библиотекой. Полная информация о выполнении Preauth/Capture transactions расположена в [статье в wiki PaynetEasy](http://wiki.payneteasy.com/index.php/PnE:Preauth/Capture_Transactions).
* Описание правил валидации можно найти в описании метода **[Validator::validateByRule()](../library-internals/02-validator.md#validateByRule)**.
* Описание работы с цепочками свойств можно найти в описании класса **[PropertyAccessor](../library-internals/03-property-accessor.md)**

## <a name="preauth"></a> Запрос "preauth"

##### Обязательные параметры запроса

Поле запроса        |Цепочка свойств платежа        |Правило валидации
--------------------|-------------------------------|-----------------
client_orderid      |clientPaymentId                |Validator::ID
order_desc          |description                    |Validator::LONG_STRING
amount              |amount                         |Validator::AMOUNT
currency            |currency                       |Validator::CURRENCY
address1            |billingAddress.firstLine       |Validator::MEDIUM_STRING
city                |billingAddress.city            |Validator::MEDIUM_STRING
zip_code            |billingAddress.zipCode         |Validator::ZIP_CODE
country             |billingAddress.country         |Validator::COUNTRY
phone               |billingAddress.phone           |Validator::PHONE
ipaddress           |customer.ipAddress             |Validator::IP
email               |customer.email                 |Validator::EMAIL
card_printed_name   |creditCard.cardPrintedName     |Validator::LONG_STRING
credit_card_number  |creditCard.creditCardNumber    |Validator::CREDIT_CARD_NUMBER
expire_month        |creditCard.expireMonth         |Validator::MONTH
expire_year         |creditCard.expireYear          |Validator::YEAR
cvv2                |creditCard.cvv2                |Validator::CVV2

##### Необязательные параметры запроса

Поле запроса        |Цепочка свойств платежа        |Правило валидации
--------------------|-------------------------------|-----------------
first_name          |customer.firstName             |Validator::MEDIUM_STRING
last_name           |customer.lastName              |Validator::MEDIUM_STRING
ssn                 |customer.ssn                   |Validator::SSN
birthday            |customer.birthday              |Validator::DATE
state               |billingAddress.state           |Validator::COUNTRY
cell_phone          |billingAddress.cellPhone       |Validator::PHONE
site_url            |siteUrl                        |Validator::URL
destination         |destination                    |Validator::LONG_STRING

[Пример выполнения запроса preauth](../../example/preauth.php)

## <a name="capture"></a> Запрос "capture"

##### Обязательные параметры запроса

Поле запроса        |Цепочка свойств платежа        |Правило валидации
--------------------|-------------------------------|-----------------
client_orderid      |clientPaymentId                |Validator::ID
orderid             |paynetPaymentId                |Validator::ID

[Пример выполнения запроса capture](../../example/capture.php)

## <a name="status"></a> Запрос "status"

##### Обязательные параметры запроса

Поле запроса        |Цепочка свойств платежа        |Правило валидации
--------------------|-------------------------------|-----------------
client_orderid      |clientPaymentId                |Validator::ID
orderid             |paynetPaymentId                |Validator::ID

[Пример выполнения запроса status](../../example/status.php)