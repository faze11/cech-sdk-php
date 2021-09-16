<?php

namespace App\Services\Clearinghouse\Data;

use Spatie\DataTransferObject\DataTransferObject;

class VisitLineItem extends DataTransferObject
{
    public string $reference_id;

    public int $service_code;

    public ?string $service_code_other;

    public float $rate;

    public RateIndicator $rate_indicator;

    public UnitType $unit_type;

    public float $units;

    public float $amount;

    public ?string $start_time_invoiced;

    public ?string $end_time_invoiced;
}
