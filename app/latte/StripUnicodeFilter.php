<?php

namespace App\Latte\Filters;

use Nette;

/**
 * Latte extension filter.
 */
class StripUnicodeFilter
{
    use Nette\SmartObject;

    /**
     * Strips multiple whitespaces, default ones and Unicode ones too.
     * @param string $s
     * @return string
     */
    public function __invoke($s)
    {
        return preg_replace('#[ \t\n\r\0\x0B\xC2\xA0]+#', ' ', $s);
    }
}
