<?php

namespace iansltx\B2Client\Model;

use iansltx\B2Client\BucketVisibility;
use iansltx\B2Client\Client;

class Bucket
{
    protected $name;
    protected $id;
    protected $visibility;
    protected $accountId;

    protected $client;

    public function __construct($data, Client $client)
    {
        $this->client = $client;

        $this->name = $data['bucketName'];
        $this->id = $data['bucketId'];
        $this->visibility = $data['bucketType'];
        $this->accountId = $data['accountId'];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getAccountId()
    {
        return $this->accountId;
    }

    public function isPublic()
    {
        return $this->visibility === BucketVisibility::ALL_PUBLIC;
    }

    public function isPrivate()
    {
        return $this->visibility === BucketVisibility::ALL_PRIVATE;
    }

    public function setVisibility($visibility)
    {
        $this->client->setBucketVisibilityById($this->id, $visibility);
        $this->visibility = $visibility;
        return $this;
    }

    public function delete()
    {
        return $this->client->deleteBucketById($this->id);
    }

    public function listFiles($startFileName = null, $limit = 100)
    {
        return $this->client->listFiles($this->id, $startFileName, $limit);
    }

    public function listVersions($startFileName = null, $startFileId = null, $limit = 100)
    {
        return $this->client->listVersions($this->id, $startFileName, $startFileId, $limit);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function uploadContents($name, $contents, $modifiedAt = null, $mimeType = null, $meta = [])
    {
        return $this->client->uploadContents($this->id, $name, $contents, $modifiedAt, $mimeType, $meta);
    }
}
