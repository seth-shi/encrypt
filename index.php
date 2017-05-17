
<?php

    require 'Encrypt.php';

    $encrypt = new Encrypt();

    $encrypt_file = "data/data.txt";
    $pic_file = "data/bg.png";

    // 加密  png/bmp
    $encrypt->encryptFile($encrypt_file, $pic_file, 'gps.png') or die($encrypt->getErrorMsg());

    // 解密  参数二是路径不包括文件名
    $encrypt->decryptFile("gps.png", '.') or die($encrypt->getErrorMsg());