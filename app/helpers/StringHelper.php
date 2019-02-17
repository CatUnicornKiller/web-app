<?php

namespace App\Helpers;

use Nette;

/**
 * Helper class for some handy functions for string operations.
 */
class StringHelper
{
    use Nette\SmartObject;

    /**
     * From department string obtained from ifmsa.org get only department
     * description.
     * @param string $department
     * @return string
     */
    public function getDepartmentDescription($department)
    {
        $result = $department;

        $semicolon = strpos($department, ";");
        if ($semicolon) {
            $result = substr($department, 0, $semicolon);
        }

        return $result;
    }

    /**
     * Remove non-alphanumeric characters from given text.
     * @param string $text
     * @return string
     */
    public function alphaNumText($text)
    {
        // Remove any character that is not alphanumeric, white-space, or a hyphen
        $text = preg_replace("/[^a-z0-9\s\_]/i", "", $text);
        // Replace multiple instances of white-space with a single space
        $text = preg_replace("/\s\s+/", " ", $text);
        // Replace all spaces with hyphens
        $text = preg_replace("/\s/", "_", $text);
        // Replace multiple hyphens with a single hyphen
        $text = preg_replace("/\_\_+/", "_", $text);
        // Remove leading and trailing hyphens
        $text = trim($text, "_");

        return $text;
    }

    /**
     * Generate random string based on its length.
     * @param int $length
     * @return string
     */
    public function generateRandomString($length)
    {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $key;
    }
}
