<?php

namespace DavidNineRoc\Encrypt\Foundation;


class FileStream
{
    /**
     * @var string $name
     *
     * 文件名字
     */
    protected $name;

    /**
     * @var string $nameSize
     *
     * 文件名字长度
     */
    protected $nameSize;

    /**
     * 文件数据
     *
     * @var string $data
     */
    protected $data;

    /**
     * @var string $dataSize
     *
     * 文件数据长度
     */
    protected $dataSize;

    /**
     * @var string $path
     *
     * 图片文件的路径
     */
    protected $path;

    public function __construct($filePath, $isInit = false)
    {
        $this->path = realpath($filePath);

        if ($isInit) {
            $this->initFileInfo();
        }
    }


    /**
     * 初始化文件信息，得到需要加密文件的文件名、
     * 文件数据，大小等数据
     */
    public function initFileInfo()
    {
        // 1. 获取文件名
        // 2. 直接获取文件数据，如果内存不足，建议之前调用 ini_set
        $fileName = basename($this->path);
        $fileData = file_get_contents($this->path);

        $this->setName($fileName)
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $fileName
     * @return self
     */
    public function setName($fileName)
    {
        $this->name = $fileName;

        return $this;
    }

    /**
     * @return string
     */
    public function getNameSize()
    {
        return $this->nameSize;
    }

    /**
     * @param  string $fileNameSize
     * @return self
     */
    public function setNameSize($fileNameSize)
    {
        $this->nameSize = $fileNameSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $fileData
     * @return self
     */
    public function setData($fileData)
    {
        $this->data = $fileData;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataSize()
    {
        return $this->dataSize;
    }

    /**
     * @param string $fileDataSize
     * @return self
     */
    public function setDataSize($fileDataSize)
    {
        $this->dataSize = $fileDataSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $filePath
     * @return self
     */
    public function setPath($filePath)
    {
        $this->path = $filePath;

        return $this;
    }

    /**
     * 拼接文件名
     *
     * @param $value
     */
    public function catName($value)
    {
        $this->name .= $value;
    }

    /**
     * 拼接文件名大小
     *
     * @param $value
     */
    public function catNameSize($value)
    {
        $this->nameSize .= $value;
    }

    /**
     * 拼接文件数据
     *
     * @param $value
     */
    public function catData($value)
    {
        $this->data .= $value;
    }

    /**
     * 拼接文件数据占用大小
     *
     * @param $value
     */
    public function catDataSize($value)
    {
        $this->dataSize .= $value;
    }


    /**
     * 获取存储的长度
     * 存储文件名长度的长度 + 存储文件数据长度的长度 + 文件名的长度 + 文件数据的长度
     *
     * @return int
     */
    public function getContentSize()
    {
        return $this->getHeadDataSize() + $this->getBodyDataSize();
    }

    /**
     * 获取头数据的大小，存储文件名字占用的大小 +
     * 存储文件内容所占的大小
     *
     * @return int
     */
    public function getHeadDataSize()
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
    public function getHeadDataAndNameSize()
    {
        return $this->getHeadDataSize() + intval($this->getNameSize());
    }
    
    
    /**
     * 获取数据区域的大小，文件名字所占的大小 +
     * 文件内容所占的大小
     *
     * @return int
     */
    public function getBodyDataSize()
    {
        return (
            intval($this->getNameSize()) +
            intval($this->getDataSize())
        );
    }

    
    /**
     * 获取所有需要存储的内容
     *
     * @return string
     */
    public function getContent()
    {
        return (
            $this->getNameSize() .
            $this->getDataSize() .
            $this->getName() .
            $this->getData()
        );
    }
}
