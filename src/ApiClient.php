<?php

namespace App\Services\Clearinghouse;

use App\Services\Clearinghouse\Data\ClaimSubmission;
use App\Services\Clearinghouse\Data\SubmissionStatusResponse;
use App\Services\Clearinghouse\Exceptions\ValidationException;
use App\Services\Clearinghouse\Exceptions\DuplicateClaimException;
use App\Services\Clearinghouse\Exceptions\InvalidResponseException;

class ApiClient
{
    public ApiClientConfig $config;

    /**
     * ApiClient constructor.
     *
     * @param ApiClientConfig $config
     */
    public function __construct(ApiClientConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Get the API path.
     *
     * @param string $path
     * @return string
     */
    public function getPath(string $path): string
    {
        return "{$this->config->url}/$path";
    }

    /**
     * Get the authentication token.
     *
     * @return string|null
     * @throws \Exception
     */
    protected function authenticate(): ?string
    {
        $path = $this->config->authServer . '/v1/token';

        $data = 'grant_type=client_credentials&scope=' . urlencode($this->config->authScope);

        try {
            $process = curl_init($path);
            $token = base64_encode($this->config->clientId . ':' . $this->config->clientSecret);
//            \Log::info("Credential token: $token");
            curl_setopt($process, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded',
                'Accept: application/json',
                "Authorization: Basic $token",
                'Cache-Control: no-cache',
            ]);
            curl_setopt($process, CURLOPT_HEADER, 1);
            curl_setopt($process, CURLOPT_TIMEOUT, 60);
            curl_setopt($process, CURLOPT_POST, 1);
            curl_setopt($process, CURLOPT_POSTFIELDS, $data);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, true);

            if (! ($result = curl_exec($process))) {
                throw new \Exception('Could not connect to the oAuth server.');
            }
            $responseCode = curl_getinfo($process, CURLINFO_HTTP_CODE);
            $header_size = curl_getinfo($process, CURLINFO_HEADER_SIZE);
            curl_close($process);

            $body = substr($result, $header_size);
//            \Log::info("Response Code: $responseCode");
//            \Log::info("Response: $body");

            if ($responseCode != 200) {
                return null;
            }

            $json = json_decode($body);
            if (isset($json, $json->access_token)) {
                return $json->access_token;
            }

            return null;
        } catch (\Exception $ex) {
            \Log::error('authenticate Exception: ' . $ex->getMessage());
            app('sentry')->captureException($ex);
            throw new \Exception('Error connecting to the Clearinghouse API.');
        }
    }

    /**
     * Submit a claim.
     *
     * @param ClaimSubmission $claim
     * @return string
     * @throws \Exception
     */
    public function submitClaim(ClaimSubmission $claim): string
    {
        try {
            $data = json_encode($claim->toArray());
            $path = $this->getPath('submission');
            list($status, $body) = $this->_post($path, $data);

            $response = json_decode($body);
        } catch (\Exception $ex) {
            \Log::error('submitClaim Exception: ' . $ex->getMessage());
            app('sentry')->captureException($ex);
            throw new \Exception('Error connecting to the Clearinghouse API.');
        }

        \Log::info('CECH Result: ' . $body);
        if ($status == 409) {
            // duplicate claim
            throw new DuplicateClaimException($body);
        }
        if ($status == 422) {
            // validation error
            throw new ValidationException($body);
        }
        if ($status != 200 || ! isset($response->id)) {
            throw new InvalidResponseException($body);
        }

        return $response->id;
    }

    /**
     * Mark the Provider as active in the Clearinghouse system.
     *
     * @param string $providerId
     * @return bool
     * @throws \Exception
     */
    public function activateProvider(string $providerId): bool
    {
        try {
            $data = json_encode(['provider_id' => $providerId]);
            $path = $this->getPath('provider/activate-pms');
            list($status, $body) = $this->_post($path, $data);
            \Log::info('CECH Result: ' . $body);
            if ($status == 412) {
                return false;
            }
            if ($status == 409) {
                return false;
            }

            $response = json_decode($body);
            if ($status != 200 || ! isset($response->pms_id)) {
                throw new \Exception('[Clearinghouse.ApiClient.activateProvider] Invalid Response: ' . $body);
            }

            return true;
        } catch (\Exception $ex) {
            \Log::error('[Clearinghouse.ApiClient.activateProvider] Exception: ' . $ex->getMessage());
            app('sentry')->captureException($ex);
            throw new \Exception('Error connecting to the Clearinghouse API.');
        }
    }

    public function getStatus(string $submissionId): SubmissionStatusResponse
    {
        try {
            $path = $this->getPath("submission/{$submissionId}/status");
            list($status, $body) = $this->_get($path);

            \Log::info("CECH Result: $body");
            if ($status == 401) {
                // unauthorized
                throw new \Exception('Clearinghouse API - Unauthorized: ' . $body);
            }
            if ($status == 404) {
                // not found
                throw new \Exception('Clearinghouse API - Submission Not Found: ' . $body);
            }

            return new SubmissionStatusResponse(json_decode($body, true));
        } catch (\Exception $ex) {
            \Log::error('getStatus Exception: ' . $ex->getMessage());
            app('sentry')->captureException($ex);
            throw new \Exception('Error connecting to the Clearinghouse API.');
        }
    }

    /**
     * Send POST API request.
     *
     * @param string $path
     * @param string $data
     * @return array
     * @throws \Exception
     */
    protected function _post(string $path, string $data): array
    {
        \Log::debug("Endpoint: $path");
        \Log::debug("Request Body: $data");

        try {
            $token = $this->authenticate();
            if (empty($token)) {
                throw new \Exception('Could not authenticate.');
            }

            $process = curl_init($path);
            curl_setopt($process, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                "Authorization: Bearer $token",
            ]);
            curl_setopt($process, CURLOPT_HEADER, 1);
            curl_setopt($process, CURLOPT_TIMEOUT, 60);
            curl_setopt($process, CURLOPT_POST, 1);
            curl_setopt($process, CURLOPT_POSTFIELDS, $data);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($process, CURLOPT_FAILONERROR, false);

            if (! ($result = curl_exec($process))) {
                \Log::debug("_post Result: $result");
                throw new \Exception('Could not connect to the Clearinghouse API.');
            }
            $responseCode = curl_getinfo($process, CURLINFO_HTTP_CODE);
            $header_size = curl_getinfo($process, CURLINFO_HEADER_SIZE);
            curl_close($process);

            $body = substr($result, $header_size);

            return [$responseCode, $body];
        } catch (\Exception $ex) {
            \Log::error('_post Exception: ' . $ex->getMessage());
            app('sentry')->captureException($ex);
            throw new \Exception('Error connecting to the Clearinghouse API.');
        }
    }

    /**
     * Send GET API request.
     *
     * @param string $path
     * @return array
     * @throws \Exception
     */
    protected function _get(string $path): array
    {
        try {
            $token = $this->authenticate();
            if (empty($token)) {
                throw new \Exception('Could not authenticate.');
            }

            $process = curl_init($path);
            curl_setopt($process, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                "Authorization: Bearer $token",
            ]);
            curl_setopt($process, CURLOPT_HEADER, 1);
            curl_setopt($process, CURLOPT_TIMEOUT, 60);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, true);

            if (! ($result = curl_exec($process))) {
                throw new \Exception('Could not connect to the Clearinghouse API.');
            }
            $responseCode = curl_getinfo($process, CURLINFO_HTTP_CODE);
            $header_size = curl_getinfo($process, CURLINFO_HEADER_SIZE);
            curl_close($process);

            $body = substr($result, $header_size);

            return [$responseCode, $body];
        } catch (\Exception $ex) {
            \Log::error('_get Exception: ' . $ex->getMessage());
            app('sentry')->captureException($ex);
            throw new \Exception('Error connecting to the Clearinghouse API.');
        }
    }
}
