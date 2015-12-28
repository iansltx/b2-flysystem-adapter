<?php

namespace iansltx\B2Client\Http;

interface ClientInterface
{
    /**
     * @param $url
     * @param $userName
     * @param $password
     * @return Response
     */
    public function getRawWithBasicAuth($url, $userName, $password);

    /**
     * @param $url
     * @param array $headers
     * @param $data
     * @return Response
     */
    public function postJson($url, array $headers, $data);

    /**
     * @param $url
     * @param array $headers
     * @param bool $headersOnly
     * @return Response
     */
    public function getRaw($url, array $headers, $headersOnly = false);
}
