 WaitMoonMan/Encrypt
===================================

[demo 演示地址](http://encrypt.shiguopeng.cn)

## 理论上可以加密 位图大小的 1/4,实际上会小一点
## 增加了 PNG 图片的支持， PNG 图片加密无限制大小

### Encrypt 说明
```php
<?php
   require 'Encrypt.php';

   $encrypt = new Encrypt();
   // 加密文件 ===> 解密的时候注释掉下面两行  参数三是生成新文件, (如果不填则返回加密后的数据二进制流)
   $encrypt->encryptFile('data/data.txt', 'data/bg.bmp', 'gps.bmp') or die($encrypt->getErrorMsg());
   exit('加密成功');

   // 解密文件  参数一是需要解密的文件， 参数二是路径,不要写文件名，因为会自动保留文件名(如果不填则返回加密后的数据二进制流)
   $encrypt->decryptFile('gps.bmp', './') or die($encrypt->getErrorMsg());
   exit('解密成功');
```

### 加密说明
>1.在根目录新建一个 *index.php*
>1.把 上面的代码 复制到 *index.php*, 运行 *index.php* <br />
>2.在 *index.php* 同级目录将会多出一个 **gps.bmp** ( `encryptFile()` 参数三是新文件名 ) 文件<br />
>3.新生成的 **gps.bmp** 就是已经将加密内容加密进去的加密位图<br />
---
### 解密说明
>1.注释掉加密文件的两行代码, 运行 *index.php* <br />
>2.在 *index.php* 同级目录将会多出一个文件, 就是之前的加密文件, `保留文件名, 格式, 大小, 数据`<br />