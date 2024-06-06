<?php

namespace App\Service;

use App\ValueObject\FileData;
use InvalidArgumentException;

class AudioFileValidator
{
    private array $extensions = [
        'mp3',
        'mp4',
        'm4a',
        'wav',
        'webm',
    ];

    private array $mimes = [
        'audio/mpeg',
        'audio/mp3',
        'audio/mp4',
        'audio/x-m4a',
        'audio/wav',
        'audio/x-wav',
        'audio/wave',
        'audio/webm',
    ];

    public function validate(FileData $fileData): void
    {
        if (!file_exists($fileData->getPath())) {
            throw new InvalidArgumentException('The audio file does not exist.');
        }

        if (empty($fileData->getName())) {
            throw new InvalidArgumentException('The name of the audio file is invalid.');
        }

        if (!in_array(strtolower($fileData->getExt()), $this->extensions)) {
            throw new InvalidArgumentException('The extension of the audio file is invalid.');
        }

        if (!in_array($fileData->getMime(), $this->mimes)) {
            throw new InvalidArgumentException('The file passed is not an audio file or the format is not supported.');
        }

        if (empty($fileData->getContent())) {
            throw new InvalidArgumentException('The content of the audio file is invalid.');
        }
    }
}