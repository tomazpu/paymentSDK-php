<?php
// # WPP Configuration

// The payment SDK needs some basic configuration regarding connectivity and merchant account IDs.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';

use Wirecard\PaymentSdk\Config;
use Wirecard\PaymentSdk\Config\PaymentMethodConfig;
use Wirecard\PaymentSdk\Transaction\WPPTransaction;

// ## Connection

// The basic configuration requires the base URL for Wirecard and the username and password for the HTTP requests.
$baseUrl = 'https://wpp-test.wirecard.com';
$httpUser = '70000-APIDEMO-CARD';
$httpPass = 'ohysS0-dvfMx';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');

// ### WPP Hosted Payment Page

$wppMAID = '28d4938b-d0d6-4c4a-b591-fb63175de53e';
$wppKey = 'dbc5a498-9a66-43b9-bf1d-a618dd399684';
$wppConfig = new PaymentMethodConfig(WPPTransaction::NAME, $wppMAID, $wppKey);
$config->add($wppConfig);
