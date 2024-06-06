<?php

namespace App\Tests\Fake;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class FakeHttpClient implements HttpClientInterface
{

    public function request(string $method, string $url, array $options = []): ResponseInterface
    {
        return new FakeHttpClientResponse();
    }

    public function stream(iterable|ResponseInterface $responses, ?float $timeout = null): ResponseStreamInterface
    {
        return new FakeHttpClientResponseStream();
    }

    public function withOptions(array $options): static
    {
        return $this;
    }
}