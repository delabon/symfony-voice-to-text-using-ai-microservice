<?php

namespace App\Service;

use App\ValueObject\FileData;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use UnexpectedValueException;

readonly class AudioToFileService
{
    public function __construct(
        private HttpClientInterface $client,
        #[Autowire('%openai_secret%')]
        private string $openAiSecret
    )
    {
    }

    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     * @throws UnexpectedValueException
     * @throws FileException
     */
    public function convert(FileData $file): string
    {
        $this->validateFile($file);
        $response = $this->makeRequest($file);

        return $this->handleResponse($response);
    }

    private function validateFile(FileData $file): void
    {
        if (!in_array($file->getMime(), [
            'audio/mpeg',
            'audio/mp3',
            'audio/mp4',
            'audio/x-m4a',
            'audio/wav',
            'audio/x-wav',
            'audio/wave',
            'audio/webm',
        ])) {
            throw new InvalidArgumentException('The file passed is not an audio file or the format is not supported.');
        }
    }

    /**
     * @throws TransportExceptionInterface
     */
    private function makeRequest(FileData $file): ResponseInterface
    {
        return $this->client->request('POST', 'https://api.openai.com/v1/audio/transcriptions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->openAiSecret,
                'Content-Type' => 'multipart/form-data'
            ],
            'body' => [
                [
                    'name' => 'file',
                    'contents' => $file->getContent(),
                    'filename' => $file->getName(),
                ],
                [
                    'name' => 'model',
                    'contents' => 'whisper-1',
                ],
            ],
        ]);
    }

    /**
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    private function handleResponse(ResponseInterface $response): string
    {
        $json = json_decode($response->getContent(), true);

        if ($json === null) {
            throw new UnexpectedValueException('Invalid response format.');
        }

        if (!array_key_exists('text', $json)) {
            throw new UnexpectedValueException('The response does not have the text key.');
        }

        return $json['text'];
    }
}