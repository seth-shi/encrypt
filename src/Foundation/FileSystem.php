<?php

namespace DavidNineRoc\Encrypt\Foundation;

use Closure;
use DavidNineRoc\Encrypt\Exceptions\ReadFileException;

class FileSystem
{
    /**
     * @var $fileStream FileStream
     */
    protected $fileStream;

    /**
     * 初始化文件信息，得到需要加密文件的文件名、
     * 文件数据，大小等数据
     *
     * @param $filePath
     */
     protected function initFileInfo($filePath)
     {
         // 1. 获取文件名
         // 2. 直接获取文件数据，如果内存不足，建议之前调用 ini_set
         $fileName = basename($filePath);
         $fileData = file_get_contents($filePath);

         $this->fileStream->setName($fileName)
                          ->setData($fileData)
                          // 文件名字长度 四个字节长度存储
                          ->setNameSize(
                               str_pad(
                                   strlen($fileName),
                                   Encryption::FILE_NAME_SIZE_STORAGE_LENGTH,
                                   '0',
                                   STR_PAD_LEFT
                               )
                           )
                           ->setDataSize(
                               str_pad(
                                   strlen($fileData),
                                   Encryption::FILE_DATA_SIZE_STORAGE_LENGTH,
                                   '0',
                                   STR_PAD_LEFT
                               )
                           );
     }

    /**
     * 文件的操作使用闭包的操作，减少每次手动打开关闭文件流
     *
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
    protected function getOffsetPoint($filename)
    {
        return $this->readFileHandler($filename, function ($pf) {
            // 第 1 2 个字节是 BM
            fread($pf, 2);

            // 位图文件的大小转为十进制
            // 不带有符号的长模式[long]（通常是32位，按机器字节顺序）32 位正好四个字节
            fread($pf, 4);

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
    protected function getContentSize()
    {
        return $this->getHeadDataSize() + $this->getBodyDataSize();
    }

    /**
     * 获取头数据的大小，存储文件名字占用的大小 +
     * 存储文件内容所占的大小
     *
     * @return int
     */
    protected function getHeadDataSize()
    {
        return (
            Encryption::FILE_NAME_SIZE_STORAGE_LENGTH +
            Encryption::FILE_DATA_SIZE_STORAGE_LENGTH
        );
    }

    /**
     * 获取头数据的大小 + 文件名的长度
     *
     * @return int
     */
    protected function getHeadDataAndNameSize()
    {
        return $this->getHeadDataSize() + intval($this->fileStream->getNameSize());
    }

    /**
     * 获取数据区域的大小，文件名字所占的大小 +
     * 文件内容所占的大小
     *
     * @return int
     */
    protected function getBodyDataSize()
    {
        return (
            intval($this->fileStream->getNameSize()) +
            intval($this->fileStream->getDataSize())
        );
    }

    /**
     * 获取所有需要存储的内容
     *
     * @return string
     */
    protected function getContent()
    {
        return (
            $this->fileStream->getNameSize() .
            $this->fileStream->getDataSize() .
            $this->fileStream->getName() .
            $this->fileStream->getData()
        );
    }
 }
