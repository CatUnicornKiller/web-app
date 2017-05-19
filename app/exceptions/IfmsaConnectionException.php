<?php

namespace App\Exceptions;

/**
 * Thrown if there was error in getting data from ifmsa.org
 */
class IfmsaConnectionException extends \Exception
{
    /**
     * Constructor with message and previous exception.
     * @param type $message
     * @param type $previous
     */
    public function __construct($message, $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
