<?php

namespace iansltx\B2Client\Model;

class VersionSet extends ResultSet
{
    public function hasNextSet()
    {
        return $this->nextId || $this->nextName;
    }

    public function getNextSet($limit = null)
    {
        return $this->client->listVersions($this->bucketId, $this->nextName, $this->nextId, $limit ?: $this->limit);
    }

    protected function importFileData(array $data)
    {
        foreach ($data as $file) {
            $this->items[$file['fileId']] = File::fromList($file, $client, $bucketId);
        }
    }

    public function getIds()
    {
        return array_keys($this->items);
    }
}
