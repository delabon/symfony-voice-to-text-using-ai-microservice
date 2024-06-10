<?php

namespace App\Tests\Unit;

use App\Service\RequestMaker;
use App\Tests\Fake\FakeHttpClient;
use App\Tests\Fake\FakeHttpClientResponse;
use App\Tests\Trait\RequestHeaderAndFormDataCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RequestMakerTest extends TestCase
{
    use RequestHeaderAndFormDataCreator;

    public function testMakesRequestCorrectly(): void
    {
        list($headers, $formData) = $this->createRequestHeadersAndFormData();

        $fakeHttpClientMock = $this->createMock(FakeHttpClient::class);
        $fakeHttpClientMock->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.openai.com/v1/audio/transcriptions',
                [
                    'headers' => $headers->toArray(),
                    'body' => $formData->toIterable(),
                ]
            )->willReturn(new FakeHttpClientResponse());

        $requestMaker = new RequestMaker($fakeHttpClientMock);

        $response = $requestMaker->make($formData, $headers);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(200, $response->getStatusCode());
    }
}