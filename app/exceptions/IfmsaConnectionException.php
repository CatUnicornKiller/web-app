<?php

namespace App\Exceptions;

use Exception;

/**
 * Thrown if there was error in getting data from ifmsa.org
 */
class IfmsaConnectionException extends Exception
{
    /**
     * Constructor with message and previous exception.
     * @param string $message
     * @param Exception $previous
     */
    public function __construct($message, $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
