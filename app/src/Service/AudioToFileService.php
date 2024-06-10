<?php

namespace App\Service;

use App\ValueObject\FileData;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractMultipartPart;
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
    public function convert(FileData $file, AbstractMultipartPart $formDataPart, Headers $headers): string
    {
        $this->validator->validate($file);
        $response = $this->requestMaker->make($formDataPart, $headers);

        return $this->responseHandler->handle($response);
    }
}