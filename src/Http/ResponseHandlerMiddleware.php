<?php

namespace Tustin\Xbox\Http;

use Tustin\PlayStation\Http\ResponseParser;
use GuzzleHttp\Psr7\Response;

use Tustin\PlayStation\Exception\PlayStationApiException;
use Tustin\PlayStation\Exception\UnauthorizedException;
use Tustin\PlayStation\Exception\NotFoundException;
use Tustin\PlayStation\Exception\AccessDeniedException;

final class ResponseHandlerMiddleware
{
    private $accessToken;

    public function __invoke(Response $response, array $options = [])
    {
        if ($this->isSuccessful($response)) {
            return $response;
        }

       $this->handleErrorResponse($response);
    }

    /**
     * Checks if the HTTP status code is successful.
     *
     * @param Response $response The response
     * @return bool
     */
    public function isSuccessful(Response $response) : bool
    {
        return $response->getStatusCode() < 400;
    }

    /**
     * Handles unsuccessful error codes by throwing the proper exception.
     *
     * @param Response $response The response
     * @return void
     */
    public function handleErrorResponse(Response $response) : void
    {
        throw new Exception('Request failed');
    }
}