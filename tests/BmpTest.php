<?php

namespace DavidNineRoc\Encrypt\Tests;

use DavidNineRoc\Encrypt\Foundation\FileStream;
use DavidNineRoc\Encrypt\Handler;
use PHPUnit\Framework\TestCase;

class BmpTest extends TestCase
{
    const DATA_PATH = __DIR__ . '/../data';
    const BMP_PATH = self::DATA_PATH . '/gps.bmp';
    const TXT_PATH = self::DATA_PATH . '/gps.txt';
    const CACHE_BMP = self::DATA_PATH . '/gps.bmp.bak';

    /**
     * @return Handler
     * @throws \DavidNineRoc\Encrypt\Exceptions\FileNonExistsException
     * @throws \DavidNineRoc\Encrypt\Exceptions\FileNotBMPException
     */
    public function testHandlerInstance()
    {
        $handler = new Handler(self::BMP_PATH);

        $this->assertInstanceOf(Handler::class, $handler);

        return $handler;
    }


    public function testDataPathIsWritable()
    {
        $this->assertDirectoryExists(self::DATA_PATH);
        $this->assertDirectoryIsWritable(self::DATA_PATH);
    }

    /**
     * @depends testHandlerInstance
     * @param Handler $handler
     * @return FileStream
     */
    public function testEncrypt(Handler $handler)
    {
        $stream = $handler->encrypt(self::TXT_PATH);

        $this->assertInstanceOf(FileStream::class, $stream);

        file_put_contents(self::CACHE_BMP, $stream->getData());

        $this->assertFileExists(self::CACHE_BMP);

        return $stream;
    }


    /**
     * @throws \DavidNineRoc\Encrypt\Exceptions\FileNonExistsException
     * @throws \DavidNineRoc\Encrypt\Exceptions\FileNotBMPException
     */
    public function testDecrypt()
    {
        $stream = (new Handler(self::CACHE_BMP))->decrypt();

        $this->assertStringEqualsFile(self::TXT_PATH, $stream->getData());
    }

    public static function tearDownAfterClass()
    {
        if (is_file(self::CACHE_BMP)) {
            unlink(self::CACHE_BMP);
        }
    }
}
