<?php

namespace DavidNineRoc\Encrypt\Exceptions;

use Exception;

class DirException extends Exception
{
    protected $message = '目录不存在';
}
