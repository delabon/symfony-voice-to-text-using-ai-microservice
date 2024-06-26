<?php

namespace App\Service;

use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractMultipartPart;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

readonly class RequestMaker
{
    public function __construct(private HttpClientInterface $client)
    {
    }

    /**
     * @param AbstractMultipartPart $formDataPart
     * @param Headers $headers
     * @return ResponseInterface
     * @throws TransportExceptionInterface
     */
    public function make(AbstractMultipartPart $formDataPart, Headers $headers): ResponseInterface
    {
        return $this->client->request('POST', 'https://api.openai.com/v1/audio/transcriptions', [
            'headers' => $headers->toArray(),
            'body' => $formDataPart->bodyToIterable(),
        ]);
    }
}