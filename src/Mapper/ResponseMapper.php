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

namespace Wirecard\PaymentSdk\Mapper;

use SimpleXMLElement;
use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Entity\FormFieldMap;
use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\FormInteractionResponse;
use Wirecard\PaymentSdk\Response\Response;
use Wirecard\PaymentSdk\Response\SuccessResponse;
use Wirecard\PaymentSdk\Transaction\Transaction;

/**
 * Class ResponseMapper
 * @package Wirecard\PaymentSdk\Mapper
 */
abstract class ResponseMapper
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Transaction
     */
    protected $transaction;

    /**
     * ResponseMapper constructor.
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    protected function simpleXmlAppendNode(SimpleXMLElement $to, SimpleXMLElement $from)
    {
        $toDom = dom_import_simplexml($to);
        $fromDom = dom_import_simplexml($from);
        $toDom->appendChild($toDom->ownerDocument->importNode($fromDom, true));
    }

    /**
     * @param string $response
     * @throws \Wirecard\PaymentSdk\Exception\MalformedResponseException
     * @throws \InvalidArgumentException
     * @return Response
     */
    public function mapInclSignature($response, $signature = null, $secret = null)
    {
        $result = $this->map($response);
        if (isset($this->xmlResponse)) {
            $validSignature = $this->validateSignature($this->xmlResponse);
        } else {
            $validSignature = $this->validateSignature($response, $signature, $secret);
        }
        $result->setValidSignature($validSignature);

        return $result;
    }

    public function map($response, Transaction $transaction = null)
    {
        // If the response is encoded, we need to first decode it.
        $decodedResponse = base64_decode($response);
        return (base64_encode($decodedResponse) === $response) ? $decodedResponse : $response;
    }

    public function mapSeamlessResponse($payload, $url)
    {
        $this->simpleXml = new SimpleXMLElement('<payment></payment>');

        $this->simpleXml->addChild("merchant-account-id", $payload['merchant_account_id']);
        $this->simpleXml->addChild("transaction-id", $payload['transaction_id']);
        $this->simpleXml->addChild("transaction-state", $payload['transaction_state']);
        $this->simpleXml->addChild("transaction-type", $payload['transaction_type']);
        $this->simpleXml->addChild("payment-method", $payload['payment_method']);
        $this->simpleXml->addChild("request-id", $payload['request_id']);

        if (array_key_exists('acs_url', $payload) && array_key_exists('pareq', $payload)) {
            $threeD = new SimpleXMLElement('<three-d></three-d>');
            $threeD->addChild('acs-url', $payload['acs_url']);
            $threeD->addChild('pareq', $payload['pareq']);
            $threeD->addChild('cardholder-authentication-status', $payload['cardholder_authentication_status']);
            $this->simpleXmlAppendNode($this->simpleXml, $threeD);
        }

        if (array_key_exists('parent_transaction_id', $payload)) {
            $this->simpleXml->addChild('parent-transaction-id', $payload['parent_transaction_id']);
        }

        // parse statuses
        $statuses = [];

        foreach ($payload as $key => $value) {
            if (strpos($key, 'status_') === 0) {
                if (strpos($key, 'status_code_') === 0) {
                    $number = str_replace('status_code_', '', $key);
                    $statuses[$number]['code'] = $value;
                }
                if (strpos($key, 'status_severity_') === 0) {
                    $number = str_replace('status_severity_', '', $key);
                    $statuses[$number]['severity'] = $value;
                }
                if (strpos($key, 'status_description_') === 0) {
                    $number = str_replace('status_description_', '', $key);
                    $statuses[$number]['description'] = $value;
                }
            }
        }

        if (count($statuses) > 0) {
            $statusesXml = new SimpleXMLElement('<statuses></statuses>');

            foreach ($statuses as $status) {
                $statusXml = new SimpleXMLElement('<status></status>');
                $statusXml->addAttribute('code', $status['code']);
                $statusXml->addAttribute('description', $status['description']);
                $statusXml->addAttribute('severity', $status['severity']);
                $this->simpleXmlAppendNode($statusesXml, $statusXml);
            }
            $this->simpleXmlAppendNode($this->simpleXml, $statusesXml);
        }

        if (array_key_exists('acs_url', $payload)) {
            $response = new FormInteractionResponse($this->simpleXml, $payload['acs_url']);

            $fields = new FormFieldMap();
            $fields->add('TermUrl', $url);
            $fields->add('PaReq', (string)$payload['pareq']);

            $fields->add(
                'MD',
                base64_encode(json_encode([
                    'enrollment-check-transaction-id' => $response->getTransactionId(),
                    'operation-type' => $payload['transaction_type']
                ]))
            );

            $response->setFormFields($fields);

            return $response;
        } else {
            if ($payload['transaction_state'] == 'success') {
                return new SuccessResponse($this->simpleXml);
            } else {
                return new FailureResponse($this->simpleXml);
            }
        }
    }
}
