<?php

namespace App\Tests\Fake;

use Exception;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class FakeTransportException extends Exception implements TransportExceptionInterface
{
}