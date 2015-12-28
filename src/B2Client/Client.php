<?php

namespace iansltx\B2Client;

use iansltx\B2Client\Http\ClientException;
use iansltx\B2Client\Http\ClientInterface;
use iansltx\B2Client\Http\CurlClient;
use iansltx\B2Client\Model\Bucket;
use iansltx\B2Client\Model\BucketSet;
use iansltx\B2Client\Model\FileSet;
use iansltx\B2Client\Model\VersionSet;
use iansltx\B2Client\Model\File;

class Client
{
    const MAX_UNIX_TIMESTAMP = 2 << 32 - 1;
    const DEFAULT_RESULTS_PER_PAGE = 100;
    const MAX_RESULTS_PER_PAGE = 1000;

    protected $settings;
    protected $http;

    protected $useMbStrlen = false;

    public function __construct(ServerSettingsInterface $settings, ClientInterface $http = null, $useMbStrlen = false) {
        $this->settings = $settings;
        if ($http) {
            $this->http = $http;
        } else if ($settings instanceof Credentials) {
            $this->http = $settings->getHttpClient();
        } else {
            $this->http = new CurlClient();
        }

        // conduct evasive maneuvers if mbstring is set to overload strlen(), or on user request
        $this->useMbStrlen = $useMbStrlen || ini_get('mbstring.func_overload') !== "0";
    }

    /* Bucket actions */

    public function listBuckets()
    {
        return new BucketSet($this->postJson('list_buckets', ['accountId' => $this->settings->getAccountId()]), $this);
    }

    public function createBucket($name, $visibility)
    {
        return new Bucket($this->postJson('create_bucket', [
            'bucketName' => $name,
            'bucketType' => $this->assertValidVisibility($visibility),
            'accountId' => $this->settings->getAccountId()
        ]), $this);
    }

    public function setBucketVisibilityById($id, $visibility)
    {
        return new Bucket($this->postJson('update_bucket', [
            'bucketId' => $id,
            'bucketType' => $visibility,
            'accountId' => $this->settings->getAccountId()
        ]), $this);
    }

    public function deleteBucketById($id)
    {
        $this->postJson('delete_bucket', [
            'bucketId' => $id,
            'accountId' => $this->settings->getAccountId()
        ]);
        return true;
    }

    /* File list actions */

    public function listFiles($bucketId, $startFileName = null, $limit = self::DEFAULT_RESULTS_PER_PAGE) {
        if ($limit > static::MAX_RESULTS_PER_PAGE) {
            throw new \InvalidArgumentException("Limit needs to be at most " . static::MAX_RESULTS_PER_PAGE);
        }

        return new FileSet($bucketId, $this->postJson('list_file_names', [
            'bucketId' => $bucketId,
            'maxFileCount' => $limit
        ] + ($startFileName ? ['startFileName' => $startFileName] : [])), $this, $limit);
    }

    public function listVersions($bucketId, $startFileName = null, $startFileId = null, $limit = self::DEFAULT_RESULTS_PER_PAGE) {
        if ($limit > static::MAX_RESULTS_PER_PAGE) {
            throw new \InvalidArgumentException("Limit needs to be at most " . static::MAX_RESULTS_PER_PAGE);
        }

        return new VersionSet($bucketId, $this->postJson('list_file_versions', [
            'bucketId' => $bucketId,
            'maxFileCount' => $limit
        ] + ($startFileName ? ['startFileName' => $startFileName] : [])
            + ($startFileId ? ['startFileId' => $startFileId] : [])), $this, $limit);
    }

    /* File actions */

    /**
     * @param $bucketId
     * @param $name
     * @param $contents
     * @param \DateTimeInterface|int|float|false|null $modifiedAt
     * @param $mimeType
     * @param array $meta
     * @return string file ID
     */
    public function uploadContents($bucketId, $name, $contents, $modifiedAt = null, $mimeType = null, array $meta = [])
    {
        $params = $this->getUploadParams($bucketId);
        return File::fromInfo($this->http->postRaw($params['uploadUrl'], [
            'Authorization' => $params['authorizationToken'],
            'X-Bz-File-Name' => urlencode($name),
            'Content-Type' => $mimeType ?: 'b2/x-auto',
            'Content-Length' => $this->useMbStrlen ? mb_strlen($contents, '8bit') : strlen($contents),
            'X-Bz-Content-Sha1' => sha1($contents)
        ] + $this->getModifiedAtHeader($modifiedAt) + $this->getMetaHeaders($meta), $contents)->getBody(),
            $this);
    }

    public function deleteVersion($fileName, $fileId)
    {
        $this->postJson('delete_file_version', [
            'fileName' => $fileName,
            'fileId' => $fileId
        ]);
        return true;
    }

    public function hideFileByName($bucketId, $fileName)
    {
        return File::fromList($this->postJson('hide_file', [
            'bucketId' => $bucketId,
            'fileName' => $fileName
        ]), $this, $bucketId);
    }

    public function getFileById($id, $withContents = true, $rawContents = false)
    {
        $response = $this->getResponse('/b2api/v1/b2_download_file_by_id?fileId=' . $id, !$withContents);
        return $rawContents ? $response->getBody() : File::fromDownloadResponse($response, $this);
    }

    public function getFileByName($bucketName, $fileName, $withContents = true, $rawContents = false)
    {
        $response = $this->getResponse('/file/' . $bucketName . '/' . $fileName, !$withContents);
        return $rawContents ? $response->getBody() : File::fromDownloadResponse($response, $this);
    }

    public function getFileInfoById($id, $asArray = false)
    {
        $res = $this->postJson('get_file_info', ['fileId' => $id]);
        return $asArray ? $res : File::fromInfo($res, $this);
    }

    /* internal calls */

    protected function assertValidVisibility($visibility) {
        if (!in_array($visibility, [BucketVisibility::ALL_PRIVATE, BucketVisibility::ALL_PUBLIC])) {
            throw new \InvalidArgumentException("Bucket must have a visibility, see BucketVisibility constants.");
        }
        return $visibility;
    }

    protected function getUploadParams($bucketId) // uploadUrl, authorizationToken in array
    {
        return $this->postJson('get_upload_url', ['bucketId' => $bucketId]);
    }

    protected function getModifiedAtHeader($modifiedAt) {
        if ($modifiedAt === false) { // don't add metadata
            return [];
        }

        if ($modifiedAt === null || $modifiedAt === true) {
            $modifiedAt = time() * 1000;
        } elseif ($modifiedAt instanceof \DateTimeInterface) {
            $modifiedAt = $modifiedAt->getTimestamp() * 1000;
        } elseif ($modifiedAt <= self::MAX_UNIX_TIMESTAMP) {
            $modifiedAt *= 1000;
        }

        return ['X-Bz-Info-src_last_modified_millis' => $modifiedAt];
    }

    protected function getMetaHeaders(array $meta = []) {
        $headers = [];
        foreach ($meta as $k => $v) {
            $headers['X-Bz-Info-' . $k] = urlencode($v);
        }
        return $headers;
    }

    protected function postJson($path, $data = [])
    {
        $response = $this->http->postJson($this->settings->getBaseAPIURL() . '/b2api/v1/b2_' . $path,
            ['Authorization' => $this->settings->getToken()], $data);
        if (!$response->isSuccess()) {
            throw new ClientException(json_encode($response->getBody()['message']), $response->getStatusCode());
        }
        return $response->getBody();
    }

    protected function getResponse($path, $headersOnly = false)
    {
        $response = $this->http->getRaw($this->settings->getBaseDownloadURL() . $path,
            ['Authorization' => $this->settings->getToken()], $headersOnly);
        if (!$response->isSuccess()) {
            throw new ClientException(json_encode($response->getBody()['message']), $response->getStatusCode());
        }
        return $response;
    }
}
