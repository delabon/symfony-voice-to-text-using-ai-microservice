<?php

namespace App\Tests\Fake;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FakeHttpClientException extends \Exception implements ClientExceptionInterface
{
    public function getResponse(): ResponseInterface
    {
        return new FakeHttpClientResponse();
    }
}