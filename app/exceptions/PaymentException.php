<?php

namespace App\Exceptions;

/**
 * Thrown from payment helpers in case of any unexpected critical error.
 */
class PaymentException extends \Exception
{
    /**
     * Constructor with message and previously thrown exception.
     * @param type $message
     * @param type $previous
     */
    public function __construct($message, $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
