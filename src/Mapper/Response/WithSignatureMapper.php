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

class WithSignatureMapper implements MapperInterface
{
    /**
     * @var array
     */
    private $payload;

    /**
     * @var ResponseMapper
     */
    private $oldResponseMapper;

    /**
     * WithSignatureMapper constructor.
     * @param string $payload
     * @param Config $config
     * @since 4.0.0
     */
    public function __construct($payload, Config $config)
    {
        $this->payload = $payload;
        $this->oldResponseMapper = new ResponseMapper($config);
    }

    /**
     * @return \Wirecard\PaymentSdk\Response\Response
     * @since 4.0.0
     */
    public function map()
    {
        return $this->oldResponseMapper->mapInclSignature($this->payload);
    }
}
