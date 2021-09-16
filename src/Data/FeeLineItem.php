<?php

namespace Clearinghouse\Data;

use Spatie\DataTransferObject\DataTransferObject;

class FeeLineItem extends DataTransferObject
{
    public string $reference_id;

    public int $fee_code;

    public ?string $fee_code_other;

    public string $date;

    public float $rate;

    public UnitType $unit_type;

    public float $units;

    public float $amount;

    public ?string $notes;
}
