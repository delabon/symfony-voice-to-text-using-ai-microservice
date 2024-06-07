<?php

namespace App\Tests\Unit;

use App\Service\AudioFileValidator;
use App\Service\AudioToFileService;
use App\Service\ResponseHandler;
use App\Tests\Fake\FakeHttpClient;
use App\Tests\Fake\FakeHttpClientResponse;
use App\Tests\Fake\FakeHttpException;
use App\Tests\Trait\FileDuplicator;
use App\ValueObject\FileData;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

/**
 * Notes:
 * - No need to create a test for non-existent file because UploadedFile throws an exception in this case
 */
class AudioToTextServiceTest extends TestCase
{
    use FileDuplicator;

    public function testConvertMethodConvertsAudioFileToTextSuccessfully(): void
    {
        list($fakeSecret, $fileData) = $this->getFakeData();

        $clientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $clientResponseMock->expects($this->exactly(1))
            ->method('getContent')
            ->with(true)
            ->willReturn(json_encode([
                'text' => "It's a nice day today, isn't it?"
            ]));

        $clientMock = $this->getClientMock($fakeSecret, $fileData, $clientResponseMock);

        $service = new AudioToFileService(
            $clientMock,
            new AudioFileValidator(),
            new ResponseHandler(),
            $fakeSecret
        );
        $text = $service->convert($fileData);

        $this->assertSame("It's a nice day today, isn't it?", $text);
    }

    public function testConvertMethodsThrowsExceptionWhenInvalidResponseIsReturned(): void
    {
        list($fakeSecret, $fileData) = $this->getFakeData();

        $clientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $clientResponseMock->expects($this->exactly(1))
            ->method('getContent')
            ->with(true)
            ->willThrowException(new FakeHttpException());

        $clientMock = $this->getClientMock($fakeSecret, $fileData, $clientResponseMock);

        $service = new AudioToFileService(
            $clientMock,
            new AudioFileValidator(),
            new ResponseHandler(),
            $fakeSecret
        );

        $this->expectException(FakeHttpException::class);

        $service->convert($fileData);
    }

    public function testConvertMethodsThrowsExceptionWhenInvalidResponseFormatIsReturned(): void
    {
        list($fakeSecret, $fileData) = $this->getFakeData();

        $clientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $clientResponseMock->expects($this->exactly(1))
            ->method('getContent')
            ->with(true)
            ->willReturn('"');

        $clientMock = $this->getClientMock($fakeSecret, $fileData, $clientResponseMock);

        $service = new AudioToFileService(
            $clientMock,
            new AudioFileValidator(),
            new ResponseHandler(),
            $fakeSecret
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid response format.');

        $service->convert($fileData);
    }

    public function testConvertMethodsThrowsExceptionWhenResponseDoesNotHaveTheTextKey(): void
    {
        list($fakeSecret, $fileData) = $this->getFakeData();

        $clientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $clientResponseMock->expects($this->exactly(1))
            ->method('getContent')
            ->with(true)
            ->willReturn('[]');

        $clientMock = $this->getClientMock($fakeSecret, $fileData, $clientResponseMock);

        $service = new AudioToFileService(
            $clientMock,
            new AudioFileValidator(),
            new ResponseHandler(),
            $fakeSecret
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The response does not have the text key.');

        $service->convert($fileData);
    }

    /**
     * @return array
     */
    protected function getFakeData(): array
    {
        $fakeSecret = 'MyFakeSecret';
        $originalFilepath = __DIR__ . '/../TestFiles/test-1.mp3';
        $filepath = self::duplicate($originalFilepath);
        $fileData = new FileData(
            $filepath,
            basename($filepath),
            'mp3',
            'audio/mpeg',
            'Fake audio content'
        );

        return [$fakeSecret, $fileData];
    }

    /**
     * @param mixed $fakeSecret
     * @param mixed $fileData
     * @param $clientResponseMock
     */
    protected function getClientMock(
        mixed $fakeSecret,
        mixed $fileData,
        $clientResponseMock
    ) {
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
                        'file' => [
                            'content' => $fileData->getContent(),
                            'filename' => $fileData->getName(),
                        ],
                        'model' => 'whisper-1',
                    ],
                ]
            )->willReturn($clientResponseMock);
        return $clientMock;
    }
}
