<?php

namespace DavidNineRoc\Encrypt\Foundation;


class BMP
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

    public function __construct($filePath)
    {
        $this->path = realpath($filePath);
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
     * @return BMP
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
     * @return BMP
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
     * @return BMP
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
     * @return BMP
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
     * @return BMP
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
}
