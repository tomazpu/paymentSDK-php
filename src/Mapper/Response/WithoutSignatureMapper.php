<?php
/**
 * Shop System SDK:
 * - Terms of Use can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/_TERMS_OF_USE
 * - License can be found under:
 * https://github.com/wirecard/paymentSDK-php/blob/master/LICENSE
 */

namespace Wirecard\PaymentSdk\Mapper\Response;

use Wirecard\PaymentSdk\Config\Config;
use Wirecard\PaymentSdk\Mapper\ResponseMapper;

/**
 * Class WithoutSignatureMapper
 * @package Wirecard\PaymentSdk\Mapper\Response
 * @since 4.0.0
 */
class WithoutSignatureMapper implements MapperInterface
{
    /**
     * @var string
     */
    private $payload;

    /**
     * @var ResponseMapper
     */
    private $oldResponseMapper;

    /**
     * WithoutSignatureMapper constructor.
     * @param string $payload
     * @param Config $config
     * @since 4.0.0
     */
    public function __construct($payload, Config $config)
    {
        $this->payload = $payload;
        $this->oldResponseMapper = new ResponseMapper($config);
    }

    public function map()
    {
        return $this->oldResponseMapper->map($this->payload);
    }
}
