<?php

namespace App\Tests\Trait;

use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

trait RequestHeaderAndFormDataCreator
{
    /**
     * @param string|null $fakeSecret
     * @param string|null $filepath
     * @return array
     */
    protected function createRequestHeadersAndFormData(?string $fakeSecret = null, ?string $filepath = null): array
    {
        $fakeSecret = $fakeSecret ?: 'Bla Blah Blahh';
        $filepath = $filepath ?: '/tmp/fake-audio.mp3';
        $filePart = DataPart::fromPath($filepath);

        $headers = new Headers();
        $headers->addParameterizedHeader('Content-Type', 'multipart/form-data boudary=---boundary---');
        $headers->addParameterizedHeader('Authorization', 'Bearer ' . $fakeSecret);

        $formData = new FormDataPart([
            'file' => $filePart,
            'model' => 'whisper-1',
        ]);

        return [$headers, $formData];
    }
}