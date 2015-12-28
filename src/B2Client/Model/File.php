<?php

namespace iansltx\B2Client\Model;

use iansltx\B2Client\Client;
use iansltx\B2Client\Http\Response;

class File
{
    /** @var Client */
    protected $client;

    protected $id;
    protected $name;
    protected $size;
    protected $uploadedAt;
    protected $isHidden = false;

    protected $contents = null;

    protected $sha1 = null;
    protected $mimeType = null;
    protected $meta = null;

    protected $bucketId;

    public static function fromDownloadResponse(Response $response, Client $client)
    {
        $f = new self;

        $f->client = $client;
        $f->contents = $response->getBody() ?: null;
        $f->meta = [];

        foreach ($response->getHeaders() as $k => $v) {
            switch ($k) {
                case 'Content-Length':
                    $f->size = $v;
                    break;
                case 'Content-Type':
                    $f->mimeType = $v;
                    break;
                case 'X-Bz-File-Id':
                    $f->id = $v;
                    break;
                case 'X-Bz-File-Name':
                    $f->name = $v;
                    break;
                case 'X-Bz-Content-Sha1':
                    $f->sha1 = $v;
                    break;
                default:
                    if (strpos($k, 'X-Bz-Info-') === 0) {
                        $f->meta[substr($k, 10)] = urldecode($v);
                    }
                    break;
            }
        }

        $f->uploadedAt = $f->meta['src_last_modified_millis'];

        return $f;
    }

    public static function fromInfo($data, Client $client)
    {
        $f = new self;

        $f->client = $client;

        $f->id = $data['fileId'];
        $f->name = $data['fileName'];
        $f->bucketId = $data['bucketId'];
        $f->size = $data['contentLength'];
        $f->sha1 = $data['contentSha1'];
        $f->mimeType = $data['contentType'];
        $f->meta = $data['fileInfo'];

        if (isset($data['fileInfo']['src_last_modified_millis'])) {
            $f->uploadedAt = $data['fileInfo']['src_last_modified_millis'];
        }

        return $f;
    }

    public static function fromList($data, Client $client, $bucketId)
    {
        $f = new self;

        $f->client = $client;

        $f->id = $data['fileId'];
        $f->name = $data['fileName'];
        $f->size = $data['size'];
        $f->uploadedAt = $data['uploadTimestamp'];
        if (isset($data['action']) && $data['action'] === 'hide') {
            $f->isHidden = true;
        }
        $f->bucketId = $bucketId;

        return $f;
    }

    protected function __construct() {}

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getSizeInBytes()
    {
        return $this->size;
    }

    public function getUploadedAt()
    {
        if (!$this->uploadedAt) {
            return null;
        }

        return (new \DateTimeImmutable())->setTimestamp($this->uploadedAt / 1000);
    }

    public function getMeta($key = null)
    {
        if ($this->meta === null) {
            $this->hydrateExtendedInfo();
        }
        return !$key ? $this->meta : $this->meta[$key];
    }

    public function getSha1()
    {
        return $this->sha1 ?: $this->hydrateExtendedInfo()->sha1;
    }

    public function getMimeType()
    {
        return $this->mimeType ?: $this->hydrateExtendedInfo()->sha1;
    }

    public function hideNewestVersion()
    {
        if (!$this->isHidden) {
            $this->client->hideFileByName($this - $this->bucketId, $this->name);
            $this->isHidden = true;
        }
        return $this;
    }

    public function getContents()
    {
        if (!$this->contents) {
            $this->contents = $this->client->getFileById($this->id, true, true);
        }

        return $this->contents;
    }

    protected function hydrateExtendedInfo()
    {
        if (!isset($this->sha1)) {
            $data = $this->client->getFileById($this->id, true);
            $this->sha1 = $data['contentSha1'];
            $this->mimeType = $data['contentType'];
            $this->meta = $data['fileInfo'];
        }

        return $this;
    }
}
