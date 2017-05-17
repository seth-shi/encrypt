<?php

    require 'Encrypt.php';

    $encrypt = new Encrypt();
    // 加密文件 ===> 解密的时候注释掉下面两行  参数三是生成新文件, (如果不填则返回加密后的数据二进制流)
    $encrypt->encryptFile('data/data.txt', 'data/bg.bmp', 'gps.bmp') or die($encrypt->getErrorMsg());
    exit('加密成功');

    // 解密文件  参数一是需要解密的文件， 参数二是路径,不要写文件名，因为会自动保留文件名(如果不填则返回加密后的数据二进制流)
    $encrypt->decryptFile('gps.bmp', './') or die($encrypt->getErrorMsg());
    exit('解密成功');


