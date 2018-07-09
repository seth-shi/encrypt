<?php

namespace DavidNineRoc\Encrypt\Foundation;

use Closure;
use DavidNineRoc\Encrypt\BMP;
use DavidNineRoc\Encrypt\Encryption;
use DavidNineRoc\Encrypt\Exceptions\ReadFileException;

trait FileSystem
{
    /**
     * 此处用于 IDE 识别
     *
     * @var $bmp BMP
     */
    protected $bmp;

    /**
     * 初始化文件信息
     *
     * @param $filePath
     */
     protected function initFileInfo($filePath)
     {
         $fileName = base64_encode(basename($filePath));
         $fileData = file_get_contents($filePath);

         // base64 加密后的文件名
         $this->bmp->setFileName($fileName)
                   ->setFileData($fileData)
                   // 文件名字长度 四个字节长度存储
                   ->setFileNameSize(
                       str_pad(
                           strlen($fileName),
                           Encryption::FILE_NAME_SIZE_STORAGE_LENGTH,
                           '0',
                           STR_PAD_LEFT
                       )
                   )
                   ->setFileDataSize(
                       str_pad(
                           strlen($fileData),
                           Encryption::FILE_DATA_SIZE_STORAGE_LENGTH,
                           '0',
                           STR_PAD_LEFT
                       )
                   );


     }

    /**
     * 文件的操作
     * @param         $filePath
     * @param Closure $closure
     * @param string  $mode
     * @return mixed
     */
     protected function readFileHandler($filePath, Closure $closure, $mode = 'rb')
     {
         $pf = fopen($filePath, $mode);

         $result = $closure($pf);

         fclose($pf);

         return $result;
     }

    /**
     * 得到位图阵列相对于文件头的偏移
     *
     * @param $filename
     * @return mixed
     */
    private function getOffsetPoint($filename)
    {
        return $this->readFileHandler($filename, function ($pf) {
            // 第 1 2 个字节是 BM
            fread($pf, 2);

            // 位图文件的大小转为十进制
            // 不带有符号的长模式[long]（通常是32位，按机器字节顺序）
            // 32 位正好四个字节
            fread($pf, 4);
            // $size = unpack('L', $size);

            // 第 7  8  9  10 字节是保留的 必须为 0
            fread($pf, 4);

            // 第 11 12 13 14 字节给出位图阵列相对于文件头的偏移
            $offsetData = fread($pf, 4);

            $offsetData = unpack('L', $offsetData);

            if (! isset($offsetData[1])) {
                throw new ReadFileException;
            }

            return $offsetData[1];
        });

    }

    /**
     * 获取存储的长度
     * 存储文件名长度的长度 + 存储文件数据长度的长度 + 文件名的长度 + 文件数据的长度
     *
     * @return int
     */
    protected function getMemoryContentSize()
    {
        return (
            Encryption::FILE_NAME_SIZE_STORAGE_LENGTH +
            Encryption::FILE_DATA_SIZE_STORAGE_LENGTH +
            intval($this->bmp->getFileNameSize()) +
            intval($this->bmp->getFileDataSize())
        );
    }

    /**
     * 获取存储的内容
     *
     * @return string
     */
    protected function getMemoryContent()
    {
        return (
            $this->bmp->getFileNameSize() .
            $this->bmp->getFileDataSize() .
            $this->bmp->getFileName() .
            $this->bmp->getFileData()
        );
    }
 }
