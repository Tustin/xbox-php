<?php

namespace Tustin\Xbox;

use Tustin\Xbox\Http\HttpClient;
use Tustin\Xbox\Http\ResponseParser;
use Tustin\Xbox\Http\TokenMiddleware;
use Tustin\Xbox\Http\ResponseHandlerMiddleware;

use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\HandlerStack;

class Client extends HttpClient
{
    private const CLIENT_ID = '0000000048093EE3';

    private $guzzleOptions;

    /**
     * @param array $guzzleOptions Guzzle options
     */
    public function __construct(array $guzzleOptions = [])
    {
        if (!isset($guzzleOptions['handler']))
        {
            $guzzleOptions['handler'] = HandlerStack::create();
        }

        $guzzleOptions['allow_redirects'] = false;

        $this->guzzleOptions = $guzzleOptions;

        $this->httpClient = new \GuzzleHttp\Client($this->guzzleOptions);

        $config  = $this->httpClient->getConfig();
        $handler = $config['handler'];

        $handler->push(
            Middleware::mapResponse(
                new ResponseHandlerMiddleware
            )
        );
        $handler->push(
            Middleware::mapResponse(function (\Psr\Http\Message\ResponseInterface $response) {
                return new \Tustin\Xbox\Http\JsonAwareResponseMiddleware(
                    $response->getStatusCode(),
                    $response->getHeaders(),
                    $response->getBody(),
                    $response->getProtocolVersion(),
                    $response->getReasonPhrase()
                );
             })
        );
    }

    /**
     * Create a new Client instance.
     *
     * @param array $guzzleOptions Guzzle options
     * @return \Tustin\Xbox\Client
     */
    public static function create(array $guzzleOptions = []) : Client
    {
        return new static($guzzleOptions);
    }

    public function login(string $email, string $password) : void
    {
        $response = $this->get('https://login.live.com/oauth20_authorize.srf', [
            'response_type' => 'token',
            'redirect_uri' => 'https://login.live.com/oauth20_desktop.srf',
            'scope' => 'service::user.auth.xboxlive.com::MBI_SSL',
            'client_id' => self::CLIENT_ID
        ]);

        var_dump($response);
    }
}