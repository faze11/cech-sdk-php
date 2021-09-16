<?php

namespace App\Services\Clearinghouse\Data;

use Spatie\DataTransferObject\DataTransferObject;

class Address extends DataTransferObject
{
    public string $address1;

    public ?string $address2;

    public string $city;

    public string $state;

    public string $zip;

    public string $country;
}
