<?php
// # WPP payment transaction

// This example displays the usage of WPP as payment method within the paymentSDK-php.

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../inc/common.php';
require __DIR__ . '/../inc/wppconfig.php';
//Header design
require __DIR__ . '/../inc/header.php';

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Transaction\WPPTransaction;
use Wirecard\PaymentSdk\TransactionService;


// ### Transaction related objects

// Use the amount object as amount which has to be paid by the consumer.
$amount = new Amount(12.59, 'EUR');

// The redirect URLs determine where the consumer should be redirected by WPP after approval/cancellation.
$redirectUrls = new Redirect(getUrl('return.php?status=success'), getUrl('return.php?status=cancel'), getUrl('return.php?status=failure'));

// The account holder last name is required for credit.
$accountHolder = new AccountHolder();
$accountHolder->setLastName('Doe');
$accountHolder->setFirstName('John');

// ## Transaction

// The WPPTransaction object holds all transaction relevant data for the payment process.
// The required fields are: amount, success and cancel redirect URL-s
$transaction = new WPPTransaction();
$transaction->setRedirect($redirectUrls);
$transaction->setAmount($amount);
$transaction->setAccountHolder($accountHolder);

// ### Transaction Service

// The service is used to execute the payment operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->reserve($transaction);

// ## Response handling has to be implemented here

//Footer design
require __DIR__ . '/../inc/footer.php';
