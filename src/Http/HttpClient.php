<?php

namespace Tustin\Xbox\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\Request;

use GuzzleHttp\Psr7\Response;

class HttpClient
{
    protected $httpClient;

    private $lastResponse;

    public function get(string $path, array $body = [], array $headers = []) 
    {
        $path .= (strpos($path, '?') === false) ? '?' : '&';
        $path .= urldecode(http_build_query($body));

        return ($this->lastResponse = $this->httpClient->get($path,  [
            'headers' => $headers
        ]))->getBody();
    }

    public function post(string $path, array $body, array $headers = []) 
    {
        return ($this->lastResponse = $this->httpClient->post($path, [
            'form_params' => $body,
            'headers' => $headers
        ]))->getBody();
    }

    public function postJson(string $path, array $body, array $headers = []) 
    {
        return ($this->lastResponse = $this->httpClient->post($path, [
            'json' => $body,
            'headers' => $headers
        ]))->getBody();
    }

    public function postMultiPart(string $path, array $body, array $headers = [])
    {
        return ($this->lastResponse = $this->httpClient->post($path, [
            'multipart' => $body,
            'headers' => $headers
        ]))->getBody();
    }

    public function delete(string $path, array $headers = [])
    {
        return ($this->lastResponse = $this->httpClient->delete($path, [
            'headers' => $headers
        ]))->getBody();
    }

    public function patch(string $path, $body = null, array $headers = [])
    {
        return ($this->lastResponse = $this->httpClient->patch($path, [
            'headers' => $headers
        ]))->getBody();
    }

    public function put(string $path, $body = null, array $headers = [])
    {
        return ($this->lastResponse = $this->httpClient->put($path, [
            'form_params' => $body,
            'headers' => $headers
        ]))->getBody();
    }

    public function putJson(string $path, $body = null, array $headers = [])
    {
        return ($this->lastResponse = $this->httpClient->put($path, [
            'json' => $body,
            'headers' => $headers
        ]))->getBody();
    }

    public function putMultiPart(string $path, $body = null, array $headers = [])
    {
        return ($this->lastResponse = $this->httpClient->put($path, [
            'multipart' => $body,
            'headers' => $headers
        ]))->getBody();
    }

    public function lastResponse() : Response
    {
        return $this->lastResponse;
    }
}