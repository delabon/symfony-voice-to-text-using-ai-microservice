<?php

namespace App\Tests\Fake;

use Symfony\Contracts\HttpClient\ChunkInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class FakeHttpClientResponseStream implements ResponseStreamInterface
{

    public function next(): void
    {
    }

    public function valid(): bool
    {
        return true;
    }

    public function rewind(): void
    {
    }

    public function key(): ResponseInterface
    {
        return new FakeHttpClientResponse();
    }

    public function current(): ChunkInterface
    {
        return new FakeHttpClientChunk();
    }
}