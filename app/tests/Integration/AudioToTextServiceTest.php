<?php

namespace App\Tests\Integration;

use App\Service\AudioFileValidator;
use App\Service\AudioToFileService;
use App\Service\RequestMaker;
use App\Service\ResponseHandler;
use App\Tests\Trait\FileDuplicator;
use App\Tests\Trait\RequestHeaderAndFormDataCreator;
use App\ValueObject\FileData;
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
}
