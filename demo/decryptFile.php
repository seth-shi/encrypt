<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/functions.php';

// 需要解密的文件
$encryptFile = $_FILES['decrypt_file'];


// 文件上传错误
if ($encryptFile['error'] !== 0) {
    msgBoxBackPage(getErrormsg($encryptFile['error']));
}

try {
    $encrypt = new \DavidNineRoc\Encrypt\Handler($encryptFile['tmp_name']);
    $fileStream = $encrypt->decrypt();

    // 下载文件
    header('Content-Type: application/octet-stream');
    header("Accept-Ranges: bytes");
    header("Accept-Length: " . $fileStream->getDataSize());
    header("Content-Disposition: attachment; filename=" . $fileStream->getName());
    echo $fileStream->getData();
} catch (Exception $exception) {
    msgBoxBackPage($exception->getMessage());
}




