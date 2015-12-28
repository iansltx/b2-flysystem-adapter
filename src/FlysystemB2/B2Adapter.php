<?php

namespace iansltx\FlysystemB2;

use iansltx\B2Client\Model\Bucket;
use iansltx\B2Client\Client;
use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;
use iansltx\FlysystemB2\Config as AdapterConfig;

class B2Adapter extends AbstractAdapter implements AdapterInterface, AdapterConfig
{
    protected $client;
    protected $configFlags;
    protected $bucketName;
    protected $bucketId;

    /**
     * @param array|Bucket $bucketOrConfig either array with client and bucket name/id or a B2Client Bucket object
     *   required keys: client (Client object), bucketId\id (bucket ID), bucketName\name (bucket name)
     * @param int $adapterFlags OR'd AdapterConfig flags
     */
    public function __construct($bucketOrConfig, $adapterFlags = 0)
    {
        throw new \LogicException('Flysystem adapter isn\'t built yet!');

        if ($bucketOrConfig instanceof Bucket) { //
            $this->bucketId = $bucketOrConfig->getId();
            $this->bucketName = $bucketOrConfig->getName();
            $this->client = $bucketOrConfig->getClient();
        } else {
            if (!isset($bucketOrConfig['client']) || !($bucketOrConfig['client'] instanceof Client)) {
                throw new \InvalidArgumentException('B2Client Client object required in "client" key');
            }
            $this->client = $bucketOrConfig['client'];
            if (!isset($bucketOrConfig['id']) && !isset($bucketOrConfig['bucketId'])) {
                throw new \InvalidArgumentException('Bucket ID required; use "id" or "bucketId" as key');
            }
            $this->bucketId = isset($bucketOrConfig['id']) ? $bucketOrConfig['id'] : $bucketOrConfig['bucketId'];
            if (!isset($bucketOrConfig['name']) && !isset($bucketOrConfig['bucketName'])) {
                throw new \InvalidArgumentException('Bucket name required; use "name" or "bucketName" as key');
            }
            $this->bucketName = isset($bucketOrConfig['name']) ? $bucketOrConfig['name'] : $bucketOrConfig['bucketName'];
        }

        $this->configFlags = $adapterFlags;
    }

    /**
     * Write a new file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function write($path, $contents, Config $config)
    {
        // TODO: Implement write() method.
    }

    /**
     * Write a new file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function writeStream($path, $resource, Config $config)
    {
        // TODO: Implement writeStream() method.
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function update($path, $contents, Config $config)
    {
        // TODO: Implement update() method.
    }

    /**
     * Update a file using a stream.
     *
     * @param string $path
     * @param resource $resource
     * @param Config $config Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function updateStream($path, $resource, Config $config)
    {
        // TODO: Implement updateStream() method.
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        // TODO: Implement rename() method.
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        // TODO: Implement copy() method.
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        // TODO: Implement delete() method.
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDir($dirname)
    {
        // TODO: Implement deleteDir() method.
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     * @param Config $config
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config)
    {
        // TODO: Implement createDir() method.
    }

    /**
     * Set the visibility for a file.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return array|false file meta data
     */
    public function setVisibility($path, $visibility)
    {
        // TODO: Implement setVisibility() method.
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        // TODO: Implement has() method.
    }

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path)
    {
        // TODO: Implement read() method.
    }

    /**
     * Read a file as a stream.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function readStream($path)
    {
        // TODO: Implement readStream() method.
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool $recursive
     *
     * @return array
     */
    public function listContents($directory = '', $recursive = false)
    {
        // TODO: Implement listContents() method.
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path)
    {
        // TODO: Implement getMetadata() method.
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path)
    {
        // TODO: Implement getSize() method.
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        // TODO: Implement getMimetype() method.
    }

    /**
     * Get the timestamp of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path)
    {
        // TODO: Implement getTimestamp() method.
    }

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getVisibility($path)
    {
        // TODO: Implement getVisibility() method.
    }
}
