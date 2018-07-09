 Encrypt
===================================
[演示地址又回来了](http://encrypt.shiguopeng.cn)
## 理论上可以加密 位图大小的 1/4,实际上会小一点

## 使用
```bash
# 安装
composer require davidnineroc/encrypt
```
```php
<?php
    require __DIR__.'/vendor/autoload.php';
   
    // 位图文件的路径(原始的或者加密过的都是构造参数)
    $bmpPath = __DIR__.'/data/gps.bmp';

    try {
         $encrypt = new \DavidNineRoc\Encrypt\Handler($bmpPath);
         
         // 加密
         $encryptFile = __DIR__.'/data/gps.txt';
         // 返回一个 BMP 对象, 如果传入参数 2，则直接生成新文件
         $bmp = $encrypt->encrypt($encryptFile);
         
         // 返回一个 BMP 对象, 如果传入参数 2，则直接生成新文件
         $bmp = $encrypt->decrypt();
    } catch (Exception $e) {
        echo $e->getMessage();
    }
```
### 解密说明
* [demo](demo/) 目录下有直接可以运行的例子
****
```bash
/****************************************
 * 容易搞混存储的概念，在此说明一下
 * Encryption::FILE_NAME_SIZE_STORAGE_LENGTH
 *     现在是等于 4, 用于约束文件名长度的大小（存储文件名大小的长度）
 * Encryption::FILE_DATA_SIZE_STORAGE_LENGTH
 *     现在是等于 12,用于约束文件数据长度的大小（存储文件数据大小的长度）
 * $fileNameSize
 *     文件名存储的长度，永远都是 Encryption::FILE_NAME_SIZE_STORAGE_LENGTH 位,
 *     4 位的时候可能是这样 0018, 1293 之类的，用来保存文件名长度（base64）
 * $fileDataSize
 *     文件数据存储的长度，永远都是 Encryption::FILE_DATA_SIZE_STORAGE_LENGTH 位,
 *     12 位的时候可能是 000019283848 之类的，用来保存加密文件数据的内容的长度
 ****************************************
 * 所以加密的内容长度有：
 * $fileNameSize + $fileDataSize + $fileName + $fileData
 * 加密文件名的大小（4位
 * 加密文件数据的大小( 12 位
 * 加密文件名
 * 加密文件数据
 ****************************************
 * 读取过程：
 * 1. 先读取 4 位得到文件名的长度 ($foo)，再读取 12 位得到文件数据的大小 ($bar)，
 * 2. 然后再读取 $foo 位，得到文件名存起来，用于命名新文件
 * 3. 然后再读取 $bar 位，得到文件内容，用于创建新文件内容
 * 4. 停止读取, 这样子就不会读取到多余的内容
 */
```
