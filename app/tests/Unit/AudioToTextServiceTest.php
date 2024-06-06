<?php

namespace App\Tests\Unit;

use App\Service\AudioToFileService;
use App\Tests\Fake\FakeHttpClient;
use App\Tests\Fake\FakeHttpClientResponse;
use App\Tests\Fake\FakeHttpException;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use UnexpectedValueException;

/**
 * Notes:
 * - No need to create a test for non-existent file because UploadedFile throws exception in this case
 */
class AudioToTextServiceTest extends TestCase
{
    public function testConvertMethodConvertsAudioFileToTextSuccessfully(): void
    {
        $file = new UploadedFile(__DIR__ . '/../TestFiles/test-1.mp3', 'test-1.mp3', 'audio/mpeg', null, true);
        $fakeSecret = 'MyFakeSecret';

        $clientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $clientResponseMock->expects($this->exactly(1))
            ->method('getContent')
            ->with(true)
            ->willReturn(json_encode([
                'text' => "It's a nice day today, isn't it?"
            ]));

        $clientMock = $this->createMock(FakeHttpClient::class);
        $clientMock->expects($this->exactly(1))
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
                        [
                            'name' => 'file',
                            'contents' => $file->getContent(),
                            'filename' => $file->getClientOriginalName(),
                        ],
                        [
                            'name' => 'model',
                            'contents' => 'whisper-1',
                        ],
                    ],
                ]
            )->willReturn($clientResponseMock);

        $service = new AudioToFileService(
            $clientMock,
            $fakeSecret
        );
        $text = $service->convert($file);

        $this->assertSame("It's a nice day today, isn't it?", $text);
    }

    public function testConvertMethodsThrowsExceptionWhenNonAudioFileIsPassed(): void
    {
        $file = new UploadedFile(__DIR__ . '/../TestFiles/test-3.pdf', 'test-3.pdf', 'application/pdf', null, true);
        $fakeSecret = 'MyFakeSecret';

        $clientMock = $this->createStub(FakeHttpClient::class);

        $service = new AudioToFileService(
            $clientMock,
            $fakeSecret
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The file passed is not an audio file or the format is not supported.');

        $service->convert($file);
    }

    public function testConvertMethodsThrowsExceptionWhenInvalidResponseIsReturned(): void
    {
        $file = new UploadedFile(__DIR__ . '/../TestFiles/test-1.mp3', 'test-1.mp3', 'audio/mpeg', null, true);
        $fakeSecret = 'MyFakeSecret';

        $clientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $clientResponseMock->expects($this->exactly(1))
            ->method('getContent')
            ->with(true)
            ->willThrowException(new FakeHttpException());

        $clientMock = $this->createMock(FakeHttpClient::class);
        $clientMock->expects($this->exactly(1))
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
                        [
                            'name' => 'file',
                            'contents' => $file->getContent(),
                            'filename' => $file->getClientOriginalName(),
                        ],
                        [
                            'name' => 'model',
                            'contents' => 'whisper-1',
                        ],
                    ],
                ]
            )->willReturn($clientResponseMock);

        $service = new AudioToFileService(
            $clientMock,
            $fakeSecret
        );

        $this->expectException(FakeHttpException::class);

        $service->convert($file);
    }

    public function testConvertMethodsThrowsExceptionWhenInvalidResponseFormatIsReturned(): void
    {
        $file = new UploadedFile(__DIR__ . '/../TestFiles/test-1.mp3', 'test-1.mp3', 'audio/mpeg', null, true);
        $fakeSecret = 'MyFakeSecret';

        $clientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $clientResponseMock->expects($this->exactly(1))
            ->method('getContent')
            ->with(true)
            ->willReturn('"');

        $clientMock = $this->createMock(FakeHttpClient::class);
        $clientMock->expects($this->exactly(1))
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
                        [
                            'name' => 'file',
                            'contents' => $file->getContent(),
                            'filename' => $file->getClientOriginalName(),
                        ],
                        [
                            'name' => 'model',
                            'contents' => 'whisper-1',
                        ],
                    ],
                ]
            )->willReturn($clientResponseMock);

        $service = new AudioToFileService(
            $clientMock,
            $fakeSecret
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid response format.');

        $service->convert($file);
    }

    public function testConvertMethodsThrowsExceptionWhenResponseDoesNotHaveTheTextKey(): void
    {
        $file = new UploadedFile(__DIR__ . '/../TestFiles/test-1.mp3', 'test-1.mp3', 'audio/mpeg', null, true);
        $fakeSecret = 'MyFakeSecret';

        $clientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $clientResponseMock->expects($this->exactly(1))
            ->method('getContent')
            ->with(true)
            ->willReturn('[]');

        $clientMock = $this->createMock(FakeHttpClient::class);
        $clientMock->expects($this->exactly(1))
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
                        [
                            'name' => 'file',
                            'contents' => $file->getContent(),
                            'filename' => $file->getClientOriginalName(),
                        ],
                        [
                            'name' => 'model',
                            'contents' => 'whisper-1',
                        ],
                    ],
                ]
            )->willReturn($clientResponseMock);

        $service = new AudioToFileService(
            $clientMock,
            $fakeSecret
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The response does not have the text key.');

        $service->convert($file);
    }
}
