<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/functions.php';

// 加密的文件
$encryptFile = $_FILES['encrypt_file'];
// bmp位图
$bmpFile = $_FILES['bmp_file'];
// 获取正确的文件名
$path = dirname($encryptFile['tmp_name']);
$encryptFileName = $path.'/'.$encryptFile['name'];
rename($encryptFile['tmp_name'], $encryptFileName);

// 文件上传错误  没有错误是 0
if (0 !== $encryptFile['error']) {
    msgBoxBackPage(getErrorMsg($encryptFile['error']));
}

// bmp 位图是否上传
$bmpPath = __DIR__."/../data/gps.bmp";
$newName = "gps.bmp";
if ($bmpFile['error'] == 0)
{
    $bmpPath = $bmpFile['tmp_name'];
    $newName = $bmpFile['name'];
}

try {
    // 内存可能不足，需要设置
    ini_set('memory_limit', '256M');
    // 加密文件
    $encrypt = new \DavidNineRoc\Encrypt\Handler($bmpPath);
    $fileStream = $encrypt->encrypt($encryptFileName);


    // 下载文件
    header("Content-Type: image/bmp");
    header("Accept-Ranges: bytes");
    header("Accept-Length: " . $fileStream->getDataSize());
    header("Content-Disposition: attachment; filename=" . $newName);

    echo $fileStream->getData();

} catch (Exception $e) {

    msgBoxBackPage($e->getMessage());
}



