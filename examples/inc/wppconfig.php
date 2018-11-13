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
$baseUrl = 'https://wpp-wdcee-test.wirecard.com';
$httpUser = '21797_CEEDEMOSHOP';
$httpPass = '4c0m0t3t';

// The configuration is stored in an object containing the connection settings set above.
// A default currency can also be provided.
$config = new Config\Config($baseUrl, $httpUser, $httpPass, 'EUR');

// ### WPP Hosted Payment Page
// WPP Select Page needs merchant-account-resolver-category instead of merchant-account-id and secret
$wppConfig = new PaymentMethodConfig(WPPTransaction::NAME);
$wppConfig->setResolverCategory('DEMOSHOP');
$config->add($wppConfig);
