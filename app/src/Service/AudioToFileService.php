<?php

namespace App\Service;

use App\Exception\ApiServerErrorException;
use App\Exception\ApiServerOverloadedException;
use App\Exception\InvalidApiSecretException;
use App\Exception\RateLimitReachedException;
use Symfony\Component\Mime\Header\Headers;
use Symfony\Component\Mime\Part\AbstractMultipartPart;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

readonly class AudioToFileService
{
    public function __construct(
        private ResponseHandler $responseHandler,
        private RequestMaker $requestMaker
    )
    {
    }

    /**
     * @param AbstractMultipartPart $formDataPart
     * @param Headers $headers
     * @return string
     * @throws ClientExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws ApiServerErrorException
     * @throws ApiServerOverloadedException
     * @throws InvalidApiSecretException
     * @throws RateLimitReachedException
     */
    public function convert(AbstractMultipartPart $formDataPart, Headers $headers): string
    {
        $response = $this->requestMaker->make($formDataPart, $headers);

        return $this->responseHandler->handle($response);
    }
}