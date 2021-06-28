<?php

namespace App\Payment;

use App\Exceptions\PaymentException;
use App\Model\Repository\EcommTransactions;
use App\Payment\External\ExternalServices;
use App\Payment\External\ExternalService;
use App\Payment\EcommMerchant\EcommTransactionsHelper;

/**
 * Helper class for external transaction and holder for external services
 * defined in configuration file. Currently there is only one method needed
 * which is the one for the transaction start.
 */
class ExternalTransactionsHelper
{
    /** @var EcommTransactionsHelper */
    private $ecommTransactionsHelper;
    /** @var EcommTransactions */
    private $ecommTransactions;
    /** @var ExternalServices */
    private $externalServices;
    /** @var PaymentParams */
    private $paymentParams;

    /**
     * DI Constructor.
     * @param EcommTransactionsHelper $ecommTransactionsHelper
     * @param EcommTransactions $ecommTransactions
     * @param ExternalServices $externalServices
     * @param PaymentParams $paymentParams
     */
    public function __construct(
        EcommTransactionsHelper $ecommTransactionsHelper,
        EcommTransactions $ecommTransactions,
        ExternalServices $externalServices,
        PaymentParams $paymentParams
    ) {
        $this->ecommTransactionsHelper = $ecommTransactionsHelper;
        $this->ecommTransactions = $ecommTransactions;
        $this->externalServices = $externalServices;
        $this->paymentParams = $paymentParams;
    }

    /**
     * Find appropriate external service using this payment gateway by the given
     * identification.
     * @param string $id external service identification
     * @return ExternalService
     * @throws PaymentException if service cannot be found
     */
    public function findService(string $id): ExternalService
    {
        return $this->externalServices->findService($id);
    }

    /**
     * Checks if service is registered within application and if so, then start
     * transaction and store data obtained from payment dateway server.
     * @param string $service service identification
     * @param int $amount amount which should be paid in given currency
     * @param string $ipAddress ip address of the user
     * @param string $description description of the transaction as obtained
     * from service
     * @param string $currency currency code
     * @return array($url, $transaction) Redirection URL where payment gateway
     * is placed and transaction entity.
     * @throws PaymentException in case of transaction initialization failure
     */
    public function startTransaction(
        string $service,
        int $amount,
        string $ipAddress,
        string $description,
        string $currency
    ): array {

        $serviceConfig = $this->findService($service);

        // description which will be visible on transaction details
        $desc = substr($serviceConfig->getDescription($description), 0, 125);
        list($url, $transaction) =
                $this->ecommTransactionsHelper->startTransaction(
                    $desc,
                    $amount,
                    $ipAddress,
                    $currency,
                    $this->paymentParams->language
                );

        // let us know about external transaction
        $transaction->externalService = $serviceConfig->getId();
        $this->ecommTransactions->flush();

        return array($url, $transaction);
    }
}
