<?php

namespace DavidNineRoc\Encrypt;


use DavidNineRoc\Encrypt\Exceptions\DirException;
use DavidNineRoc\Encrypt\Exceptions\FileNonExistsException;
use DavidNineRoc\Encrypt\Exceptions\FileNotBMPException;
use DavidNineRoc\Encrypt\Exceptions\ReadFileException;
use DavidNineRoc\Encrypt\Foundation\FileSystem;


class Handler
{
    use FileSystem;

    /**
     * @var $bmp BMP
     */
    protected $bmp;

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
            throw new FileNonExistsException("[{$pictureFile}]不是一个有效的文件");
        }

        $type = mime_content_type($pictureFile);
        if (false === strpos($type, 'bmp')) {
            throw new FileNotBMPException("[{$pictureFile}]不是正确的图片类型");
        }

        $this->bmp = new BMP($pictureFile);
    }

    /**
     * 加密文件
     *
     * @param      $encryptFile
     * @param null $newFileName
     * @return bool|BMP
     */
    public function encrypt($encryptFile, $newFileName = null)
    {
        $this->initFileInfo($encryptFile);
        // 1. 获取数据区所在图片的位置
        // 2. 获取存储数据的总长度
        // 3. 获取存储的总数据
        $offset = $this->getOffsetPoint($this->bmp->getFilePath());
        $length = $this->getMemoryContentSize();
        $content = $this->getMemoryContent();

        // 核心处理文件
        $this->readFileHandler($this->bmp->getFilePath(), function ($pf) use ($offset, $length, $content) {
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

            $this->bmp->setFileData($data);
        });

        if (is_null($newFileName)) {
            return $this->bmp;
        }


        return (bool) file_put_contents($newFileName, $this->bmp->getFileData());
    }


    /**
     * @param null $path
     * @return bool|BMP
     * @throws DirException
     *
     */
    public function decrypt($path = null)
    {
        // 1. 获取数据区所在图片的位置
        $offset = $this->getOffsetPoint($this->bmp->getFilePath());

        $this->readFileHandler($this->bmp->getFilePath(), function ($pf) use ($offset) {
            // 跳过没有操作过的文件头信息，这些数据无法操作
            // 存储文件名和文件数据的长度
            $data = fread($pf, $offset);
            $headSize = Encryption::FILE_NAME_SIZE_STORAGE_LENGTH + Encryption::FILE_DATA_SIZE_STORAGE_LENGTH;

            // 读取位图中的加密信息
            for ($i = 1; !feof($pf); ++$i)
            {
                // 3 个字节代表一个像素 rgb
                fread($pf, 3);
                $alpha = fread($pf, 1);


                if ($i <= Encryption::FILE_NAME_SIZE_STORAGE_LENGTH) {
                    // 1 ~ 4 四个字节    是文件名长度数据
                    $attribute = 'fileNameSize';
                } elseif ($i <= $headSize) {
                    // 5 ~ 16 八个字节    是文件数据长度
                    $attribute = 'fileDataSize';
                } elseif ($i <= ($headSize + intval($this->bmp->getFileNameSize()))) {
                    // 16 ~ $this->file_name_length  是文件名
                    $attribute = 'fileName';
                } elseif ($i <= ($headSize + intval($this->bmp->getFileNameSize())) + intval($this->bmp->getFileDataSize())) {
                    $attribute = 'fileData';
                } else {
                    // 后面的已经不是有效的数据区了，可以直接退出
                    break;
                }

                // 根据动态属性拼接
                $this->bmp->catAttributeValue($attribute, $alpha);

            }

            $this->bmp->setFileName(
                base64_decode($this->bmp->getFileName())
            );
        });

        if (is_null($path)) {
            return $this->bmp;
        }

        if (! is_dir($path)) {
            throw new DirException("[{$path}]目录不存在");
        } elseif (! is_writable($path)) {
            throw new DirException("[{$path}]目录不可写");
        }

        $fileName = trim($path, "/\\").'/'.$this->bmp->getFileName();

        return (bool) file_put_contents($fileName, $this->bmp->getFileData());
    }

}
