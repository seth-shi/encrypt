<?php

namespace DavidNineRoc\Encrypt\Exceptions;

use Exception;

class ReadFileException extends Exception
{
    protected $message = '读取文件出错';
}
