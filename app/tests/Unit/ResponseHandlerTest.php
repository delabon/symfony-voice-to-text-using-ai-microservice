<?php

namespace App\Tests\Unit;

use App\Exception\ApiServerErrorException;
use App\Exception\ApiServerOverloadedException;
use App\Exception\InvalidApiSecretException;
use App\Exception\RateLimitReachedException;
use App\Service\ResponseHandler;
use App\Tests\Fake\FakeHttpClientResponse;
use App\Tests\Trait\RequestHeaderAndFormDataCreator;
use PHPUnit\Framework\TestCase;

class ResponseHandlerTest extends TestCase
{
    use RequestHeaderAndFormDataCreator;

    public function testHandlesVoiceToTextResponseCorrectly(): void
    {
        $mockClientResponse = $this->createMock(FakeHttpClientResponse::class);
        $mockClientResponse->expects($this->once())
            ->method('getContent')
            ->willReturn('{"text":"My fake response text"}');

        $responseHandler = new ResponseHandler();

        $text = $responseHandler->handle($mockClientResponse);

        $this->assertSame("My fake response text", $text);
    }

    public function testHandlesVoiceToTextResponseCorrectlyWithDifferentResult(): void
    {
        $mockClientResponse = $this->createMock(FakeHttpClientResponse::class);
        $mockClientResponse->expects($this->once())
            ->method('getContent')
            ->willReturn('{"text":"Cool Stuff"}');

        $responseHandler = new ResponseHandler();

        $text = $responseHandler->handle($mockClientResponse);

        $this->assertSame("Cool Stuff", $text);
    }

    public function testThrowsExceptionWhenInvalidApiSecret(): void
    {
        $fakeResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $fakeResponseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(401);

        $responseHandler = new ResponseHandler();

        $this->expectException(InvalidApiSecretException::class);
        $this->expectExceptionMessage('Invalid API secret.');
        $this->expectExceptionCode(401);

        $responseHandler->handle($fakeResponseMock);
    }

    public function testThrowsExceptionWhenRateLimitIsReached(): void
    {
        $fakeResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $fakeResponseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(429);

        $responseHandler = new ResponseHandler();

        $this->expectException(RateLimitReachedException::class);
        $this->expectExceptionMessage('Rate limit reached for requests.');
        $this->expectExceptionCode(429);

        $responseHandler->handle($fakeResponseMock);
    }

    public function testThrowsExceptionWhenApiServerHasAnError(): void
    {
        $fakeResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $fakeResponseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(500);

        $responseHandler = new ResponseHandler();

        $this->expectException(ApiServerErrorException::class);
        $this->expectExceptionMessage('API server error.');
        $this->expectExceptionCode(500);

        $responseHandler->handle($fakeResponseMock);
    }

    public function testThrowsExceptionWhenApiServerIsOverloaded(): void
    {
        $fakeResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $fakeResponseMock->expects($this->once())
            ->method('getStatusCode')
            ->willReturn(503);

        $responseHandler = new ResponseHandler();

        $this->expectException(ApiServerOverloadedException::class);
        $this->expectExceptionMessage('API server is overloaded.');
        $this->expectExceptionCode(503);

        $responseHandler->handle($fakeResponseMock);
    }

    public function testMethodHandleThrowsExceptionWithInvalidResponseBody(): void
    {
        $mockClientResponse = $this->createMock(FakeHttpClientResponse::class);
        $mockClientResponse->expects($this->once())
            ->method('getContent')
            ->willReturn('"');

        $responseHandler = new ResponseHandler();

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage("Invalid response format.");

        $responseHandler->handle($mockClientResponse);
    }

    public function testMethodHandleThrowsExceptionResponseBodyDoesNotHaveTheTextParameter(): void
    {
        $mockClientResponse = $this->createMock(FakeHttpClientResponse::class);
        $mockClientResponse->expects($this->once())
            ->method('getContent')
            ->willReturn('{"success":true}');

        $responseHandler = new ResponseHandler();

        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage("The response does not have the text key.");

        $responseHandler->handle($mockClientResponse);
    }
}