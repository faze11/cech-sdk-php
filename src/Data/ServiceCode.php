<?php

namespace App\Services\Clearinghouse\Data;

use App\BaseEnum;

class ServiceCode extends BaseEnum
{
    private const SKILLED_CARE = 3001;
    private const PERSONAL_CARE = 3002;
    private const HOMEMAKER = 3003;
    private const THERAPY = 3004;
    private const SOCIAL_WORK = 3005;
    private const MILEAGE = 3006;
    private const EXPENSES = 3007;

    public static function SKILLED_CARE()
    {
        return new self(self::SKILLED_CARE);
    }

    public static function PERSONAL_CARE()
    {
        return new self(self::PERSONAL_CARE);
    }

    public static function HOMEMAKER()
    {
        return new self(self::HOMEMAKER);
    }

    public static function THERAPY()
    {
        return new self(self::THERAPY);
    }

    public static function SOCIAL_WORK()
    {
        return new self(self::SOCIAL_WORK);
    }

    public static function MILEAGE()
    {
        return new self(self::MILEAGE);
    }

    public static function EXPENSES()
    {
        return new self(self::EXPENSES);
    }
}
