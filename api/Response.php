<?php


namespace API;

class Response
{
    private int $responseCode;
    private array $response;
    private array $headers;

    /**
     * Response constructor.
     * @param int $responseCode
     * @param array $response
     * @param array|null $headers
     */
    public function __construct(int $responseCode, array $response, array $headers = null)
    {
        $this->responseCode = $responseCode;
        $this->response = $response;
        if (is_null($headers)) {
            $this->headers = array('Access-Control-Allow-Origin' => '*',
                'Content-Type', 'application/json; charset=UTF-8');
        } else {
            $this->headers = $headers;
        }
    }

    /**
     * @return int
     */
    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @return array|string[]
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

}