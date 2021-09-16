<?php

namespace Clearinghouse\Data;

use Spatie\DataTransferObject\DataTransferObject;

class Caregiver extends DataTransferObject
{
    public string $reference_id;

    public string $dob;

    public string $ssn;

    public int $license_type;

    public string $first_name;

    public ?string $middle_name;

    public string $last_name;

    public ?string $contact_phone;

    public ?PhoneType $contact_phone_type;

    public ?string $email;

    public ?Address $address;
}
