<?php

namespace App\Payment\EcommMerchant;

/**
 * Parameters from configuration file concerning ecomm payment system and its
 * settings.
 */
class EcommParams
{
    /** Address of the API server. */
    public $serverUrl;
    /** Address where payment gateway resides. */
    public $clientUrl;
    /** Full path to keystore file */
    public $certPath;
    /** Keystore password */
    public $certPass;

    /**
     * Constroctor from given parameters.
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->serverUrl = $params['server_url'];
        $this->clientUrl = $params['client_url'];
        $this->certPath = $params['cert_path'];
        $this->certPass = $params['cert_pass'];
    }
}
