<?php

namespace DavidNineRoc\Encrypt;


class BMP
{
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

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return BMP
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * @return int
     */
    public function getFileNameSize()
    {
        return $this->fileNameSize;
    }

    /**
     * @param int $fileNameSize
     * @return BMP
     */
    public function setFileNameSize($fileNameSize)
    {
        $this->fileNameSize = $fileNameSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileData()
    {
        return $this->fileData;
    }

    /**
     * @param string $fileData
     * @return BMP
     */
    public function setFileData($fileData)
    {
        $this->fileData = $fileData;

        return $this;
    }

    /**
     * @return int
     */
    public function getFileDataSize()
    {
        return $this->fileDataSize;
    }

    /**
     * @param int $fileDataSize
     * @return BMP
     */
    public function setFileDataSize($fileDataSize)
    {
        $this->fileDataSize = $fileDataSize;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * @param string $filePath
     * @return BMP
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    /**
     * 字符串拼接值
     *
     * @param $attribute
     * @param $value
     */
    public function catAttributeValue($attribute, $value)
    {
        $this->{$attribute} .= $value;
    }
}
