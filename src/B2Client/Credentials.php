<?php

namespace iansltx\B2Client;

use iansltx\B2Client\Http\ClientInterface;
use iansltx\B2Client\Http\CurlClient;
use iansltx\B2Client\Http\ClientException;

class Credentials implements ServerSettingsInterface, \JsonSerializable
{
    const AUTH_URL = 'https://api.backblaze.com/b2api/v1/b2_authorize_account';

    /** @var ServerSettings */
    protected $settings = null;

    protected $accountId;
    protected $applicationKey;
    protected $http;

    public function __construct($account_id, $application_key, ClientInterface $curl = null)
    {
        $this->accountId = $account_id;
        $this->applicationKey = $application_key;
        $this->http = $curl ?: new CurlClient();
    }

    public function getSettings()
    {
        if (!$this->settings) {
            $response = $this->http->getRawWithBasicAuth(static::AUTH_URL, $this->accountId, $this->applicationKey);
            if (!$response->isSuccess()) {
                throw new ClientException(json_encode($response->getBody()), $response->getStatusCode());
            }

            try {
                $this->settings = ServerSettings::fromJSON($response->getBody());
            } catch (\InvalidArgumentException $e) {
                throw new ClientException("Missing fields in response body", 500, $e);
            }
        }

        return $this->settings;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    public function getBaseAPIURL()
    {
        return $this->getSettings()->getBaseAPIURL();
    }

    public function getToken()
    {
        return $this->getSettings()->getToken();
    }

    public function getBaseDownloadURL()
    {
        return $this->getSettings()->getBaseDownloadURL();
    }

    public function getHttpClient()
    {
        return $this->http;
    }

    public function jsonSerialize()
    {
        return $this->getSettings()->jsonSerialize();
    }
}