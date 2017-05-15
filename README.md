 WaitMoonMan/Encrypt
===================================  
 ![gps](https://avatars0.githubusercontent.com/u/28035971?v=3&s=460 "gps")  

### 加密说明
> 加密内容到 BMP 文件中
>
><?php
>   require 'Encrypt.php';
>
>   $encrypt = new Encrypt();
>   // 加密文件
>   $encrypt->encryptFile('data.txt', 'bg.bmp') or die($encrypt->getErrorMsg());
>   echo "加密成功";

### 解密说明
> 解密 BMP 文件的内容
><?php
>   require 'Encrypt.php';
>
>   $encrypt = new Encrypt();
>   // 解密文件
>   $encrypt->decryptFile('gps.bmp') or die($encrypt->getErrorMsg());
>   echo "解密成功";
