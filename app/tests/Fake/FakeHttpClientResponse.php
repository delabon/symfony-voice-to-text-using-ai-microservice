<?php

namespace App\Tests\Fake;

use Symfony\Contracts\HttpClient\ResponseInterface;

class FakeHttpClientResponse implements ResponseInterface
{
    public function getStatusCode(): int
    {
        return 200;
    }

    public function getHeaders(bool $throw = true): array
    {
        return [];
    }

    public function getContent(bool $throw = true): string
    {
        return '';
    }

    public function toArray(bool $throw = true): array
    {
        return [];
    }

    public function cancel(): void
    {
    }

    public function getInfo(?string $type = null): mixed
    {
        return null;
    }
}