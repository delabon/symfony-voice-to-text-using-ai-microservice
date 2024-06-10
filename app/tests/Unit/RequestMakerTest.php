<?php

namespace App\Tests\Unit;

use App\Exception\ApiServerErrorException;
use App\Exception\ApiServerOverloadedException;
use App\Exception\InvalidApiSecretException;
use App\Exception\RateLimitReachedException;
use App\RequestMaker;
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

    public function testThrowsExceptionWhenInvalidApiSecret(): void
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

        $this->expectException(InvalidApiSecretException::class);
        $this->expectExceptionMessage('Invalid API secret.');
        $this->expectExceptionCode(401);

        $requestMaker->make($formData, $headers);
    }

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

        $this->expectException(RateLimitReachedException::class);
        $this->expectExceptionMessage('Rate limit reached for requests.');
        $this->expectExceptionCode(429);

        $requestMaker->make($formData, $headers);
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

        $this->expectException(ApiServerErrorException::class);
        $this->expectExceptionMessage('API server error.');
        $this->expectExceptionCode(500);

        $requestMaker->make($formData, $headers);
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

        $this->expectException(ApiServerOverloadedException::class);
        $this->expectExceptionMessage('API server is overloaded.');
        $this->expectExceptionCode(503);

        $requestMaker->make($formData, $headers);
    }
}