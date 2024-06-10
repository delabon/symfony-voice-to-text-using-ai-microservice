<?php

namespace App\Tests\Trait;

use App\ValueObject\FileData;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

trait RequestHeaderAndFormDataCreator
{
    /**
     * @param string|null $fakeSecret
     * @param FileData|null $fileData
     * @return array
     */
    protected function createRequestHeadersAndFormData(?string $fakeSecret = null, ?FileData $fileData = null): array
    {
        $fakeSecret = $fakeSecret ?: 'Bla Blah Blahh';
        $fileData = $fileData ?: new FileData(
            '/tmp/fake-audio.mp3',
            'fake-audio.mp3',
            'mp3',
            'audio/mp3',
            'Fake content'
        );
        $filePart = DataPart::fromPath($fileData->getPath());

        $headers = new Headers();
        $headers->addParameterizedHeader('Content-Type', 'multipart/form-data boudary=---boundary---');
        $headers->addParameterizedHeader('Authorization', 'Bearer ' . $fakeSecret);

        $formData = new FormDataPart([
            'file' => $filePart,
            'model' => 'whisper-1',
        ]);

        return [$headers, $formData, $fileData];
    }
}