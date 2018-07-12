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

}
