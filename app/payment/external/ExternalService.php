<?php

namespace App\Payment\External;

/**
 * External service which is connected to this application and use payment
 * gateway implementation. Service in this form is designed to be general
 * and easy to define in configuration file. Items in here should be sufficient
 * for all kinds of external services.
 */
class ExternalService
{
    /** Identifier of the service */
    private $id;
    /** Transaction identification http query key */
    private $transactionParamId;
    /** Error message http query key */
    private $errorParamId;
    /** Base URL for the redirection when the transaction was successful */
    private $okRedirectionUrl;
    /** Base URL for the redirection when the transaction failed */
    private $failRedirectionUrl;

    /**
     * Constructor.
     * @param string $id identification of service, used on transaction start
     * @param string $transactionParamId transaction identification query key
     * @param string $errorParamId error message query key
     * @param string $okRedirection success redirection url base
     * @param string $failRedirection failure redirection url base
     */
    public function __construct(
        $id,
        $transactionParamId,
        $errorParamId,
        $okRedirection,
        $failRedirection
    ) {

        $this->id = $id;
        $this->transactionParamId = $transactionParamId;
        $this->errorParamId = $errorParamId;
        $this->okRedirectionUrl = $okRedirection;
        $this->failRedirectionUrl = $failRedirection;
    }

    /**
     * Get external service identification.
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Get appropriate complete description of the transaction.
     * @param string $description base description without service
     * identification
     * @return string complete description
     */
    public function getDescription(string $description): string
    {
        return "***" . strtoupper($this->getId()) ." Payment***;" .
            $description;
    }

    /**
     * Get complete redirection URL for failed transactions.
     * @param string $transactionId transaction identification
     * @param string $errorMsg error message
     * @return string URL
     */
    public function getFailRedirectionUrl($transactionId, $errorMsg): string
    {
        return $this->failRedirectionUrl . "?" . $this->transactionParamId .
                "=" . urlencode($transactionId) . "&" . $this->errorParamId .
                "=" . urlencode($errorMsg);
    }

    /**
     * Get complete redirection URL for successful transactions.
     * @param string $transactionId
     * @return string URL
     */
    public function getOkRedirectionUrl($transactionId): string
    {
        return $this->okRedirectionUrl . "?" . $this->transactionParamId .
                "=" . urlencode($transactionId);
    }
}
