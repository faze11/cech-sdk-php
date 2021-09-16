<?php

namespace Clearinghouse\Data;

use MyCLabs\Enum\Enum;

class AdlSpecification extends Enum
{
    private const HANDS_ON = 'HANDS_ON';
    private const STANDBY = 'STANDBY';

    public static function HANDS_ON()
    {
        return new self(self::HANDS_ON);
    }

    public static function STANDBY()
    {
        return new self(self::STANDBY);
    }
}
