<?php

namespace App\Helpers\Date;

use Nette;

/**
 * Date and time helper for better work with dates. Functions return special
 * DateTimeHolder which contains both textual and typed DateTime.
 */
class DateHelper extends Nette\Object
{
    /**
     * Create datetime from the given text if valid, or otherwise return first
     * day of current month.
     * @param string $text
     * @return \App\Helpers\Date\DateTimeHolder
     */
    public function createFromDateOrFirstDayOfMonth($text): DateTimeHolder
    {
        $holder = new DateTimeHolder;
        $date = date_create_from_format("j. n. Y", $text);
        $holder->typed = $date ? $date : new \DateTime('first day of this month');
        $holder->textual = $holder->typed->format("j. n. Y");
        return $holder;
    }

    /**
     * Create datetime from the given text if valid, or otherwise return last
     * day of current month.
     * @param string $text
     * @return \App\Helpers\Date\DateTimeHolder
     */
    public function createFromDateOrLastDayOfMonth($text): DateTimeHolder
    {
        $holder = new DateTimeHolder;
        $date = date_create_from_format("j. n. Y", $text);
        $holder->typed = $date ? $date : new \DateTime('last day of this month');
        $holder->textual = $holder->typed->format("j. n. Y");
        return $holder;
    }
}
