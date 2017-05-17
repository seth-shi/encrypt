<?php
    // 引入加密文件
    require 'common/Encrypt.php';
    require 'common/function.php';

    // 需要解密的文件
    $encrypt_file = $_FILES['decrypt_file'];


    // 文件上传错误
    $encrypt_file['error'] and msgBoxBackPage(getErrormsg($encrypt_file['error']));


    // 文件路径
    $encrypt_path = $encrypt_file['tmp_name'];



    $encrypt = new Encrypt();

    // 解密文件 直接返回数据  [0] => 文件名  [1] => 文件数据
    $results = $encrypt->decryptFile($encrypt_path);

    if ($results)
    {
        // 下载文件
        header('Content-Type: application/octet-stream');
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . strlen($results['file_data']));
        header("Content-Disposition: attachment; filename=" . $results['file_name']);
        echo $results['file_data'];
    }
    else
    {
        msgBoxBackPage($encrypt->getErrorMsg());
    }



