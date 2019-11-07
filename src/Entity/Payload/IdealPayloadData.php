<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Entity\Payload;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Constant\PayloadFields;
use Wirecard\PaymentSdk\Transaction\IdealTransaction;
use Wirecard\PaymentSdk\TransactionService;

/**
 * Class IdealPayloadData
 * @package Wirecard\PaymentSdk\Entity\Payload
 * @since 4.0.0
 */
class IdealPayloadData implements PayloadDataInterface
{
    const TYPE = 'ideal';

    /**
     * @var array
     */
    private $payload;

    /**
     * IdealPayloadData constructor.
     * @param array $payload
     * @param Config $config
     * @throws \Http\Client\Exception
     * @since 4.0.0
     */
    public function __construct(array $payload, Config $config)
    {
        $transactionService = new TransactionService($config);
        $this->payload = $transactionService->getTransactionByRequestId(
            $payload[PayloadFields::REQUEST_ID],
            IdealTransaction::NAME,
            false
        );
    }

    /**
     * @return string
     * @since 4.0.0
     */
    public function getData()
    {
        return $this->payload;
    }

    /**
     * @return string
     * @since 4.0.0
     */
    public function getType()
    {
        return self::TYPE;
    }
}
