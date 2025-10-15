<?php

namespace WHMCS\Config;

class Setting
{
    /**
     * @throws \Exception
     */
    public static function getValue(string $value)
    {
        if ($value == 'SystemURL') {
            return 'https://something.nl/';
        }
        throw new \Exception("Unknown!");
    }
}
