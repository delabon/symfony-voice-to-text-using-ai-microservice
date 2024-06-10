<?php

namespace App\Tests\Integration;

use App\Exception\InvalidApiSecretException;
use App\Service\AudioFileValidator;
use App\Service\AudioToFileService;
use App\Service\RequestMaker;
use App\Service\ResponseHandler;
use App\Tests\Trait\FileDuplicator;
use App\Tests\Trait\RequestHeaderAndFormDataCreator;
use App\ValueObject\FileData;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class AudioToTextServiceTest extends WebTestCase
{
    use FileDuplicator;
    use RequestHeaderAndFormDataCreator;

    public function testConvertMethodConvertsAudioFileToTextSuccessfully(): void
    {
        static::bootKernel();
        $client = HttpClient::create();
        $secretKey = $this->getContainer()->getParameter('openai_secret');
        $filepath = self::duplicate(__DIR__ . '/../TestFiles/test-1.mp3');

        $fileData = new FileData(
            $filepath,
            basename($filepath),
            'mp3',
            'audio/mpeg',
            file_get_contents($filepath)
        );
        $filePart = DataPart::fromPath($filepath);;
        $formData = new FormDataPart([
            'file' => $filePart,
            'model' => 'whisper-1',
        ]);
        $headers = $formData->getPreparedHeaders();
        $headers->addParameterizedHeader('Authorization', 'Bearer ' . $secretKey);

        $service = new AudioToFileService(
            new AudioFileValidator(),
            new ResponseHandler(),
            new RequestMaker($client)
        );

        $text = $service->convert($fileData, $formData, $headers);

        $this->assertSame("It's a nice day today, isn't it?", $text);
    }

    /**
     * This test just to make sure our AudioToFileService interacts with AudioFileValidator
     */
    public function testThrowsExceptionWhenNonExistentFileIsUsed(): void
    {
        static::bootKernel();
        $client = HttpClient::create();
        $secretKey = $this->getContainer()->getParameter('openai_secret');
        $filepath = __DIR__ . '/../TestFiles/not-found-1.mp3';

        $fileData = new FileData(
            $filepath,
            basename($filepath),
            'mp3',
            'audio/mpeg',
            'Fake content'
        );
        $filePart = DataPart::fromPath($filepath);;
        $formData = new FormDataPart([
            'file' => $filePart,
            'model' => 'whisper-1',
        ]);
        $headers = $formData->getPreparedHeaders();
        $headers->addParameterizedHeader('Authorization', 'Bearer ' . $secretKey);

        $service = new AudioToFileService(
            new AudioFileValidator(),
            new ResponseHandler(),
            new RequestMaker($client)
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The audio file does not exist.');

        $service->convert($fileData, $formData, $headers);
    }

    /**
     * This test just to make sure our AudioToFileService interacts with ResponseHandler
     */
    public function testThrowsExceptionWhenInvalidSecret(): void
    {
        $client = HttpClient::create();
        $secretKey = 'bad secret';
        $filepath = __DIR__ . '/../TestFiles/test-1.mp3';

        $fileData = new FileData(
            $filepath,
            basename($filepath),
            'mp3',
            'audio/mpeg',
            'Fake content'
        );
        $filePart = DataPart::fromPath($filepath);;
        $formData = new FormDataPart([
            'file' => $filePart,
            'model' => 'whisper-1',
        ]);
        $headers = $formData->getPreparedHeaders();
        $headers->addParameterizedHeader('Authorization', 'Bearer ' . $secretKey);

        $service = new AudioToFileService(
            new AudioFileValidator(),
            new ResponseHandler(),
            new RequestMaker($client)
        );

        $this->expectException(InvalidApiSecretException::class);
        $this->expectExceptionMessage('Invalid API secret.');

        $service->convert($fileData, $formData, $headers);
    }
}
