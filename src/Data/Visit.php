<?php

namespace App\Services\Clearinghouse\Data;

use Spatie\DataTransferObject\DataTransferObject;

class Visit extends DataTransferObject
{
    public string $reference_id;

    public Caregiver $caregiver;

    public string $timezone;

    public string $start_time_scheduled;

    public string $start_time_recorded;

    public string $start_time_invoiced;

    public Address $start_address_scheduled;

    public AddressType $start_address_type_scheduled;

    public ?string $start_address_type_other_scheduled;

    public Address $start_address_invoiced;

    public AddressType $start_address_type_invoiced;

    public ?string $start_address_type_other_invoiced;

    public VerificationMethod $start_verification_method;

    public ?Coordinates $start_address_coordinates_recorded;

    public ?string $start_user_agent;

    public ?string $start_ip_address;

    public ?string $start_phone_number;

    public ?int $start_unverified_reason_code;

    public string $end_time_scheduled;

    public string $end_time_recorded;

    public string $end_time_invoiced;

    public Address $end_address_scheduled;

    public AddressType $end_address_type_scheduled;

    public ?string $end_address_type_other_scheduled;

    public Address $end_address_invoiced;

    public AddressType $end_address_type_invoiced;

    public ?string $end_address_type_other_invoiced;

    public VerificationMethod $end_verification_method;

    public ?Coordinates $end_address_coordinates_recorded;

    public ?string $end_user_agent;

    public ?string $end_ip_address;

    public ?string $end_phone_number;

    public ?int $end_unverified_reason_code;

    /** @var \App\Services\Clearinghouse\Data\Adl[]|null */
    public ?array $adls;

    public ?string $notes;

    /** @var \App\Services\Clearinghouse\Data\VisitLineItem[] */
    public array $line_items;

    public float $amount;
}
