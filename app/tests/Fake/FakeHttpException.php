<?php

namespace App\Tests\Fake;

use Exception;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FakeHttpException extends Exception implements HttpExceptionInterface
{
    public function __toString()
    {
        return '';
    }

    public function getResponse(): ResponseInterface
    {
        return New FakeHttpClientResponse();
    }
}