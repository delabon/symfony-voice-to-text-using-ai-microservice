<?php

namespace App\Tests\Unit;

use App\Service\RequestMaker;
use App\Tests\Fake\FakeHttpClient;
use App\Tests\Fake\FakeHttpClientResponse;
use App\Tests\Fake\FakeTransportException;
use App\ValueObject\FileData;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RequestMakerTest extends TestCase
{
    public function testMakesRequestCorrectly(): void
    {
        $fakeSecret = 'Bla Blah Blahh';
        $fileData = new FileData(
            '/tmp/fake-audio.mp3',
            'fake-audio.mp3',
            'mp3',
            'audio/mp3',
            'Fake content'
        );
        $fakeHttpClientMock = $this->createMock(FakeHttpClient::class);
        $fakeHttpClientMock->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.openai.com/v1/audio/transcriptions',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $fakeSecret,
                        'Content-Type' => 'multipart/form-data'
                    ],
                    'body' => [
                        'file' => [
                            'content' => $fileData->getContent(),
                            'filename' => $fileData->getName(),
                        ],
                        'model' => 'whisper-1',
                    ],
                ]
            )->willReturn(new FakeHttpClientResponse());

        $requestMaker = new RequestMaker($fakeHttpClientMock, $fakeSecret);

        $response = $requestMaker->make($fileData);

        $this->assertInstanceOf(ResponseInterface::class, $response);
    }

    /**
     * @return void
     * @throws TransportExceptionInterface
     */
    public function testThrowsExceptionSomethingGoesWrong(): void
    {
        $fakeSecret = 'Bla Blah Blahh';
        $fileData = new FileData(
            '/tmp/fake-audio.mp3',
            'fake-audio.mp3',
            'mp3',
            'audio/mp3',
            'Fake content'
        );

        $fakeHttpClientMock = $this->createMock(FakeHttpClient::class);
        $fakeHttpClientMock->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'https://api.openai.com/v1/audio/transcriptions',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $fakeSecret,
                        'Content-Type' => 'multipart/form-data'
                    ],
                    'body' => [
                        'file' => [
                            'content' => $fileData->getContent(),
                            'filename' => $fileData->getName(),
                        ],
                        'model' => 'whisper-1',
                    ],
                ]
            )->willThrowException(new FakeTransportException());

        $requestMaker = new RequestMaker($fakeHttpClientMock, $fakeSecret);

        $this->expectException(FakeTransportException::class);

        $requestMaker->make($fileData);
    }
}