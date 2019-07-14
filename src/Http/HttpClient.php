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

    public function get(string $path, array $body = [], array $options = []) 
    {
        $path .= (strpos($path, '?') === false) ? '?' : '&';
        $path .= urldecode(http_build_query($body));

        return ($this->lastResponse = $this->httpClient->get($path, $options))->getBody();
    }

    public function post(string $path, array $body, array $options = []) 
    {
        $options = array_merge($options, [
            'form_params' => $body
        ]);

        return ($this->lastResponse = $this->httpClient->post($path, $options))->getBody();
    }

    public function postJson(string $path, array $body, array $options = []) 
    {
        $options = array_merge($options, [
            'json' => $body
        ]);

        return ($this->lastResponse = $this->httpClient->post($path, $options))->getBody();
    }

    public function postMultiPart(string $path, array $body, array $options = [])
    {
        $options = array_merge($options, [
            'multipart' => $body
        ]);

        return ($this->lastResponse = $this->httpClient->post($path, $options))->getBody();
    }

    public function delete(string $path, array $options = [])
    {
        return ($this->lastResponse = $this->httpClient->delete($path, $options))->getBody();
    }

    public function patch(string $path, $body = null, array $options = [])
    {
        return ($this->lastResponse = $this->httpClient->patch($path, $options))->getBody();
    }

    public function put(string $path, $body = null, array $options = [])
    {
        $options = array_merge($options, [
            'form_params' => $body
        ]);

        return ($this->lastResponse = $this->httpClient->put($path, $options))->getBody();
    }

    public function putJson(string $path, $body = null, array $options = [])
    {
        $options = array_merge($options, [
            'json' => $body
        ]);
    
        return ($this->lastResponse = $this->httpClient->put($path, $options))->getBody();
    }

    public function putMultiPart(string $path, $body = null, array $options = [])
    {
        $options = array_merge($options, [
            'multipart' => $body
        ]);
    
        return ($this->lastResponse = $this->httpClient->put($path, $options))->getBody();
    }

    public function lastResponse() : Response
    {
        return $this->lastResponse;
    }
}