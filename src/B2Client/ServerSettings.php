<?php

namespace iansltx\B2Client;

class ServerSettings implements ServerSettingsInterface, \JsonSerializable
{
    protected $accountId;
    protected $token;
    protected $apiBase;
    protected $downloadBase;

    public static function fromJSON($json)
    {
        $body = json_decode($json, JSON_OBJECT_AS_ARRAY);
        if (!isset($body['accountId']) || !isset($body['apiUrl']) || !isset($body['downloadUrl']) ||
                !isset($body['authorizationToken'])) {
            throw new \InvalidArgumentException("JSON did not contain expected fields; " . $json);
        }

        return new static($body['accountId'], $body['authorizationToken'], $body['apiUrl'], $body['downloadUrl']);
    }

    public function __construct($account_id, $token, $base_api_url, $base_download_url)
    {
        $this->accountId = $account_id;
        $this->token = $token;
        $this->apiBase = $base_api_url;
        $this->downloadBase = $base_download_url;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    public function getToken()
    {
        return $this->token;
    }

    public function getBaseAPIURL()
    {
        return $this->apiBase;
    }

    public function getBaseDownloadURL()
    {
        return $this->downloadBase;
    }

    public function jsonSerialize()
    {
        return [
            'accountId' => $this->accountId,
            'authorizationToken' => $this->token,
            'downloadUrl' => $this->downloadBase,
            'apiUrl' => $this->apiBase
        ];
    }
}