<?php

namespace App\ValueObject;

readonly class FileData
{
    public function __construct(
        public string $path,
        public string $name,
        public string $ext,
        public string $mime,
        public string $content
    )
    {
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getExt(): string
    {
        return $this->ext;
    }

    public function getMime(): string
    {
        return $this->mime;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}