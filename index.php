<?php

    require 'Encrypt.php';

    $encrypt = new Encrypt();

    // $encrypt->encryptFile('data/data.txt', 'data/bg.bmp') or die($encrypt->getErrorMsg());
    // echo "加密成功";

    $encrypt->decryptFile('gps.bmp') or die($encrypt->getErrorMsg());
    echo "解密成功";