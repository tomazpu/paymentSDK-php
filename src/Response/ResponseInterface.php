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

/**
 * Interface ResponseInterface
 * @package Wirecard\PaymentSdk\Response
 *
 * Represents a response with all mandatory getter
 */
interface ResponseInterface
{
    /**
     * @return string
     * @since 3.5.0
     */
    public function getRawData();

    /**
     * @return array
     * @since 3.5.0
     */
    public function getData();

    /**
     * @return Wirecard\PaymentSdk\Entity\StatusCollection
     * @since 3.5.0
     */
    public function generateStatusCollection();

    /**
     * @param string $element
     * @return string
     * @since 3.5.0
     */
    public function findElement($element);

    /**
     * @return null|\Wirecard\PaymentSdk\Entity\Amount
     * @since 3.5.0
     */
    public function getRequestedAmount();

    /**
     * @return \Wirecard\PaymentSdk\Entity\AccountHolder
     * @since 3.5.0
     */
    public function getAccountHolder();

    /**
     * @return \Wirecard\PaymentSdk\Entity\AccountHolder
     * @since 3.5.0
     */
    public function getShipping();

    /**
     * @return \Wirecard\PaymentSdk\Entity\CustomFieldCollection
     * @since 3.5.0
     */
    public function getCustomFields();

    /**
     * @return array
     * @since 3.5.0
     */
    public function findProviderTransactionId();

    /**
     * @return mixed
     * @since 3.5.0
     */
    public function getCard();

    /**
     * @return mixed
     * @since 3.5.0
     */
    public function getBasketData();

    /**
     * @return string
     * @since 3.5.0
     */
    public function getPaymentMethod();

    /**
     * @return string
     * @since 3.5.0
     */
    public function getFormat();

    /**
     * @return mixed
     * @since 3.5.0
     */
    public function getDataForDetails();
}