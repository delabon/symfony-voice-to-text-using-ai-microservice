<?php

namespace App\Service;

use App\ValueObject\FileData;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class RequestMaker
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
     */
    public function make(FileData $fileData): ResponseInterface
    {
        return $this->client->request('POST', 'https://api.openai.com/v1/audio/transcriptions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->openAiSecret,
                'Content-Type' => 'multipart/form-data'
            ],
            'body' => [
                'file' => [
                    'content' => $fileData->getContent(),
                    'filename' => $fileData->getName(),
                ],
                'model' => 'whisper-1',
            ],
        ]);
    }
}