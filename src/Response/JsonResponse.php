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
	const FORMAT = 'json';

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

    /**
     * Get the collection of status returned by Wirecard's Payment Processing Gateway
     *
     * @return mixed|StatusCollection
     * @since 3.5.0
     */
    public function generateStatusCollection()
    {
        $collection = new StatusCollection();

        if (isset($this->json->{'errors'})) {
            $collection = $this->processStatusCollectionFromErrors(
                $this->json->{'errors'},
                $collection
            );
        } else {
            $collection = $this->processStatusCollectionFromStatuses(
                $this->json->{'payment'}->{'statuses'},
                $collection
            );
        }

        return $collection;
    }

    /**
     * @param $statuses
     * @param $collection
     * @return mixed
     * @since 3.5.0
     */
    private function processStatusCollectionFromErrors($statuses, $collection)
    {
        foreach ($statuses as $status) {
            $collection->add(
                new Status(
                    $status->{'code'},
                    $status->{'description'},
                    ''//we get no sevirity in the return
                )
            );
        }

        return $collection;
    }

    /**
     * @param $statuses
     * @param $collection
     * @return mixed
     * @since 3.5.0
     */
    private function processStatusCollectionFromStatuses($statuses, $collection)
    {
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

    /**
     * @return Amount
     * @since 3.5.0
     */
    public function getRequestedAmount()
    {
        return new Amount(
            $this->json->{'payment'}->{'requested-amount'}->{'value'},
            $this->json->{'payment'}->{'requested-amount'}->{'currency'}
        );
    }

    /**
     * @return null|AccountHolder
     * @since 3.5.0
     */
    public function getAccountHolder()
    {
        $accountHolder = $this->getAccountHolderFromJson();

        return $accountHolder;
    }

    /**
     * @return null|AccountHolder
     * @since 3.5.0
     */
    public function getShipping()
    {
        $shipping = $this->getAccountHolderFromJson('shipping');

        return $shipping;
    }

    /**
     * @param string $from
     * @return null|AccountHolder
     * @since 3.5.0
     */
    private function getAccountHolderFromJson($from = 'account-holder')
    {
	    $accountHolderFields = array (
		    'first-name' => 'setFirstName',
		    'last-name' => 'setLastName',
		    'email' => 'setEmail',
		    'phone' => 'setPhone',
		    'address' => 'setAddress',
		    'crmid' => 'setCrmId',
		    'date-of-birth' => 'setDateOfBirth',
		    'gender' => 'setGender',
		    'shipping' => 'setShippingMethod',
		    'social-security-number' => 'setSocialSecurityNumber' //is newer send back by WPP
	    );

	    if (isset($this->json->{'payment'}->{$from})) {
		    $accountHolder = new AccountHolder();
		    foreach ($accountHolderFields as $property => $setter) {
			    if (isset($this->json->{'payment'}->{$from}->{$property})) {
				    $accountHolder->{$setter}($this->json->{'payment'}->{'account-holder'}->{$property});
			    }
		    }
		    return $accountHolder;
	    }

	    return null;
    }

    /**
     * @return CustomFieldCollection
     * @since 3.5.0
     */
    public function getCustomFields()
    {
        $customFields = new CustomFieldCollection();

	    if (isset($this->json->{'payment'}->{'custom-fields'})) {
		    foreach ($this->json->{'payment'}->{'custom-fields'}->{'custom-field'} as $field) {
		    	if (isset($field->{'field-name'}) && isset($field->{'field-value'})) {
		    		$name = substr((string) $field->{'field-name'}, strlen(CustomField::PREFIX));
		    		$value = $field->{'field-value'};
		    		$customFields->add(new CustomField($name, $value));
			    }
		    }
	    }

        return $customFields;
    }

    /**
     * @param $element
     * @return string
     * @since 3.5.0
     */
    public function findElement($element)
    {
        if (isset($this->json->{'payment'}->{$element})) {
            if (is_object($this->json->{'payment'}->{$element})) {
                return (string)$this->json->{'payment'}->{$element}->{'value'};
            } else {
                return (string)$this->json->{'payment'}->{$element};
            }
        }

        throw new MalformedResponseException('Missing ' . $element . ' in response.');
    }

    /**
     * @param $entity
     * @param $property
     * @return null
     * @since 3.5.0
     */
    public function getValueFromJson($entity, $property)
    {
        if (isset($this->json->{'payment'}->{$entity}->{$property})) {
            return $this->json->{'payment'}->{$entity}->{$property};
        }

        return null;
    }

    /**
     * @return array
     * @throws MalformedResponseException
     * @since 3.5.0
     */
    public function findProviderTransactionId()
    {
        $result = [];
        foreach ($this->json->{'payment'}->{'statuses'}->{'status'} as $status) {
            if (isset($status->{'provider-transaction-id'})) {
                $result[] = $status->{'provider-transaction-id'};
            }
        }

        return (array)$result;
    }

    /**
     * @return mixed
     * @since 3.5.0
     */
	public function getCard()
	{
		if (isset($this->json->{'payment'}->{'card-token'})) {
			return $this->json->{'payment'}->{'card-token'};
		}
	}

    /**
     * @return mixed
     * @since 3.5.0
     */
	public function getBasketData()
	{
		if (isset($this->json->{'payment'}->{'order-items'}->{'order-item'}) && count($this->json->{'payment'}->{'order-items'}->{'order-item'}) > 0) {
			return $this->json->{'payment'}->{'order-items'}->{'order-item'};
		}
	}

    /**
     * @return mixed
     * @since 3.5.0
     */
	public function getPaymentMethod()
	{
		return $this->json->{'payment'}->{'payment-methods'}->{'payment-method'}[0]->{'name'};
	}

    /**
     * @return string
     * @since 3.5.0
     */
	public function getFormat()
	{
		return $this::FORMAT;
	}

    /**
     * @return mixed
     * @since 3.5.0
     */
	public function getDataForDetails()
	{
		$response = $this->json->{'payment'};
		if (!is_string($response->{'merchant-account-id'})) {
			$response->{'merchant-account-id'} = $this->json->{'payment'}->{'merchant-account-id'}->{'value'};
		}
		$requestedAmount = $this->json->{'payment'}->{'requested-amount'};
		if (is_object($requestedAmount)) {
			$response->{'currency'} = $requestedAmount->{'currency'};
			$response->{'requested-amount'} = $requestedAmount->{'value'};
		}

		return $response;
	}
}