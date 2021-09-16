<?php

namespace Clearinghouse\Data;

use MyCLabs\Enum\Enum;

class CaregiverLicense extends Enum
{
    private const RN = 5001;
    private const LPN = 5002;
    private const PHYSICAL_THERAPIST = 5003;
    private const OCCUPATIONAL_THERAPIST = 5004;
    private const SPEECH_THERAPIST = 5005;
    private const SOCIAL_WORKER = 5006;
    private const CNA = 5007;
    private const HHA = 5008;
    private const HOME_CARE_AIDE = 5009;
    private const PERSONAL_CARE_AIDE_ATTENDANT = 5010;
    private const UNLICENSED_CAREGIVER = 5011;

    public static function RN()
    {
        return new self(self::RN);
    }

    public static function LPN()
    {
        return new self(self::LPN);
    }

    public static function PHYSICAL_THERAPIST()
    {
        return new self(self::PHYSICAL_THERAPIST);
    }

    public static function OCCUPATIONAL_THERAPIST()
    {
        return new self(self::OCCUPATIONAL_THERAPIST);
    }

    public static function SPEECH_THERAPIST()
    {
        return new self(self::SPEECH_THERAPIST);
    }

    public static function SOCIAL_WORKER()
    {
        return new self(self::SOCIAL_WORKER);
    }

    public static function CNA()
    {
        return new self(self::CNA);
    }

    public static function HHA()
    {
        return new self(self::HHA);
    }

    public static function HOME_CARE_AIDE()
    {
        return new self(self::HOME_CARE_AIDE);
    }

    public static function PERSONAL_CARE_AIDE_ATTENDANT()
    {
        return new self(self::PERSONAL_CARE_AIDE_ATTENDANT);
    }

    public static function UNLICENSED_CAREGIVER()
    {
        return new self(self::UNLICENSED_CAREGIVER);
    }
}
