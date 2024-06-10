<?php

namespace App\Tests\Unit;

use App\Service\RequestMaker;
use App\Tests\Fake\FakeHttpClient;
use App\Tests\Fake\FakeHttpClientResponse;
use App\Tests\Trait\RequestHeaderAndFormDataCreator;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
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

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testThrowsExceptionWhenInvalidAuthentication(): void
    {
        $fakeSecret = 'Invalid secret';
        list($headers, $formData) = $this->createRequestHeadersAndFormData($fakeSecret);

        $fakeResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $fakeResponseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(401);

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
            )->willReturn($fakeResponseMock);

        $requestMaker = new RequestMaker($fakeHttpClientMock);

        $response = $requestMaker->make($formData, $headers);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(401, $response->getStatusCode());
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testThrowsExceptionWhenRateLimitIsReached(): void
    {
        $fakeSecret = 'Invalid secret';
        list($headers, $formData) = $this->createRequestHeadersAndFormData($fakeSecret);

        $fakeResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $fakeResponseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(429);

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
            )->willReturn($fakeResponseMock);

        $requestMaker = new RequestMaker($fakeHttpClientMock);

        $response = $requestMaker->make($formData, $headers);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(429, $response->getStatusCode());
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testThrowsExceptionWhenApiServerHasAnError(): void
    {
        list($headers, $formData) = $this->createRequestHeadersAndFormData();

        $fakeResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $fakeResponseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(500);

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
            )->willReturn($fakeResponseMock);

        $requestMaker = new RequestMaker($fakeHttpClientMock);

        $response = $requestMaker->make($formData, $headers);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(500, $response->getStatusCode());
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testThrowsExceptionWhenApiServerIsOverloaded(): void
    {
        list($headers, $formData) = $this->createRequestHeadersAndFormData();

        $fakeResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $fakeResponseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(503);

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
            )->willReturn($fakeResponseMock);

        $requestMaker = new RequestMaker($fakeHttpClientMock);

        $response = $requestMaker->make($formData, $headers);

        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertSame(503, $response->getStatusCode());
    }
}