<?php

namespace iansltx\B2Client\Http;

class Response
{
    protected $body;
    protected $headers = [];
    protected $lcHeaders = null;
    protected $status = null;

    public function __construct($body, $headers = [], $status_code = 200)
    {
        $this->body = $body;
        $this->headers = $headers;
        $this->status = $status_code;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getStatusCode()
    {
        return $this->status;
    }

    public function isSuccess()
    {
        return $this->status >= 200 && $this->status <= 399;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getHeader($name)
    {
        if (isset($this->headers[$name])) {
            return $this->headers[$name];
        }

        return $this->getLcHeader($name);
    }

    protected function getLcHeader($name)
    {
        if ($this->lcHeaders === null) {
            $this->lcHeaders = [];
            foreach ($this->headers as $k => $v) {
                $this->lcHeaders[strtolower($k)] = $v;
            }
        }

        return isset($this->lcHeaders[strtolower($name)]) ? $this->lcHeaders[strtolower($name)] : null;
    }
}
