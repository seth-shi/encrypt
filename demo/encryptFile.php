<?php
/**
 * Created by PhpStorm.
 * User: WaitMoonMan
 * Date: 2017/5/15
 * Time: 23:25
 */

    require 'common/function.php';
    require 'common/Encrypt.php';

    // 加密的文件
    $encrypt_file = $_FILES['encrypt_file'];
    // bmp位图
    $bmp_file = $_FILES['bmp_file'];




    // 文件上传错误  没有错误是 0
    $encrypt_file['error'] and msgBoxBackPage(getErrormsg($encrypt_file['error']));

    // 文件路径
    $encrypt_path = $encrypt_file['tmp_name'];
    // 加密的文件名
    $encrypt_name = $encrypt_file['name'];


    // bmp 位图是否上传
    if ($bmp_file['error'])
    {
        $bmp_path = $bmp_file['tmp_name'];
        $new_name = $encrypt_file['name'];
    }
    else
    {
        // 用默认的
        $bmp_path = "data/gps.bmp";
        $new_name = "gps.bmp";
    }

    // 实例化
    $encrypt = new Encrypt();
    // 加密成功
    $results = $encrypt->encryptFile($encrypt_path, $bmp_path, $encrypt_name);

    if ($results)
    {
        // 下载文件
        header("Content-Type: image/bmp");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . strlen($results));
        header("Content-Disposition: attachment; filename=" . basename($new_name));
        echo $results;
    }
    else 
    {
        exit($encrypt->getErrorMsg());;
    }