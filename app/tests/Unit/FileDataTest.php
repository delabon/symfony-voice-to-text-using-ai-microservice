<?php

namespace App\Tests\Unit;

use App\ValueObject\FileData;
use PHPUnit\Framework\TestCase;

class FileDataTest extends TestCase
{
    public function testReturnsCorrectInstanceAndData(): void
    {
        $fileData = new FileData(
            '/tmp/myfile.mp4',
            'myfile.mp4',
            'mp4',
            'video/mp4',
            'Fake video content'
        );

        $this->assertInstanceOf(FileData::class, $fileData);
        $this->assertSame('/tmp/myfile.mp4', $fileData->getPath());
        $this->assertSame('myfile.mp4', $fileData->getName());
        $this->assertSame('mp4', $fileData->getExt());
        $this->assertSame('video/mp4', $fileData->getMime());
        $this->assertSame('Fake video content', $fileData->getContent());
    }
}
