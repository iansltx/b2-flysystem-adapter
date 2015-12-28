<?php

namespace iansltx\B2Client\Model;

use iansltx\B2Client\Client;

class BucketSet implements \Countable, \ArrayAccess, \Iterator
{
    use ResultSetTrait;

    protected $client;

    public function __construct($data, Client $client)
    {
        $this->client = $client;
        foreach ($data['buckets'] as $row) {
            $this->items[] = new Bucket($row, $client);
        }
    }

    /* Result set specific methods */

    public function getIds()
    {
        return array_values(array_map(function(Bucket $bucket) {
            return $bucket->getId();
        }, $this->items));
    }

    public function getNames()
    {
        return array_values(array_unique(array_map(function(Bucket $bucket) {
            return $bucket->getName();
        }, $this->items)));
    }
}
