<?php

namespace App\Payment;

/**
 * Special parameters from configuration file concerning general payment and its
 * settings.
 */
class PaymentParams
{
    /** Access token for public accesses. */
    public $publicAccessToken;
    /** Specification of currency. */
    public $currency; //203=CZK 978=EUR 840=USD 941=RSD 703=SKK 440=LTL 233=EEK 643=RUB 891=YUM
    /** Language of the payment gateway. */
    public $language;

    /**
     * Constructor which takes parameters from configuration file.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->publicAccessToken = $params['publicAccessToken'];
        $this->currency = $params['currency'];
        $this->language = $params['language'];
    }
}
