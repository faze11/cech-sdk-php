<?php

namespace App\Services\Clearinghouse\Data;

use MyCLabs\Enum\Enum;

class PhoneType extends Enum
{
    private const LANDLINE = 'LANDLINE';
    private const MOBILE = 'MOBILE';

    public static function LANDLINE()
    {
        return new self(self::LANDLINE);
    }

    public static function MOBILE()
    {
        return new self(self::MOBILE);
    }
}
