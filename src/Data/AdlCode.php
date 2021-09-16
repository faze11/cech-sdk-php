<?php

namespace Clearinghouse\Data;

use MyCLabs\Enum\Enum;

class AdlCode extends Enum
{
    // ADLs
    private const BATHING = 1001;
    private const DRESSING = 1002;
    private const EATING = 1003;
    private const TOILETING = 1004;
    private const TRANSFERRING = 1005;
    private const CONTINENCE = 1006;
    private const AMBULATION_MOBILITY = 1007;
    private const COGNITIVE_SUPERVISION = 1008;
    private const MEDICATION_ADMINISTRATION = 1009;

    // IADLs
    private const MEAL_PREPARATION = 2001;
    private const HOUSEKEEPING = 2002;
    private const TRANSPORTATION = 2003;
    private const LAUNDRY = 2004;
    private const SHOPPING = 2005;
    private const OTHER = 2999;

    public static function BATHING()
    {
        return new self(self::BATHING);
    }

    public static function DRESSING()
    {
        return new self(self::DRESSING);
    }

    public static function EATING()
    {
        return new self(self::EATING);
    }

    public static function TOILETING()
    {
        return new self(self::TOILETING);
    }

    public static function TRANSFERRING()
    {
        return new self(self::TRANSFERRING);
    }

    public static function CONTINENCE()
    {
        return new self(self::CONTINENCE);
    }

    public static function AMBULATION_MOBILITY()
    {
        return new self(self::AMBULATION_MOBILITY);
    }

    public static function COGNITIVE_SUPERVISION()
    {
        return new self(self::COGNITIVE_SUPERVISION);
    }

    public static function MEDICATION_ADMINISTRATION()
    {
        return new self(self::MEDICATION_ADMINISTRATION);
    }

    public static function MEAL_PREPARATION()
    {
        return new self(self::MEAL_PREPARATION);
    }

    public static function HOUSEKEEPING()
    {
        return new self(self::HOUSEKEEPING);
    }

    public static function TRANSPORTATION()
    {
        return new self(self::TRANSPORTATION);
    }

    public static function LAUNDRY()
    {
        return new self(self::LAUNDRY);
    }

    public static function SHOPPING()
    {
        return new self(self::SHOPPING);
    }

    public static function OTHER()
    {
        return new self(self::OTHER);
    }
}
