<?php

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
