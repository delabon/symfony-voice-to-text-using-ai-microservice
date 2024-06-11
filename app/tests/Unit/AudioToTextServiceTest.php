<?php

namespace App\Tests\Unit;

use App\Service\AudioToFileService;
use App\Service\RequestMaker;
use App\Service\ResponseHandler;
use App\Tests\Fake\FakeHttpClient;
use App\Tests\Fake\FakeHttpClientResponse;
use App\Tests\Fake\FakeHttpException;
use App\Tests\Trait\FileDuplicator;
use App\Tests\Trait\RequestHeaderAndFormDataCreator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use UnexpectedValueException;

/**
 * Notes:
 * - No need to create a test for non-existent file because UploadedFile throws an exception in this case
 */
class AudioToTextServiceTest extends TestCase
{
    use FileDuplicator;
    use RequestHeaderAndFormDataCreator;

    private ?string $filepath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filepath = self::duplicate(__DIR__ . '/../TestFiles/test-1.mp3');
    }

    protected function tearDown(): void
    {
        @unlink($this->filepath);
        $this->filepath = null;

        parent::tearDown();
    }

    public function testConvertMethodConvertsAudioFileToTextSuccessfully(): void
    {
        list($headers, $formData) = $this->createRequestHeadersAndFormData(filepath: $this->filepath);

        $clientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $clientResponseMock->expects($this->exactly(1))
            ->method('getContent')
            ->with(false)
            ->willReturn(json_encode([
                'text' => "It's a nice day today, isn't it?"
            ]));

        $clientMock = $this->getClientMock($headers, $formData, $clientResponseMock);

        $service = new AudioToFileService(
            new ResponseHandler(),
            new RequestMaker($clientMock)
        );
        $text = $service->convert($formData, $headers);

        $this->assertSame("It's a nice day today, isn't it?", $text);
    }

    public function testConvertMethodsThrowsExceptionWhenInvalidResponseIsReturned(): void
    {
        list($headers, $formData) = $this->createRequestHeadersAndFormData(filepath: $this->filepath);

        $clientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $clientResponseMock->expects($this->exactly(1))
            ->method('getContent')
            ->with(false)
            ->willThrowException(new FakeHttpException());

        $clientMock = $this->getClientMock($headers, $formData, $clientResponseMock);

        $service = new AudioToFileService(
            new ResponseHandler(),
            new RequestMaker($clientMock)
        );

        $this->expectException(FakeHttpException::class);

        $service->convert($formData, $headers);
    }

    public function testConvertMethodsThrowsExceptionWhenInvalidResponseFormatIsReturned(): void
    {
        list($headers, $formData) = $this->createRequestHeadersAndFormData(filepath: $this->filepath);

        $clientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $clientResponseMock->expects($this->exactly(1))
            ->method('getContent')
            ->with(false)
            ->willReturn('"');

        $clientMock = $this->getClientMock($headers, $formData, $clientResponseMock);

        $service = new AudioToFileService(
            new ResponseHandler(),
            new RequestMaker($clientMock)
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid response format.');

        $service->convert($formData, $headers);
    }

    public function testConvertMethodsThrowsExceptionWhenResponseDoesNotHaveTheTextKey(): void
    {
        list($headers, $formData) = $this->createRequestHeadersAndFormData(filepath: $this->filepath);

        $clientResponseMock = $this->createMock(FakeHttpClientResponse::class);
        $clientResponseMock->expects($this->exactly(1))
            ->method('getContent')
            ->with(false)
            ->willReturn('[]');

        $clientMock = $this->getClientMock($headers, $formData, $clientResponseMock);

        $service = new AudioToFileService(
            new ResponseHandler(),
            new RequestMaker($clientMock)
        );

        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('The response does not have the text key.');

        $service->convert($formData, $headers);
    }

    /**
     * @param Headers $headers
     * @param FormDataPart $formDataPart
     * @param $clientResponseMock
     * @return FakeHttpClient|(FakeHttpClient&object&MockObject)|(FakeHttpClient&MockObject)|(object&MockObject)|MockObject
     */
    protected function getClientMock(
        Headers $headers,
        FormDataPart $formDataPart,
        $clientResponseMock
    ) {
        $clientMock = $this->createMock(FakeHttpClient::class);
        $clientMock->expects($this->exactly(1))
            ->method('request')
            ->with(
                'POST',
                'https://api.openai.com/v1/audio/transcriptions',
                [
                    'headers' => $headers->toArray(),
                    'body' => $formDataPart->bodyToIterable(),
                ]
            )->willReturn($clientResponseMock);

        return $clientMock;
    }
}
