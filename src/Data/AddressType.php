<?php

namespace Clearinghouse\Data;

use MyCLabs\Enum\Enum;

class AddressType extends Enum
{
    private const HOME = 'HOME';
    private const FACILITY = 'FACILITY';
    private const HOSPITAL = 'HOSPITAL';
    private const OTHER = 'OTHER';

    public static function HOME()
    {
        return new self(self::HOME);
    }

    public static function FACILITY()
    {
        return new self(self::FACILITY);
    }

    public static function HOSPITAL()
    {
        return new self(self::HOSPITAL);
    }

    public static function OTHER()
    {
        return new self(self::OTHER);
    }
}
