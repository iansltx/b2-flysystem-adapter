<?php

namespace iansltx\B2Client\Model;

class FileSet extends ResultSet
{
    public function hasNextSet()
    {
        return $this->nextName !== null;
    }

    public function getNextSet($limit = null)
    {
        return $this->client->listFiles($this->bucketId, $this->nextName, $limit ?: $this->limit);
    }

    protected function importFileData(array $data)
    {
        foreach ($data as $file) {
            $this->items[$file['fileName']] = File::fromList($file, $client, $bucketId);
        }
    }

    public function getNames()
    {
        return array_keys($this->items);
    }
}
