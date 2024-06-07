<?php

namespace App\Service;

use App\ValueObject\FileData;
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
        private AudioFileValidator $validator,
        private ResponseHandler $responseHandler,
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
        $this->validator->validate($file);
        $response = $this->makeRequest($file);

        return $this->responseHandler->handle($response);
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
                'file' => [
                    'content' => $file->getContent(),
                    'filename' => $file->getName(),
                ],
                'model' => 'whisper-1',
            ],
        ]);
    }
}