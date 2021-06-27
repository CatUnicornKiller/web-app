<?php

namespace App\Exceptions;

use Exception;

/**
 * If doctrine entity could not be found this exception is raised.
 */
class NotFoundException extends Exception
{
    /**
     * Constructor with message and previously thrown exception.
     * @param string $message
     * @param Exception $previous
     */
    public function __construct($message, $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
