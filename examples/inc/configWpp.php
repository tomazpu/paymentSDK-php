<?php
// # Configuration

// The payment SDK needs some basic configuration regarding connectivity and merchant account IDs.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Config\CreditCardConfig;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Transaction\BancontactTransaction;
use Wirecard\PaymentSdk\Transaction\EpsTransaction;
use Wirecard\PaymentSdk\Transaction\PayPalTransaction;
use Wirecard\PaymentSdk\Transaction\PaysafecardTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInstallmentTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayInvoiceTransaction;
use Wirecard\PaymentSdk\Transaction\RatepayDirectDebitTransaction;
use Wirecard\PaymentSdk\Transaction\MasterpassTransaction;
use Wirecard\PaymentSdk\Transaction\AlipayCrossborderTransaction;
use Wirecard\PaymentSdk\Transaction\PoiPiaTransaction;
use Wirecard\PaymentSdk\Transaction\PtwentyfourTransaction;
use Wirecard\PaymentSdk\Transaction\CreditCardMotoTransaction;
use Wirecard\PaymentSdk\Transaction\UpopTransaction;

// ## Connection

// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://wpp-test.wirecard.com';
$httpUser = '70000-APITEST-AP';
$httpPass = 'qD2wzQ_hrc!8';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');


// ## Payment methods

// Each payment method can be configured with an individual merchant account ID and the corresponding key.
// The configuration object for Credit Card is a little different than other payment methods and can be
// instantiated without any parameters. If you wish to omit non-3-D transactions you can just leave out the
// maid and secret in the default CreditCardConfig. However if you want to use non-3-D transactions you have two
// ways of setting the credentials. First via setting the parameters maid and secret -

// ### Credit Card Non-3-D

$creditcardConfig = new CreditCardConfig();

// - second via using this specific setter.
$creditcardConfig->setNonThreeDCredentials(
    '53f2895a-e4de-4e82-a813-0d87a10e55e6',
    'dbc5a498-9a66-43b9-bf1d-a618dd399684'
);

// Define the limit to allow the maximum amount for a non-3-D transaction, all amounts above this value will be done as
// 3d secure transaction
$creditcardConfig->addNonThreeDMaxLimit(new Amount(100.0, 'EUR'));

// Define the limit to allow the minimum amount for a 3-D transaction, all amounts below or equal the limit will be done
// as non-3-D transaction
$creditcardConfig->addThreeDMinLimit(new Amount(50.0, 'EUR'));

// Amounts larger than threeDMinLimit and smaller or equal nonThreeDLimit will first be tried as 3-D-Secure transaction and
// will fallback on error as non-3D transaction

// ### Credit Card 3-D

$creditcardConfig->setThreeDCredentials(
    '508b8896-b37d-4614-845c-26bf8bf2c948',
    'dbc5a498-9a66-43b9-bf1d-a618dd399684'
);

$config->add($creditcardConfig);