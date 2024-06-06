<?php

namespace App\Tests\Fake;

use Symfony\Contracts\HttpClient\ChunkInterface;

class FakeHttpClientChunk implements ChunkInterface
{

    public function isTimeout(): bool
    {
        return false;
    }

    public function isFirst(): bool
    {
        return true;
    }

    public function isLast(): bool
    {
        return true;
    }

    public function getInformationalStatus(): ?array
    {
        return [];
    }

    public function getContent(): string
    {
        return '';
    }

    public function getOffset(): int
    {
        return 1;
    }

    public function getError(): ?string
    {
        return null;
    }
}