<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use UnexpectedValueException;

class ResponseHandler
{
    /**
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function handle(ResponseInterface $response): string
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