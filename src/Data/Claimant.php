<?php

namespace App\Services\Clearinghouse\Data;

use Spatie\DataTransferObject\DataTransferObject;

class Claimant extends DataTransferObject
{
    public string $policy_number;

    public ?string $medicaid_id;

    public string $dob;

    public string $first_name;

    public ?string $middle_name;

    public string $last_name;

    public ?string $name_suffix;

    public Address $mailing_address;

    public Address $service_address;

    public ?Address $service_address_alternate;

    public ?string $contact_phone;

    public ?PhoneType $contact_phone_type;

    public ?string $email;
}
