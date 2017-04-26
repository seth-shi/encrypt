<?php
    define('ROOT_PATH', dirname(__FILE__));

    $file = ROOT_PATH . '/bg.bmp';

    // 打开文件
    $pf = fopen($file, 'ab+');

    // 1）BMP文件头
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

    // 读到第 28 个字节
    fread($pf, 14);

    // 2）点位图信息
    // 第 29 30 字节描述的是像素的位数  S – 不带有符号的短模式[short]（通常是16位，按机器字节排序）
    $point = fread($pf, 2);
    $point = unpack('S', $point);

    // 读到第 34 位
    fread($pf, 4);

    // 第 35、36、37、38字节确定图像字节数的多少，但通常此项为空。
    $bit_count = fread($pf, 4);
    $bit_count = unpack('L', $bit_count);


    // 一次读取一个字节 也就是八位
    while (!feof($pf))
    {
        // 3 个字节代表一个像素
        $red   = fread($pf, 1);
        $green = fread($pf, 1);
        $blue  = fread($pf, 1);


        // C 255
        // 红色可以改  2 个
        $red   = unpack('C', $red)[1];
        // 绿色可以改  1 个
        $green = unpack('C', $green)[1];
        // 蓝色可以改  5 个
        $blue  = unpack('C', $blue)[1];


        // TODO
        echo $red;
        var_dump(bin2bstr($red));

        // 3）位图阵列
        // 从第 39 个字节开始，每 3 个字节表示一个像素，这 3 个字节依次表示该像素的红、绿、蓝亮度分量值
        exit();
    }

    fclose($pf);


    // 把 char 类型转化为 二进制显示的字符串
    function bin2bstr($input)
    {
        // 转化为二进制
        $bin = base_convert($input, 10, 2);
        // 填充 0
        $bin = str_pad($bin, 8, '0', STR_PAD_LEFT);

        return $bin;
    }