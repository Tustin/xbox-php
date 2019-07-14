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

    private $accessToken;
    private $refreshToken;

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
        $authenticationCookies = new \GuzzleHttp\Cookie\CookieJar;

        $response = $this->get('https://login.live.com/oauth20_authorize.srf', [
            'response_type' => 'token',
            'redirect_uri' => 'https://login.live.com/oauth20_desktop.srf',
            'scope' => 'service::user.auth.xboxlive.com::MBI_SSL',
            'client_id' => self::CLIENT_ID
        ], [
            'cookies' => $authenticationCookies
        ]);

        if (preg_match("/sFTTag:'.*value=\"(.*)\"\/>'/", $response, $tag) === 0) {
            throw new \Exception("PFFT tag not found");
        }

        if (preg_match("/urlPost:'([A-Za-z0-9:\?_\-\.&\\/=]+)/", $response, $urlPost) === 0) {
            throw new \Exception("Redirect URL tag not found");
        }

        $ppftValue = $tag[1];
        $urlPostValue = $urlPost[1];

        $response = $this->post($urlPostValue, [
            'login' => $email,
            'loginfmt' => $email,
            'passwd' => $password,
            'PPFT' => $ppftValue
        ], [
            'cookies' => $authenticationCookies
        ]);

        if ($this->lastResponse()->getStatusCode() != 302) {
            throw new \Exception("Invalid status code from login response");
        }

        $redirectUrl = $this->lastResponse()->getHeaderLine('Location');

        if (preg_match("/access_token=(.+?)&/", $redirectUrl, $accessToken) === 0) {
            throw new \Exception("Unable to parse access token");
        }

        if (preg_match("/refresh_token=(.+?)&/", $redirectUrl, $refreshToken) === 0) {
            throw new \Exception("Unable to parse refresh token");
        }

        $response = $this->postJson('https://user.auth.xboxlive.com/user/authenticate', [
            'RelyingParty' => 'http://auth.xboxlive.com',
            'TokenType' => 'JWT',
            'Properties' => [
                'AuthMethod' => 'RPS',
                'SiteName' => 'user.auth.xboxlive.com',
                'RpsTicket' => $accessToken[1]
            ]
        ], [
            'headers' => [
                'x-xbl-contract-version' => 0
            ]
        ]);

        $userToken = $response->Token;

        $response = $this->postJson('https://xsts.auth.xboxlive.com/xsts/authorize', [
            'RelyingParty' => 'http://xboxlive.com',
            'TokenType' => 'JWT',
            'Properties' => [
                'UserTokens' => [
                    $userToken
                ],
                'SandboxId' => 'RETAIL',
            ]
            ], [
                'headers' => [
                    'x-xbl-contract-version' => 0
                ]
            ]);
    }
}