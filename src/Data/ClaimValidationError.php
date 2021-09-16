<?php

namespace App\Services\Clearinghouse\Data;

use Spatie\DataTransferObject\DataTransferObject;

class ClaimValidationError extends DataTransferObject
{
    public string $failure_type;

    public string $failure_message;

    public ?string $visit_reference_id;

    public ?int $visit_index;

    public ?string $line_item_reference_id;

    public ?int $line_item_index;

    public ?string $fee_line_item_reference_id;

    public ?int $fee_line_item_index;
}
