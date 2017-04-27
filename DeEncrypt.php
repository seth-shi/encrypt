<?php
define('ROOT_PATH', dirname(__FILE__));

$file = ROOT_PATH . '/image.bmp';

// 打开文件
$pf = fopen($file, 'ab+');

// ####################################################
// 位图文件头
// 大小: 14 byte
//
//
// 第 1 2 个字节是 BM
$exten = fread($pf, 2);

// 第 3  4  5  6     字节存放的是位图文件的大小
$size = fread($pf, 4);
// 转为十进制 不带有符号的长模式[long]（通常是32位，按机器字节顺序） 32 位正好四个字节
$size = unpack('L', $size);

// 第 7  8  9  10    字节是保留的 必须为 0
$retain = fread($pf, 4);
$retain = unpack('L', $retain);

// 第 11 12 13 14    字节给出位图阵列相对于文件头的偏移
$devi = fread($pf, 4);
$devi = unpack('L', $devi);

// 返回文件指针头
rewind($pf);

// 偏移到数据区
$devi = $devi[1];

// 直接读取前面的 $devi 个字节，跳到数据区
$image = "";
fread($pf, $devi);

// 一次读取4个字节
$i = 0;
while (!feof($pf))
{
    // 3 个字节代表一个像素
    $red   = fread($pf, 1);
    $green = fread($pf, 1);
    $blue  = fread($pf, 1);
    $Alpha = fread($pf, 1);



    $image .= $Alpha;

    $i ++;
    // 3）位图阵列
    // 从第 39 个字节开始，每 3 个字节表示一个像素，这 3 个字节依次表示该像素的红、绿、蓝亮度分量值
}

fclose($pf);

// 写入新文件
file_put_contents('dd.txt', $image);