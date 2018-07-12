<?php

namespace DavidNineRoc\Encrypt\Tests;


use DavidNineRoc\Encrypt\Foundation\FileStream;
use PHPUnit\Framework\TestCase;

class FileStreamTest extends TestCase
{
    const BMP_PATH = __DIR__ . '/../data/gps.txt';

    public function testAttribute()
    {
        $fileStream = new FileStream(self::BMP_PATH, true);

        $this->assertStringEqualsFile(self::BMP_PATH, $fileStream->getData());
    }
}
