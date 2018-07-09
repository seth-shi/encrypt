<?php

namespace DavidNineRoc\Encrypt\Exceptions;

use Exception;

class FileNonExistsException extends Exception
{
    protected $message = '文件不存在';
}
