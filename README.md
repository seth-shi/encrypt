 WaitMoonMan/Encrypt
===================================  
 ![gps](https://avatars0.githubusercontent.com/u/28035971?v=3&s=460 "gps")  

[demo 演示地址](http://encrypt.shiguopeng.cn)

## 理论上可以加密 位图大小的 1/4,实际上会小一点
# 加密后的文件不会变大一个字节

### Encrypt 说明
```php
<?php
   require 'Encrypt.php';

   $encrypt = new Encrypt();
   // 加密文件 ===> 解密的时候注释掉下面两行
   $encrypt->encryptFile('data/data.txt', 'data/bg.bmp') or die($encrypt->getErrorMsg());
   exit('加密成功');

   // 解密文件
   $encrypt->decryptFile('gps.bmp') or die($encrypt->getErrorMsg());
   exit('解密成功');
```

### 加密说明
>1.把上面的代码复制到 *index.php*, 运行 *index.php* <br />
>2.在 *index.php* 同级目录将会多出一个 **gps.bmp** ( `encryptFile()` 参数三是新文件名 ) 文件<br />
>3.新生成的 **gps.bmp** 就是已经将加密内容加密进去的加密位图<br />
---
### 解密说明
>1.注释掉加密文件的两行代码, 运行 *index.php* <br />
>2.在 *index.php* 同级目录将会多出一个文件, 就是之前的加密文件, `保留文件名, 格式, 数据`<br />