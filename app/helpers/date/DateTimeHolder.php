<?php

namespace App\Helpers\Date;

use Nette;

/**
 * Holder of both textual description of the date and typed DateTime.
 */
class DateTimeHolder extends Nette\Object
{
    /**
     * Textual description of the typed date and time below.
     * @var string
     */
    public $textual;
    /**
     * Typed date and time.
     * @var \DateTime
     */
    public $typed;
}
