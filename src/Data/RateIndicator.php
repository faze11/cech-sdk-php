<?php

namespace Clearinghouse\Data;

use MyCLabs\Enum\Enum;

class RateIndicator extends Enum
{
    private const REG = 'REG';
    private const HOL = 'HOL';
    private const OT = 'OT';
    private const HOL_OT = 'HOL_OT';

    public static function REG()
    {
        return new self(self::REG);
    }

    public static function HOL()
    {
        return new self(self::HOL);
    }

    public static function OT()
    {
        return new self(self::OT);
    }

    public static function HOL_OT()
    {
        return new self(self::HOL_OT);
    }
}
