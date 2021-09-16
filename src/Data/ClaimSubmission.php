<?php

namespace Clearinghouse\Data;

use Spatie\DataTransferObject\DataTransferObject;

class ClaimSubmission extends DataTransferObject
{
    public string $reference_id;

    public string $provider_id;

    public string $provider_name;

    public ?string $provider_dba;

    public ?string $provider_npi;

    public string $provider_tax_id;

    public Address $provider_address;

    public string $provider_phone;

    public PhoneType $provider_phone_type;

    public string $carrier_code;

    public Claimant $claimant;

    public string $timezone;

    public string $date;

    public int $revision_number;

    public ?string $original_claim_id;

    public float $amount;

    /** @var \Clearinghouse\Data\Visit[] */
    public array $visits;

    /** @var null|\Clearinghouse\Data\FeeLineItem[] */
    public ?array $fees;
}
