<?php

namespace App\Tests\Feature;

use App\Tests\Trait\FileDuplicator;
use App\Tests\Trait\SessionHelper;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class AudioToTextTest extends WebTestCase
{
    use FileDuplicator;
    use SessionHelper;

    private KernelBrowser $client;
    private string $csrfToken;

    public function testConvertsAudioToTextSuccessfully(): void
    {
        $client = static::createClient();
        $filepath = self::duplicate(__DIR__ . '/../TestFiles/test-1.mp3');
        $uploadedFile = new UploadedFile($filepath, basename($filepath), mime_content_type($filepath), null, true);

        $client->request('POST', '/audio-to-text',
            parameters: [
                '_token' => $this->generateCsrfToken($client, 'voice_to_text_csrf')
            ],
            files: [
                'audioFile' => $uploadedFile
            ],
        );

        $this->assertResponseIsSuccessful();
        $this->assertSame([
            'success' => true,
            'text' => "It's a nice day today, isn't it?"
        ], json_decode($client->getResponse()->getContent(), true));
    }

    public function testConvertsAnotherAudioToTextSuccessfully(): void
    {
        $client = static::createClient();
        $filepath = self::duplicate(__DIR__ . '/../TestFiles/test-2.mp3');
        $uploadedFile = new UploadedFile($filepath, basename($filepath), mime_content_type($filepath), null, true);

        $client->request('POST', '/audio-to-text',
            parameters: [
                '_token' => $this->generateCsrfToken($client, 'voice_to_text_csrf')
            ],
            files: [
                'audioFile' => $uploadedFile
            ],
        );

        $this->assertResponseIsSuccessful();
        $this->assertSame([
            'success' => true,
            'text' => 'Cool stuff, keep going.'
        ], json_decode($client->getResponse()->getContent(), true));
    }

    public function testReturnsForbiddenResponseWhenInvalidCsrf(): void
    {
        $client = static::createClient();
        $filepath = self::duplicate(__DIR__ . '/../TestFiles/test-1.mp3');
        $uploadedFile = new UploadedFile($filepath, basename($filepath), 'audio/mpeg', null, true);

        $client->request('POST', '/audio-to-text',
            parameters: [
                '_token' => 'invalid token'
            ],
            files: [
                'audioFile' => $uploadedFile
            ],
        );

        $this->assertSame(Response::HTTP_FORBIDDEN, $client->getResponse()->getStatusCode());
        $this->assertSame([
            'success' => false,
            'error' => 'Invalid CSRF token.'
        ], json_decode($client->getResponse()->getContent(), true));
    }

    public function testReturnsBadRequestResponseWhenNoFile(): void
    {
        $client = static::createClient();
        $client->request('POST', '/audio-to-text',
            parameters: [
                '_token' => $this->generateCsrfToken($client, 'voice_to_text_csrf')
            ],
        );

        $this->assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertSame([
            'success' => false,
            'error' => 'No file was uploaded'
        ], json_decode($client->getResponse()->getContent(), true));
    }

    public function testReturnsBadRequestResponseWhenNonAudioFile(): void
    {
        $client = static::createClient();
        $filepath = self::duplicate(__DIR__ . '/../TestFiles/test-3.pdf');
        $uploadedFile = new UploadedFile($filepath, basename($filepath), mime_content_type($filepath), null, true);

        $client->request('POST', '/audio-to-text',
            parameters: [
                '_token' => $this->generateCsrfToken($client, 'voice_to_text_csrf')
            ],
            files: [
                'audioFile' => $uploadedFile
            ],
        );

        $this->assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertSame([
            'success' => false,
            'errors' => [
                'Please upload a valid audio file (flac, mp3, mp4, mpeg, mpga, m4a, ogg, wav, or webm).'
            ]
        ], json_decode($client->getResponse()->getContent(), true));
    }
}
