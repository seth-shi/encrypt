<?php

namespace DavidNineRoc\Encrypt;


use DavidNineRoc\Encrypt\Exceptions\FileNonExistsException;
use DavidNineRoc\Encrypt\Exceptions\FileNotBMPException;
use DavidNineRoc\Encrypt\Exceptions\ReadFileException;
use DavidNineRoc\Encrypt\Foundation\FileSystem;


class Handler
{
    use FileSystem;

    /**
     * @var string $fileName
     *
     * 文件名字
     */
    private $fileName;

    /**
     * @var int $fileNameLength
     *
     * 文件名字长度
     */
    private $fileNameSize;

    /**
     * 文件数据
     *
     * @var string $fileData
     */
    private $fileData;

    /**
     * @var int $fileDataLength
     *
     * 文件数据长度
     */
    private $fileDataSize;

    /**
     * @var string $filePath
     *
     * 图片文件的路径
     */
    private $filePath;


    /**
     * 存储需要操作的文件路径
     *
     * @param $pictureFile
     * @throws FileNonExistsException
     * @throws FileNotBMPException
     */
    public function __construct($pictureFile)
    {
        if (! is_file($pictureFile)) {
            throw new FileNonExistsException;
        }

        $type = mime_content_type($pictureFile);
        if (false === strpos($type, 'bmp')) {
            throw new FileNotBMPException;
        }

        $this->filePath = $pictureFile;
    }

    /**
     * 加密文件
     *
     * @param      $encryptFile
     * @param null $newFileName
     * @return bool|mixed
     */
    public function encrypt($encryptFile, $newFileName = null)
    {
        $this->initFileInfo($encryptFile);
        // 1. 获取数据区所在图片的位置
        // 2. 获取存储数据的总长度
        // 3. 获取存储的总数据
        $offset = $this->getOffsetPoint($this->filePath);
        $length = $this->getMemoryContentSize();
        $content = $this->getMemoryContent();

        // 核心处理文件
        $data = $this->readFileHandler($this->filePath, function ($pf) use ($offset, $length, $content) {
            // 一次性读取完文件头信息，这些数据无法操作
            $data = fread($pf, $offset);

            // 把 Alpha 换成加密内容
            for ($i = 0; !feof($pf); ++$i) {
                // 3 个字节代表一个像素 最后一个字节是 Alpha 通道-透明度
                $red   = fread($pf, 1);
                $green = fread($pf, 1);
                $blue  = fread($pf, 1);
                $alpha = fread($pf, 1);

                // 如果当前还在存储区的内容，就把内容存进起来
                if ($i < $length) {
                    $alpha = $content{$i};
                }

                $data .= $red . $green . $blue . $alpha;
            }


            if ($i < $length) {
                throw new ReadFileException('图片太小，不足以加密内容');
            }

            return $data;
        });

        if (is_null($newFileName)) {
            return $data;
        }


        return (bool) file_put_contents($newFileName, $data);
    }

}
