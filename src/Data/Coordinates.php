<?php

namespace App\Services\Clearinghouse\Data;

use Spatie\DataTransferObject\DataTransferObject;

class Coordinates extends DataTransferObject
{
    public float $latitude;

    public float $longitude;

    public ?float $elevation;
}
