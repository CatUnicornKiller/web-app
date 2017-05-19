<?php

namespace App\Payment\External;

use App\Exceptions\PaymentException;

/**
 * Holder for all defined external services which are loaded from configuration
 * file. Finder of the service is also included.
 */
class ExternalServices
{
    /** @var array */
    private $services = array();

    /**
     * DI Constructor.
     * @param array $params
     */
    public function __construct(array $params)
    {
        foreach ($params as $key => $value) {
            $service = new ExternalService(
                $key,
                $value['transactionParamId'],
                $value['errorParamId'],
                $value['okRedirectionUrl'],
                $value['failRedirectionUrl']
            );
        }
        $this->services[$service->getId()] = $service;
    }

    /**
     * Find service among external services loaded from configuration file.
     * @param string $id service identification
     * @return ExternalService
     * @throws PaymentException if external service cannot be found
     */
    public function findService(string $id): ExternalService
    {
        if (array_key_exists($id, $this->services)) {
            return $this->services[$id];
        }
        throw new PaymentException("Service {$id} cannot be found");
    }
}
