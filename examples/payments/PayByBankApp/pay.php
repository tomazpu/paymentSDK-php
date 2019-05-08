<?php
// # Purchase for Pay By Bank App

// To reserve and capture an amount for a credit card

// ## Required objects

// To include the necessary files, we use the composer for PSR-4 autoloading.
require __DIR__ . '/../../../vendor/autoload.php';
require __DIR__ . '/../../inc/common.php';
require __DIR__ . '/../../configuration/config.php';
//Header design
require __DIR__ . '/../../inc/header.php';

use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Redirect;
use Wirecard\PaymentSdk\Entity\Device;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Transaction\PayByBankAppTransaction;
use Wirecard\PaymentSdk\TransactionService;

// ### Transaction related objects

// Create a amount object as amount which has to be paid by the consumer.
$amount = new Amount(1.23, 'GBP');

// Create a consumer device.
$device = new Device();
$device->setType("pc");
$device->setOperatingSystem("windows");

// Create the mandatory fields needed for Pay By Bank App(merchant string, transaction type, Delivery type).
$customFields = new CustomFieldCollection();
$customFields->add(prepareCustomField('zapp.in.MerchantRtnStrng', '123'));
$customFields->add(prepareCustomField('zapp.in.TxType', 'PAYMT'));
$customFields->add(prepareCustomField('zapp.in.DeliveryType', 'DELTAD'));

// The redirect URLs determine where the consumer should be redirected after approval/cancellation.
$redirectUrls = new Redirect(getUrl('return.php?status=success'), getUrl('return.php?status=cancel'));

// As soon as the transaction status changes, a server-to-server notification will get delivered to this URL.
$notificationUrl = getUrl('notify.php');

// ## Transaction

// The Pay By Bank App transaction holds all transaction relevant data for the pay process.
$transaction = new PayByBankAppTransaction();

// ### Mandatory fields

$transaction->setAmount($amount);
$transaction->setDevice($device);
$transaction->setCustomFields($customFields);
$transaction->setRedirect($redirectUrls);
$transaction->setNotificationUrl($notificationUrl);

// ### Optional fields


// ### Transaction Service

// The service is used to execute the payment operation itself. A response object is returned.
$transactionService = new TransactionService($config);
$response = $transactionService->pay($transaction);

// ## Response handling
// The response from the service can be used for disambiguation.
// Since a redirect for successful transactions is defined, a InteractionResponse is returned
// if the transaction was successful.
if ($response instanceof InteractionResponse) {
        die("<meta http-equiv='refresh' content='0;url={$response->getRedirectUrl()}'>");

// The failure state is represented by a FailureResponse object.
// In this case the returned errors should be stored in your system.
} elseif ($response instanceof FailureResponse) {
// In our example we iterate over all errors and echo them out. You should display them as
// error, warning or information based on the given severity.
    foreach ($response->getStatusCollection() as $status) {
        /**
         * @var $status \Wirecard\PaymentSdk\Entity\Status
         */
        $severity = ucfirst($status->getSeverity());
        $code = $status->getCode();
        $description = $status->getDescription();
        echo sprintf('%s with code %s and message "%s" occurred.<br>', $severity, $code, $description);
    }
}
//Footer design
require __DIR__ . '/../../inc/footer.php';

