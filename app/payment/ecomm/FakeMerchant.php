<?php

namespace App\Payment\EcommMerchant;

/**
 * Fake merchant interface used for testing purposes.
 */
class FakeMerchant
{
    private $url;
    private $keystore;
    private $keystorepassword;
    private $verbose;

    /**
     * Empty constructor.
     * @param string $url
     * @param string $keystore
     * @param string $keystorepassword
     * @param bool $verbose
     */
    public function __construct($url, $keystore, $keystorepassword, $verbose = false)
    {
        $this->url = $url;
        $this->keystore = $keystore;
        $this->keystorepassword = $keystorepassword;
        $this->verbose = $verbose;
    }

    /**
     * Start transaction and get results from remote payment server.
     * @param int $amount
     * @param int $currency
     * @param string $ip
     * @param string $desc
     * @param string $language
     * @return string
     */
    public function startSMSTrans($amount, $currency, $ip, $desc, $language)
    {
        return "TRANSACTION_ID: hehehehehehe";
    }

    /**
     * Get result of transaction with specified identification.
     * @param string $transactionId
     * @param string $ip
     * @return string
     */
    public function getTransResult($transactionId, $ip)
    {
        return "RESULT: NOK\nRESULT_CODE: 000\n3DSECURE: ok\nCARD_NUMBER: number\n"; // incorrect transaction
//        return "RESULT: OK\nRESULT_CODE: 000\n3DSECURE: ok\nCARD_NUMBER: number\n"; // correct transaction
    }

    /**
     * Close bussiness day.
     * @return string
     */
    public function closeDay()
    {
        return "RESULT: OK RESULT_CODE: 500 FLD_075: 4 FLD_076: 6 FLD_087: 40 FLD_088: 60";
    }

    /**
     * Reverse specified transaction.
     * @param string $transactionId
     * @param int $amount
     * @return string
     */
    public function reverse($transactionId, $amount)
    {
        return "RESULT: REVERSED\nRESULT_CODE: 000\n"; // correct reversal
    }
}
