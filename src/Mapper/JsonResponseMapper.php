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

use Wirecard\PaymentSdk\Response\FailureResponse;
use Wirecard\PaymentSdk\Response\InteractionResponse;
use Wirecard\PaymentSdk\Transaction\Transaction;
use Wirecard\PaymentSdk\Exception\MalformedResponseException;
use Wirecard\PaymentSdk\Response\Response;
use SimpleXMLElement;

/**
 * Class JsonResponseMapper
 * @package Wirecard\PaymentSdk\Mapper
 */
class JsonResponseMapper extends ResponseMapper
{
    /**
     * Map the json Response from Wirecard's Payment Page to ResponseObjects
     *
     * @param string $response
     * @param Transaction $transaction
     * @throws \InvalidArgumentException
     * @throws MalformedResponseException
     * @return Response
     */
    public function map($response, Transaction $transaction = null)
    {
        $response = parent::map($response);
        return $this->parseJsonToXml($response);
    }

    /**
     * @param string $jsonResponse
     * @return Response
     */
    public function parseJsonToXml($jsonResponse)
    {
        $payload = json_decode($jsonResponse, true);
        $responseXml = new SimpleXMLElement('<payment></payment>');
        //add statuses
        $this->simpleXmlAppendNode($responseXml, $this->parseResponseStatus($payload));

        if (key_exists('errors', $payload)) {
            $response = new FailureResponse($responseXml);
        } else if (key_exists('payment-redirect-url', $payload)) {
            $response = new InteractionResponse($responseXml, $payload['payment-redirect-url'], true);
        } else {
            throw new MalformedResponseException('Unexpected blabla bla came in!');
        }

        return $response;
    }

    /**
     * @param array $payload
     * @return mixed
     */
    public function parseResponseStatus($payload)
    {
        $statusesXml = new SimpleXMLElement('<statuses></statuses>');
        if (key_exists('errors', $payload)) {
            foreach ($payload['errors'] as $error) {
                $statusXml = new SimpleXMLElement('<status></status>');
                $statusXml->addAttribute('code', $error['code']);
                $statusXml->addAttribute('description', $error['description']);
                //We get no severity in the json response
                $statusXml->addAttribute('severity', '1');
                $this->simpleXmlAppendNode($statusesXml, $statusXml);
            }
        } else {
            $statusXml = new SimpleXMLElement('<status></status>');
            $statusXml->addAttribute('code', '200');
            $statusXml->addAttribute('description', 'success');
        }

        return $statusesXml;
    }
}