<?php

namespace App\Services\Clearinghouse\Data;

use Spatie\DataTransferObject\DataTransferObject;

class Adl extends DataTransferObject
{
    public int $code;

    public ?string $specification;

    public ?string $other_comment;
}
