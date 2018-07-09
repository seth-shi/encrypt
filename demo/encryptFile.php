<?php

require __DIR__.'/../vendor/autoload.php';

// 加密的文件
$encryptFile = $_FILES['encrypt_file'];
// bmp位图
$bmpFile = $_FILES['bmp_file'];


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
    $results = $encrypt->encrypt($encryptFile['tmp_name']);

    if ($results)
    {
        // 下载文件
        header("Content-Type: image/bmp");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . strlen($results));
        header("Content-Disposition: attachment; filename=" . basename($newName));
        echo $results;
    }

} catch (Exception $e) {
    msgBoxBackPage($e->getMessage());
}


/**
 * 弹出提示框并返回上一页
 * @param $msg
 */
function msgBoxBackPage($msg)
{
    echo "<script>alert('". $msg ."');window.history.go(-1);</script>";
    exit;
}

/**
 * 获取文件上传错误信息
 * @param  int
 * @return string
 */
function getErrorMsg($errorCode)
{
    switch ($errorCode)
    {
        case 1:
            $errorMsg = '上传的文件超过了 php.ini 中 upload_max_filesize选项限制的值';
            break;
        case 2:
            $errorMsg = '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值';
            break;
        case 3:
            $errorMsg = '文件只有部分被上传';
            break;
        case 4:
            $errorMsg = '没有文件被上传';
            break;
        case 5:
            $errorMsg = '找不到临时文件夹';
            break;
        case 6:
            $errorMsg = '找不到临时文件夹';
            break;
        default:
            $errorMsg = "未知错误";
            break;
    }

    return $errorMsg;
}
