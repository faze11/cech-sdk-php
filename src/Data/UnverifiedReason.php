<?php

namespace Clearinghouse\Data;

use MyCLabs\Enum\Enum;

class UnverifiedReason extends Enum
{
    private const SERVICE_OUTSIDE_HOME = 6001;
    private const FILL_IN = 6002;
    private const CLIENT_CANCELLED = 6003;
    private const CAREGIVER_ID_MISMATCH = 6004;
    private const CAREGIVER_FORGOT_CLOCK_IN = 6005;
    private const CAREGIVER_TECHNICAL_DIFFICULTIES_TVV = 6006;
    private const CAREGIVER_WRONG_PHONE_TVV = 6007;
    private const CAREGIVER_TECHNICAL_DIFFICULTIES_EVV = 6008;
    private const CAREGIVER_NO_SERVICE = 6009;
    private const CLIENT_PHONE_NOT_WORKING = 6010;
    private const CLIENT_PHONE_IN_USE = 6011;
    private const DISASTER_EMERGENCY = 6012;

    public static function SERVICE_OUTSIDE_HOME()
    {
        return new self(self::SERVICE_OUTSIDE_HOME);
    }

    public static function FILL_IN()
    {
        return new self(self::FILL_IN);
    }

    public static function CLIENT_CANCELLED()
    {
        return new self(self::CLIENT_CANCELLED);
    }

    public static function CAREGIVER_ID_MISMATCH()
    {
        return new self(self::CAREGIVER_ID_MISMATCH);
    }

    public static function CAREGIVER_FORGOT_CLOCK_IN()
    {
        return new self(self::CAREGIVER_FORGOT_CLOCK_IN);
    }

    public static function CAREGIVER_TECHNICAL_DIFFICULTIES_TVV()
    {
        return new self(self::CAREGIVER_TECHNICAL_DIFFICULTIES_TVV);
    }

    public static function CAREGIVER_WRONG_PHONE_TVV()
    {
        return new self(self::CAREGIVER_WRONG_PHONE_TVV);
    }

    public static function CAREGIVER_TECHNICAL_DIFFICULTIES_EVV()
    {
        return new self(self::CAREGIVER_TECHNICAL_DIFFICULTIES_EVV);
    }

    public static function CAREGIVER_NO_SERVICE()
    {
        return new self(self::CAREGIVER_NO_SERVICE);
    }

    public static function CLIENT_PHONE_NOT_WORKING()
    {
        return new self(self::CLIENT_PHONE_NOT_WORKING);
    }

    public static function CLIENT_PHONE_IN_USE()
    {
        return new self(self::CLIENT_PHONE_IN_USE);
    }

    public static function DISASTER_EMERGENCY()
    {
        return new self(self::DISASTER_EMERGENCY);
    }
}
