<?php

namespace App\Tests\Unit;

use App\Service\AudioFileValidator;
use App\ValueObject\FileData;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class AudioFileValidatorTest extends TestCase
{
    public function testValidatesAudioFileCorrectly(): void
    {
        $fileData = new FileData(
            __DIR__ . '/../TestFiles/test-1.mp3',
            'test-1.mp3',
            'mp3',
            'audio/mpeg',
            'Fake audio content'
        );
        $validator = new AudioFileValidator();
        $validator->validate($fileData);

        $this->expectNotToPerformAssertions();
    }

    public function testThrowsExceptionWhenFileDoesNotExist(): void
    {
        $fileData = new FileData(
            '/tmp/fileDoesNotExist.mp3',
            'fileDoesNotExist.mp3',
            'mp3',
            'audio/mpeg',
            'Fake audio content'
        );
        $validator = new AudioFileValidator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The audio file does not exist.');

        $validator->validate($fileData);
    }

    public function testThrowsExceptionWhenInvalidFileName(): void
    {
        $fileData = new FileData(
            __DIR__ . '/../TestFiles/test-1.mp3',
            '',
            'mp3',
            'audio/mpeg',
            'Fake audio content'
        );
        $validator = new AudioFileValidator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The name of the audio file is invalid.');

        $validator->validate($fileData);
    }

    public function testThrowsExceptionWhenEmptyFileExtension(): void
    {
        $fileData = new FileData(
            __DIR__ . '/../TestFiles/test-1.mp3',
            'test-1.mp3',
            '',
            'audio/mpeg',
            'Fake audio content'
        );
        $validator = new AudioFileValidator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The extension of the audio file is invalid.');

        $validator->validate($fileData);
    }

    public function testThrowsExceptionWhenNonSupportedFileExtension(): void
    {
        $fileData = new FileData(
            __DIR__ . '/../TestFiles/test-1.mp3',
            'test-1.mp3',
            'avi',
            'audio/mpeg',
            'Fake audio content'
        );
        $validator = new AudioFileValidator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The extension of the audio file is invalid.');

        $validator->validate($fileData);
    }

    public function testThrowsExceptionWhenInvalidFileMimeType(): void
    {
        $fileData = new FileData(
            __DIR__ . '/../TestFiles/test-1.mp3',
            'test-1.mp3',
            'mp3',
            'application/pdf',
            'Fake pdf content'
        );
        $validator = new AudioFileValidator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The file passed is not an audio file or the format is not supported.');

        $validator->validate($fileData);
    }

    public function testThrowsExceptionWhenInvalidFileContent(): void
    {
        $fileData = new FileData(
            __DIR__ . '/../TestFiles/test-1.mp3',
            'test-1.mp3',
            'mp3',
            'audio/mpeg',
            ''
        );
        $validator = new AudioFileValidator();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The content of the audio file is invalid.');

        $validator->validate($fileData);
    }
}
