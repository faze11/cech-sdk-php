<?php

namespace App\Services\Clearinghouse\Data;

use Spatie\DataTransferObject\DataTransferObject;

class SubmissionStatusResponse extends DataTransferObject
{
    public string $id;

    public string $reference_id;

    public string $submitted_at;

    public string $status;

    /** @var null|\App\Services\Clearinghouse\Data\ClaimValidationError[] */
    public ?array $validation_errors;
}
