<?php

namespace Clearinghouse\Data;

/**
 * ValidationErrorType Enum
 */
class ValidationErrorType extends BaseEnum
{
    private const NO_PMS_ID = 'NO_PMS_ID';
    private const NO_CARRIER_CODE = 'NO_CARRIER_CODE';
    private const NO_PROVIDER_ID = 'NO_PROVIDER_ID';
    private const PMS_NOT_FOUND = 'PMS_NOT_FOUND';
    private const PMS_IS_DISABLED = 'PMS_IS_DISABLED';
    private const PROVIDER_NOT_FOUND = 'PROVIDER_NOT_FOUND';
    private const PROVIDER_IS_DISABLED = 'PROVIDER_IS_DISABLED';
    private const INVALID_PMS_FOR_PROVIDER = 'INVALID_PMS_FOR_PROVIDER';
    private const CARRIER_CODE_NOT_FOUND = 'CARRIER_CODE_NOT_FOUND';
    private const CARRIER_IS_DISABLED = 'CARRIER_IS_DISABLED';
    private const INVALID_TOTAL_AMOUNT = 'INVALID_TOTAL_AMOUNT';
    private const INVALID_VISIT_AMOUNT = 'INVALID_VISIT_AMOUNT';
    private const INVALID_VISIT_LINE_ITEM = 'INVALID_VISIT_LINE_ITEM';

    public static function NO_PMS_ID()
    {
        return new self(self::NO_PMS_ID);
    }

    public static function NO_CARRIER_CODE()
    {
        return new self(self::NO_CARRIER_CODE);
    }

    public static function NO_PROVIDER_ID()
    {
        return new self(self::NO_PROVIDER_ID);
    }

    public static function PMS_NOT_FOUND()
    {
        return new self(self::PMS_NOT_FOUND);
    }

    public static function PMS_IS_DISABLED()
    {
        return new self(self::PMS_IS_DISABLED);
    }

    public static function PROVIDER_NOT_FOUND()
    {
        return new self(self::PROVIDER_NOT_FOUND);
    }

    public static function PROVIDER_IS_DISABLED()
    {
        return new self(self::PROVIDER_IS_DISABLED);
    }

    public static function INVALID_PMS_FOR_PROVIDER()
    {
        return new self(self::INVALID_PMS_FOR_PROVIDER);
    }

    public static function CARRIER_CODE_NOT_FOUND()
    {
        return new self(self::CARRIER_CODE_NOT_FOUND);
    }

    public static function CARRIER_IS_DISABLED()
    {
        return new self(self::CARRIER_IS_DISABLED);
    }

    public static function INVALID_TOTAL_AMOUNT()
    {
        return new self(self::INVALID_TOTAL_AMOUNT);
    }

    public static function INVALID_VISIT_AMOUNT()
    {
        return new self(self::INVALID_VISIT_AMOUNT);
    }

    public static function INVALID_VISIT_LINE_ITEM()
    {
        return new self(self::INVALID_VISIT_LINE_ITEM);
    }
}
