<?php


namespace Utils;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

final class Request
{
    private $responseBodyLength = 1024;
    private $client;


    public function __construct($options = [])
    {
        $this->client = new Client($options);
    }

    /**
     * @param string $url
     * @param array $params
     * @return array
     */
    public function get(string $url, array $params = []): array
    {
        return $this->getResponse('get', $url.'?'.http_build_query($params));
    }

    /**
     * @param int $responseBodyLength
     */
    public function setResponseBodyLength(int $responseBodyLength): void
    {
        $this->responseBodyLength = $responseBodyLength;
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $params
     * @return array
     */
    private function getResponse(string $method, string $url, array $params = []): array
    {
        $method = strtolower($method);
        try {
            $response = $this->client
                ->$method($url)
                ->getBody()
                ->read($this->responseBodyLength)
            ;
            $body['data'] = @json_decode($response, true) ?? [];
        }catch (GuzzleException $exception){
            $body['error'] = $exception->getMessage();
        }

        return $body;
    }
}
