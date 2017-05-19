<?php

namespace App\Exceptions;

/**
 * If doctrine entity could not be found this exception is raised.
 */
class NotFoundException extends \Exception
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
