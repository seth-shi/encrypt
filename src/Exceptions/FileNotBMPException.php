<?php

namespace DavidNineRoc\Encrypt\Exceptions;

use Exception;

class FileNotBMPException extends Exception
{
    protected $message = '文件不是正确的 BMP 位图';
}
