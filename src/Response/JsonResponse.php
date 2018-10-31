<?php
/**
 * Shop System SDK - Terms of Use
 *
 * The SDK offered are provided free of charge by Wirecard AG and are explicitly not part
 * of the Wirecard AG range of products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 3 (GPLv3) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard AG does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the SDK at their own risk. Wirecard AG does not guarantee their full
 * functionality neither does Wirecard AG assume liability for any disadvantages related to
 * the use of the SDK. Additionally, Wirecard AG does not guarantee the full functionality
 * for customized shop systems or installed SDK of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the SDK's functionality before starting productive
 * operation.
 *
 * By installing the SDK into the shop system the customer agrees to these terms of use.
 * Please do not use the SDK if you do not agree to these terms of use!
 */

namespace Wirecard\PaymentSdk\Response;

use Wirecard\PaymentSdk\Entity\AccountHolder;
use Wirecard\PaymentSdk\Entity\Amount;
use Wirecard\PaymentSdk\Entity\Basket;
use Wirecard\PaymentSdk\Entity\Card;
use Wirecard\PaymentSdk\Entity\CustomField;
use Wirecard\PaymentSdk\Entity\CustomFieldCollection;
use Wirecard\PaymentSdk\Entity\PaymentDetails;
use Wirecard\PaymentSdk\Entity\Status;
use Wirecard\PaymentSdk\Entity\StatusCollection;
use Wirecard\PaymentSdk\Entity\TransactionDetails;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use Wirecard\PaymentSdk\TransactionService;

/**
 * Class Response
 * @package Wirecard\PaymentSdk\Response
 */
class JsonResponse
{
    /**
     * @var string
     */
    protected $json;

    /**
     * Response constructor.
     * @param string $json
     * @throws MalformedResponseException
     */
    public function __construct($json)
    {
        $this->json = $json;
    }

    public function generateStatusCollection()
    {
        $collection = new StatusCollection();

        $statuses = $this->json->{'payment'}->{'statuses'};
        if (count($statuses->{'status'}) > 0) {
            foreach ($statuses->{'status'} as $status) {
                if ((string)$status->{'code'} !== '') {
                    $code = (string)$status->{'code'};
                } else {
                    throw new MalformedResponseException('Missing status code in response.');
                }
                if ((string)$status->{'description'} !== '') {
                    $description = (string)$status->{'description'};
                } else {
                    throw new MalformedResponseException('Missing status description in response.');
                }
                if ((string)$status->{'severity'} !== '') {
                    $severity = (string)$status->{'severity'};
                } else {
                    throw new MalformedResponseException('Missing status severity in response.');
                }
                $status = new Status($code, $description, $severity);
                $collection->add($status);
            }
        }

        return $collection;
    }

    public function getRequestedAmount()
    {
        return new Amount(
            $this->json->{'payment'}->{'requested-amount'}->{'value'},
            $this->json->{'payment'}->{'requested-amount'}->{'currency'}
        );
    }

    public function getAccountHolder()
    {
        $accounHolderFields = array (
            'first-name' => 'setFirstName',
            'last-name' => 'setLastName',
            'first-name' => 'setLastName'
        );
        $accountHolder = new AccountHolder();
/*
        foreach ($accounHolderFields as $property => $setter) {
            if ($this->json->{'payment'}->{'account-holder'}->{$property}) {
                $accounHolderFields->$$setter();
            }
        }*/
        //@TODO for all other things for accountholder

        return $accountHolder;
    }

    public function getShipping()
    {
        $shipping = new AccountHolder();
        //@TODO for all other things for shipping

        return $shipping;
    }

    public function getCustomFields()
    {
        $customFields = new CustomFieldCollection();
        //@TODO for all other things for customfields

        return $customFields;
    }

    public function findElement($element)
    {
        if (isset($this->json->{$element})) {
            return (string)$this->json->{$element};
        }

        throw new MalformedResponseException('Missing ' . $element . ' in response.');
    }

    public function getValueFromJson($entity, $property)
    {
        if (isset($this->json->{'payment'}->{$entity}->{$property})) {
            return $this->json->{'payment'}->{$entity}->{$property};
        }

        return null;
    }
}