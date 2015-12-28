<?php

namespace iansltx\B2Client\Model;

use iansltx\B2Client\Client;

abstract class ResultSet implements \Countable, \ArrayAccess, \Iterator
{
    use ResultSetTrait;

    protected $client;
    protected $bucketId;
    protected $limit;

    protected $nextId;
    protected $nextName;

    public function __construct($bucketId, $data, Client $client, $limit = Client::DEFAULT_RESULTS_PER_PAGE)
    {
        $this->client = $client;
        $this->bucketId = $bucketId;
        $this->limit = $limit;

        $this->nextId = isset($data['nextFileId']) ? $data['nextFileId'] : null;
        $this->nextName = isset($data['nextFileName']) ? $data['nextFileName'] : null;

        $this->importFileData($data['files']);
    }

    /* Result set specific methods; may be overridden by extended classes for higher efficiency */

    public function getBucketId()
    {
        return $this->bucketId;
    }

    public function getIds()
    {
        return array_values(array_map(function(File $file) {
            return $file->getId();
        }, $this->items));
    }

    public function getNames()
    {
        return array_values(array_unique(array_map(function(File $file) {
            return $file->getName();
        }, $this->items)));
    }

    /* Abstract methods */

    abstract public function hasNextSet();
    abstract public function getNextSet($limit = null);
    abstract protected function importFileData(array $data);
}
