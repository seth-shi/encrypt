<?php

namespace DavidNineRoc\Encrypt;


use DavidNineRoc\Encrypt\Exceptions\FileNonExistsException;
use DavidNineRoc\Encrypt\Exceptions\FileNotBMPException;
use DavidNineRoc\Encrypt\Exceptions\ReadFileException;
use DavidNineRoc\Encrypt\Foundation\Encryption;
use DavidNineRoc\Encrypt\Foundation\FileStream;
use DavidNineRoc\Encrypt\Foundation\FileSystem;


class Handler extends FileSystem
{
    /**
     * 参数传入一个文件路径
     *
     * @param $file
     * @throws FileNonExistsException
     * @throws FileNotBMPException
     */
    public function __construct($file)
    {
        $this->checkFileType($file);
        $this->fileStream = new FileStream($file);
    }

    /**
     * 检查文件是否存在，并且是一个有效的 BMP 文件
     *
     * @param $pictureFile
     * @throws FileNonExistsException
     * @throws FileNotBMPException
     */
    protected function checkFileType($pictureFile)
    {
        if (! is_file($pictureFile)) {
            throw new FileNonExistsException("[{$pictureFile}]不是一个有效的文件");
        }


        if (!function_exists('mime_content_type')) {
            $type = mime_content_type($pictureFile);
        } else {
            $type = getimagesize($pictureFile);
            $type = $type === false ? '' : $type['mime'];
        }

        if (false === strpos($type, 'bmp')) {
            throw new FileNotBMPException("[{$pictureFile}]不是正确的图片类型");
        }
    }

    /**
     * 加密文件调用此方法
     * 参数一需传入一个需要加密的文件（不限类型
     *    否则生成新文件
     *
     * @param  $encryptFile
     * @return FileStream
     */
    public function encrypt($encryptFile)
    {
        $this->initFileInfo($encryptFile);

        // 1. 获取数据区所在图片的位置
        // 2. 获取存储数据的总长度
        // 3. 获取存储的总数据
        $offset = $this->getOffsetPoint($fileStreamPath = $this->fileStream->getPath());
        $length = $this->getContentSize();
        $content = $this->getContent();


        // 核心处理文件
        $this->readFileHandler($fileStreamPath, function ($pf) use ($offset, $length, $content) {
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

            $this->fileStream->setData($data);
        });


        return $this->fileStream;
    }


    /**
     * 解密文件, 直接返回一个 FileStream 对象
     *
     * @return FileStream
     */
    public function decrypt()
    {
        // 1. 获取数据区所在图片的位置
        $offset = $this->getOffsetPoint($fileStreamPath = $this->fileStream->getPath());

        $this->readFileHandler($fileStreamPath, function ($pf) use ($offset) {
            // 跳过没有操作过的文件头信息，这些数据无法操作
            // 存储文件名和文件数据的长度
            fread($pf, $offset);

            // 读取位图中的加密信息
            for ($i = 1; !feof($pf); ++$i)
            {
                // 3 个字节代表一个像素 rgb
                fread($pf, 3);
                $alpha = fread($pf, 1);

                // 1 ~ 4 四个字节    是文件名大小
                if ($i <= Encryption::FILE_NAME_SIZE_STORAGE_LENGTH) {
                    $this->fileStream->catNameSize($alpha);
                }
                // 5 ~ 16 八个字节    是文件数据的大小
                elseif ($i <= $this->getHeadDataSize()) {
                    $this->fileStream->catDataSize($alpha);
                }
                // 16 ~ 16+BMP::nameSize 因为要前面两个判断会影响 nameSize，所以,不断通过函数判断
                elseif ($i <= $this->getHeadDataAndNameSize()) {
                    $this->fileStream->catName($alpha);
                }
                // 如上，上面是文件名字长度，这个是文件内容长度
                elseif ($i <= $this->getContentSize()) {
                    $this->fileStream->catData($alpha);
                }
                // 后面的已经不是有效的数据区了，可以直接退出
                else {
                    break;
                }

            }

        });

        return $this->fileStream;
    }
}
