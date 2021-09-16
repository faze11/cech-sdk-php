<?php

namespace Clearinghouse\Data;

use MyCLabs\Enum\Enum;

class VerificationMethod extends Enum
{
    private const EVV = 'EVV';
    private const TVV = 'TVV';
    private const NONE = 'NONE';

    public static function EVV()
    {
        return new self(self::EVV);
    }

    public static function TVV()
    {
        return new self(self::TVV);
    }

    public static function NONE()
    {
        return new self(self::NONE);
    }
}
