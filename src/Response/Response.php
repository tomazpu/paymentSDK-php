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

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use SimpleXMLElement;
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
abstract class Response
{
    /**
     * @var StatusCollection
     */
    private $statusCollection;

    /**
     * @var string
     */
    private $requestId;

    /**
     * @var boolean
     */
    private $validSignature = true;

    /**
     * @var SimpleXMLElement
     */
    protected $simpleXml;

    /**
     * @var string
     */
    protected $transactionType;

    /**
     * @var string
     */
    protected $operation = null;

    /**
     * @var Basket $basket
     */
    protected $basket;

    /**
     * @var Amount $amount
     */
    protected $requestedAmount;

    /**
     * @var AccountHolder
     */
    protected $accountHolder;

    /**
     * @var AccountHolder
     */
    protected $shipping;

    /**
     * @var CustomFieldCollection
     */
    protected $customFields;

    /**
     * @var Card
     */
    protected $card;

    /**
     * @var XmlResponse|JsonResponse
     */
    private $responseData;

    /**
     * Response constructor.
     * @param SimpleXMLElement|string $responsePayload
     * @throws MalformedResponseException
     */
    public function __construct($responsePayload)
    {
        if ($responsePayload instanceof SimpleXMLElement) {
            $this->responseData = new XmlResponse($responsePayload);
        } else {
            $this->responseData = new JsonResponse($responsePayload);
        }

        $this->statusCollection = $this->responseData->generateStatusCollection();
        $this->setValueForRequestId();
        $this->setRequestedAmount();
        $this->setAccountHolder();
        $this->setShipping();
        $this->setCustomFields();
	    $this->setCard();
	    $this->setBasket();
    }

    /**
     * get the raw response data of the called interface
     *
     * @return string
     */
    public function getRawData()
    {
        return $this->responseData->getRawData();
    }

    /**
     * get the response in a flat array
     *
     * @return array
     */
    public function getData()
    {
        return $this->responseData->getRawData();
    }

    /**
     * @return bool
     */
    public function isValidSignature()
    {
        return $this->validSignature;
    }

    /**
     * @return StatusCollection
     */
    public function getStatusCollection()
    {
        return $this->statusCollection;
    }

    /**
     * @param bool $validSignature
     */
    public function setValidSignature($validSignature)
    {
        $this->validSignature = $validSignature;
    }

    /**
     * @return string
     */
    public function getRequestId()
    {
        return $this->requestId;
    }

    /**
     * Get the transaction type of the response
     *
     * The transaction type is set in the request and should therefore be identical in the response.
     * @return mixed
     */
    public function getTransactionType()
    {
        return $this->transactionType;
    }

    protected function setValueForRequestId()
    {
        $this->requestId = $this->responseData->findElement('request-id');
    }

    public function findElement($element)
    {
        return $this->responseData->findElement($element);
    }

    /**
     * @return CustomFieldCollection
     */
    public function getCustomFields()
    {
        return $this->customFields;
    }

    /**
     * Set the operation executed
     *
     * Necessary mainly for cancel, so that it is possible to see whether
     * there was just a void or a refund.
     * @param string $operation
     * @since 0.6.5
     */
    public function setOperation($operation = null)
    {
        $this->operation = $operation;
    }

    /**
     * @return string|null
     * @since 0.6.5
     */
    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Parse simplexml and create basket object
     *
     * @since 3.0.0
     */
    private function setBasket()
    {
        $basket = new Basket();

	    $this->basket = $basket->parseBasket(
	    	$this->responseData->getPaymentMethod(),
		    $this->responseData->getBasketData(),
		    $this->responseData->getFormat()
	    );
    }

    /**
     * Parse simplexml and create requestedAmount object
     *
     * @since 3.0.0
     */
    private function setRequestedAmount()
    {
        //WPP sends no amount in response
        if ($this instanceof FailureResponse && $this->responseData instanceof JsonResponse) {
            return;
        }

        $amount = $this->responseData->getRequestedAmount();
        if ($amount) {
            $this->requestedAmount = $amount;
        }
    }

    /**
     * @since 3.0.0
     */
    private function setAccountHolder()
    {
        $accountHolder = $this->responseData->getAccountHolder();
        if (isset($accountHolder)) {
            $this->accountHolder = $accountHolder;
        }
    }

    /**
     * @since 3.0.0
     */
    private function setShipping()
    {
        $shipping = $this->responseData->getShipping();
        if (isset($shipping)) {
            $this->shipping = $shipping;
        }
    }

    /**
     * parse simpleXml to load all custom fields
     *
     * @since 3.0.0
     */
    private function setCustomFields()
    {
        $customFields = $this->responseData->getCustomFields();
        if (isset($customFields)) {
            $this->customFields = $customFields;
        }
    }

    /**
     * @return Basket
     * @since 3.0.0
     */
    public function getBasket()
    {
        return $this->basket;
    }

    /**
     * @return AccountHolder
     * @since 3.0.0
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @return AccountHolder
     * @since 3.0.0
     */
    public function getAccountHolder()
    {
        return $this->accountHolder;
    }

    /**
     * @return Amount
     * @since 3.0.0
     */
    public function getRequestedAmount()
    {
        return $this->requestedAmount;
    }

    /**
     * Generate QrCode from authorization code. Available only for payment methods returning
     * authorization-code (e.g. WeChat).
     *
     * Note: This method uses gd2 library. If you can't use gd2, you must set $type to QRCode::OUTPUT_MARKUP_SVG
     * or QRCode::OUTPUT_STRING_TEXT.
     *
     * @param string $type
     * @param int $scale
     *
     * @since 3.1.1
     * @return string
     */
    public function getQrCode($type = QRCode::OUTPUT_IMAGE_PNG, $scale = 5)
    {
        try {
            $outputOptions = new QROptions([
                'outputType' => $type,
                'scale' => $scale,
                'version' => 5
            ]);

            $image = new QRCode($outputOptions);
            return $image->render($this->findElement('authorization-code'));
        } catch (\Exception $ignored) {
            throw new MalformedResponseException('Authorization-code not found in response.');
        }
    }

    public function getPaymentDetails()
    {
        return new PaymentDetails($this->simpleXml);
    }

    public function getTransactionDetails()
    {
        return new TransactionDetails($this->simpleXml);
    }

    public function getCard()
    {
        return $this->card;
    }

    public function setCard()
    {
    	$cardData = $this->responseData->getCard();

        $this->card = new Card($cardData);
    }

    /**
     * @return array
     * @throws MalformedResponseException
     */
    protected function findProviderTransactionId()
    {
        return $this->responseData->findProviderTransactionId();
    }
}
