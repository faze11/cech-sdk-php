<?php

namespace Clearinghouse;

use Spatie\DataTransferObject\DataTransferObject;

class ApiClientConfig extends DataTransferObject
{
    public string $url;

    public string $clientSecret;

    public string $clientId;

    public string $authServer;

    public string $authScope;
}
