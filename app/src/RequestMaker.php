<?php

namespace App;

use App\Exception\ApiServerErrorException;
use App\Exception\ApiServerOverloadedException;
use App\Exception\InvalidApiSecretException;
use App\Exception\RateLimitReachedException;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractMultipartPart;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class RequestMaker
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws InvalidApiSecretException
     * @throws RateLimitReachedException
     * @throws ApiServerErrorException
     * @throws ApiServerOverloadedException
     */
    public function make(AbstractMultipartPart $formDataPart, Headers $headers): ResponseInterface
    {
        $response = $this->client->request('POST', 'https://api.openai.com/v1/audio/transcriptions', [
            'headers' => $headers->toArray(),
            'body' => $formDataPart->bodyToIterable(),
        ]);

        return match ($response->getStatusCode()) {
            401 => throw new InvalidApiSecretException('Invalid API secret.', 401),
            429 => throw new RateLimitReachedException('Rate limit reached for requests.', 429),
            500 => throw new ApiServerErrorException('API server error.', 500),
            503 => throw new ApiServerOverloadedException('API server is overloaded.', 503),
            default => $response
        };
    }
}