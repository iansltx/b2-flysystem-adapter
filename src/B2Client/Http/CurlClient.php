<?php

namespace iansltx\B2Client\Http;

class CurlClient implements ClientInterface
{
    protected static $baseOptions = [CURLOPT_RETURNTRANSFER => true, CURLOPT_HEADER => true];
    protected $userOptions = [];

    public function __construct($userOptions = [])
    {
        $this->userOptions = $userOptions;
    }

    public function getRawWithBasicAuth($url, $userName, $password) {
        return $this->sendInternal($url, 'GET', [], [CURLOPT_USERPWD => $userName . ':' . $password], [], false);
    }

    public function postJson($url, array $headers = [], $data = []) {
        return $this->sendInternal($url, 'POST', $headers, [], $data);
    }

    public function postRaw($url, array $headers, $data) {
        return $this->sendInternal($url, 'POST', $headers, [], $data);
    }

    public function getRaw($url, array $headers, $headersOnly = false) {
        return $this->sendInternal($url, $headersOnly ? 'HEAD' : 'GET', $headers);
    }

    protected function sendInternal($url, $verb = 'GET', array $headers = [], array $opts = [],
                                    $data = [], $decode = true)
    {
        $verb = strtoupper($verb);

        $curlHeaders = [];
        if ($verb !== 'GET' && !isset($headers['content-type']) && is_array($data)) {
            $headers['content-type'] = 'application/json';
            $data = json_encode($data);
        }
        foreach ($headers as $k => $v) {
            $curlHeaders[] = $k . ': ' . $v;
        }
        $opts[CURLOPT_HTTPHEADER] = $curlHeaders;

        if ($verb === 'GET') {
            $ch = $this->getHandle($url . (count($data) ? ('?' . http_build_query($data)) : ''), $opts);
        } else {
            $ch = $this->getHandle($url, [CURLOPT_POSTFIELDS => $data] + $opts);
        }

        $res = curl_exec($ch);
        $httpCOde = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $info = curl_getinfo($ch);
        curl_close($ch);
        if (!$res) {
            throw new ClientException('Empty response; ' . json_encode($info));
        }

        // discard sets of headers prior to the final one...and make sure the body doesn't
        // include stray headers (e.g. if running through a proxy that emits an initial 200
        // on-connect)
        $body = $res;
        do {
            list($rawHeaders, $body) = explode("\r\n\r\n", $body, 2);
        } while (strpos($body, 'HTTP/') === 0 && strpos($body, "\r\n\r\n") !== false);

        $resHeaders = [];
        foreach (array_slice(explode("\r\n", $rawHeaders), 1) as $rawHeader) {
            list($key, $value) = explode(': ', $rawHeader, 2);
            $resHeaders[$key] = $value;
        }

        if ($decode) {
            $decoded = json_decode($body, JSON_OBJECT_AS_ARRAY);
            if ($decoded === false || $decoded === null) {
                throw new ClientException('JSON decoding error: ' . json_last_error_msg() . ', ' . $body);
            }
            return new Response($decoded, $resHeaders, $httpCOde);
        }

        return new Response($body, $resHeaders, $httpCOde);
    }

    protected function getHandle($url, $options = [])
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, static::$baseOptions);
        curl_setopt_array($ch, $this->userOptions);
        curl_setopt_array($ch, $options);
        return $ch;
    }
}
