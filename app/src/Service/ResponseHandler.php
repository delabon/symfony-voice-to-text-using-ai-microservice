<?php

namespace App\Service;

use App\Exception\ApiServerErrorException;
use App\Exception\ApiServerOverloadedException;
use App\Exception\InvalidApiSecretException;
use App\Exception\RateLimitReachedException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use UnexpectedValueException;

class ResponseHandler
{
    /**
     * @param ResponseInterface $response
     * @return string
     * @throws ApiServerErrorException
     * @throws ApiServerOverloadedException
     * @throws ClientExceptionInterface
     * @throws InvalidApiSecretException
     * @throws RateLimitReachedException
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function handle(ResponseInterface $response): string
    {
        $json = match ($response->getStatusCode()) {
            401 => throw new InvalidApiSecretException('Invalid API secret.', 401),
            429 => throw new RateLimitReachedException('Rate limit reached for requests.', 429),
            500 => throw new ApiServerErrorException('API server error.', 500),
            503 => throw new ApiServerOverloadedException('API server is overloaded.', 503),
            default => json_decode($response->getContent(false), true)
        };

        if ($json === null) {
            throw new UnexpectedValueException('Invalid response format.');
        }

        if (!array_key_exists('text', $json)) {
            throw new UnexpectedValueException('The response does not have the text key.');
        }

        return $json['text'];
    }
}