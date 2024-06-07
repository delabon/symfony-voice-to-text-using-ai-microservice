<?php

namespace App\Tests\Unit;

use App\Service\ResponseHandler;
use App\Tests\Fake\FakeHttpClientResponse;
use PHPUnit\Framework\TestCase;

class ResponseHandlerTest extends TestCase
{
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