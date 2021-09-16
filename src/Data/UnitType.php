<?php

namespace Clearinghouse\Data;

use MyCLabs\Enum\Enum;

class UnitType extends Enum
{
    private const HOUR = 'HOUR';
    private const DAY = 'DAY';
    private const WEEK = 'WEEK';
    private const MONTH = 'MONTH';
    private const VISIT = 'VISIT';
    private const MILE = 'MILE';
    private const OTHER = 'OTHER';

    public static function HOUR()
    {
        return new self(self::HOUR);
    }

    public static function DAY()
    {
        return new self(self::DAY);
    }

    public static function WEEK()
    {
        return new self(self::WEEK);
    }

    public static function MONTH()
    {
        return new self(self::MONTH);
    }

    public static function VISIT()
    {
        return new self(self::VISIT);
    }

    public static function MILE()
    {
        return new self(self::MILE);
    }

    public static function OTHER()
    {
        return new self(self::OTHER);
    }
}
