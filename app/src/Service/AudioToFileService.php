<?php

namespace App\Service;

use App\ValueObject\FileData;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use UnexpectedValueException;

readonly class AudioToFileService
{
    public function __construct(
        private AudioFileValidator $validator,
        private ResponseHandler $responseHandler,
        private RequestMaker $requestMaker
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
        $response = $this->requestMaker->make($file);

        return $this->responseHandler->handle($response);
    }
}