<?php

namespace App\Services\Clearinghouse\Data;

use App\BaseEnum;

/**
 * SubmissionStatus Enum
 *
 * @method static SubmissionStatus SUBMITTED()
 * @method static SubmissionStatus VALIDATED()
 * @method static SubmissionStatus REJECTED()
 * @method static SubmissionStatus PENDING_TRANSMISSION()
 * @method static SubmissionStatus RECEIVED_BY_CARRIER()
 * @method static SubmissionStatus COMPLETE()
 */
class SubmissionStatus extends BaseEnum
{
    private const SUBMITTED = 'SUBMITTED';
    private const VALIDATED = 'VALIDATED';
    private const REJECTED = 'REJECTED';
    private const PENDING_TRANSMISSION = 'PENDING_TRANSMISSION';
    private const RECEIVED_BY_CARRIER = 'RECEIVED_BY_CARRIER';
    private const COMPLETE = 'COMPLETE';
}
