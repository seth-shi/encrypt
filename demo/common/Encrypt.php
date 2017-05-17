<?php


    class Encrypt
    {
        /**
         * 文件名
         * @var [string]
         */
        private $file_name;
        /**
         * 文件名字长度 ==== 如果不够 4 个字节长度 ==> 将会填充成 4 个字节
         * @var [string | int]
         */
        private $file_name_length;
        /**
         * 文件数据长度 ==== 如果不够 8 个字节长度 ==> 将会填充成 8 个字节 
         * @var [string]
         */
        private $file_data;
        /**
         * 文件数据的长度
         * @var [string | int]
         */
        private $file_data_length;


        /**
         * 错误号
         * @var [int]
         */
        private $errno;
        /**
         * 错误消息
         * @var [string]
         */
        private $errmsg;



        /**
         * 初始化错误
         */
        public function __construct()
        {
            // 初始化四个文件变量
            $this->file_name = "";
            $this->file_name_length = "";
            $this->file_data = "";
            $this->file_data_length = "";

            // 0 代表没有错误
            $this->errno = 0;
            $this->errmsg = "没有错误~";
        }


        /**
         * 加密文件 -> 核心方法
         *
         * @param $encrypt_file     加密文件 ( 绝对路径 )
         * @param $pic              图片文件（ 暂支持 BMP/PNG ）
         * @param string $new_name 加密出来的新文件名 （如果不填则返回加密后的数据） ( BMP/PNG )
         * @return bool
         */
        public function encryptFile($encrypt_file, $pic_file, $new_name = null)
        {
            // 文件是否存在
            if (!is_file($pic_file))
            {
                $this->errno = 1;

                return false;
            }

            // 文件是否存在
            if (is_file($encrypt_file))
            {
                // 初始化文件信息
                $this->initFileInfo($encrypt_file, $new_name);
            }
            else
            {
                // 不是文件
                $this->errno = 2;

                return false;
            }

            // 获取文件类型
            $mime = getimagesize($pic_file);
            // 是否为图片文件
            if (!$mime)
            {
                $this->errno = 3;

                return false;
            }

            // 加密不同类型的文件
            $img_data = "";
            switch ($mime['mime'])
            {
                case "image/png":
                    $img_data = $this->encryptPNG($pic_file);
                    break;
                case "image/x-ms-bmp":
                    $img_data = $this->encryptBMP($pic_file);
                    break;
                default:
                    $this->errno = 4;

                    return false;
                    break;
            }

            // 判断是否生成数据出错
            if (!$img_data)
            {
                return false;
            }

            // 是否生成新文件
            if (!is_null($new_name))
            {
                return $img_data;
            }
            else
            {
                // 把内容写成新图片
                if (file_put_contents($new_name, $img_data))
                {
                    return true;
                }
                else
                {
                    $this->errno = 6;

                    return false;
                }
            }

        }

        /**
         * 加密 BMP 位图
         * @param $bmp
         * @return bool|string
         */
        private function encryptBMP($bmp)
        {

            // 获取 BMP 文件偏移数据位置
            $offset = $this->getOffsetPoint($bmp);

            // 打开文件
            $pf = fopen($bmp, 'rb');

            // 一次性读取完文件头信息 无法写入数据区域
            $encrypt_img = fread($pf, $offset);

            // 获取格式化文件信息  第一个是文件信息长度， 第二个是文件内容
            list($length, $content) = $this->getFormatFileInfo();



            // 把 Alpha 换成加密内容
            for ($i = 0; !feof($pf); ++$i)
            {
                // 3 个字节代表一个像素 最后一个字节是 Alpha 通道-透明度
                $red   = fread($pf, 1);
                $green = fread($pf, 1);
                $blue  = fread($pf, 1);
                $alpha = fread($pf, 1);

                // 前面四个字节存储文件名长度
                if ($i < $length)
                {
                    $encrypt = $content{$i};
                }
                else
                {
                    $encrypt = $alpha;
                }

                $encrypt_img .= $red . $green . $blue . $encrypt;

            }

            // 关闭文件
            fclose($pf);

            // 加密内容过多
            if ($length > $i)
            {
                $this->errno = 5;

                return false;
            }

            return $encrypt_img;
        }

        /**
         * 加密 PNG 文件
         * @param $png
         * @return string
         */
        private function encryptPNG($png)
        {
            // 秘钥
            $key = substr(uniqid(), 3);
            // 对文件数据加密一下

            $this->file_data = $this->binEnAndDe($this->file_data, $key);
            $this->file_data_length = str_pad(strlen($this->file_data), 12, '0', STR_PAD_LEFT);

            // 格式化好的文件信息  _GPS 只是一个标记 标记之后的数据是加密的数据
            list(, $content) = $this->getFormatFileInfo();


            // 标记 -> 10 b 秘钥
            $file_info = "_GPS" . $key . $content;


            $pf = fopen($png, 'rb');

            // 1：8b -> 文件头信息   第一个字节是 137 超出了 ASCII 字符， 所以显示不正常
            $png_data = "";
            $file_head = fread($pf, 8);
            $png_data .= $file_head;


            while (!feof($pf))
            {
                // 2: 数据块长度
                $chunk_length = fread($pf, 4);
                $png_data .= $chunk_length;
                $chunk_length = unpack('N', $chunk_length)[1];

                // 数据块类型
                $chunk_type_code = fread($pf, 4);
                $png_data .= $chunk_type_code;


                // 是否是文件末尾
                if ($chunk_type_code != "IEND")
                {
                    // 3: 6b -> PLTE
                    $chunk_data = fread($pf, $chunk_length);
                    $png_data .= $chunk_data;

                    // crc 数据块结尾标记
                    $crc = fread($pf, 4);
                    $png_data .= $crc;
                }
                else
                {
                    // 多写几个数据看看
                    $png_data .= $file_info;

                    // 多读一个字节，让文件到末尾
                    $crc = fread($pf, 5);
                    $png_data .= $crc;

                    if (!feof($pf))
                    {
                        $this->errno = -4;

                        return false;
                    }
                }

            }
            fclose($pf);

            return $png_data;
        }


        /**
         * 初始化文件信息
         * 
         * @param $encrypt_file     文件路径 ( 绝对路径 )
         */
        private function initFileInfo($encrypt_file, $new_name)
        {
            $file = basename($new_name);

            // base64 加密后的文件名
            $this->file_name = base64_encode($file);
            // 文件名字长度 四个字节长度存储
            $this->file_name_length = str_pad(strlen($this->file_name), 4, '0', STR_PAD_LEFT);
            // 把文件信息添加到文件内容前面
            $this->file_data = file_get_contents($encrypt_file);
            // 文件数据长度 八个字节长度存储
            $this->file_data_length = str_pad(strlen($this->file_data), 12, '0', STR_PAD_LEFT);
        }

        /**
         * 对数据进行加密解密（二进制有效)
         * @param $content
         * @param $key
         * @param bool $is_encrypt
         * @return string
         */
        private function binEnAndDe($content, $key, $is_encrypt = true)
        {
            // 解密
            if (!$is_encrypt)
            {
                $content = base64_decode($content);
            }


            $content_length = strlen($content);
            $key_length = strlen($key);

            $encrypt = "";
            for ($i = 0; $i < $content_length; ++ $i)
            {
                // ;
                $encrypt .= $content{$i} ^ substr($key, $i%$key_length, 1);
            }

            // 加密
            if ($is_encrypt)
            {
                $encrypt = base64_encode($encrypt);
            }

            return $encrypt;
        }

        /**
         * 获取格式化后的文件信息
         * 
         * @return array [0] => 文件存储大小  [1] => 文件内容
         */
        private function getFormatFileInfo()
        {
            $info = [];
            // 先确定需要存储多大的内存存储
            // 文件名字=4 + 文件数据=8 + $this->file_name_length + $this->file_data_length
            $info[] = 4 + 12 + intval($this->file_name_length) + intval($this->file_data_length);

            // 数据信息  和上面的 大小一一对应
            $info[] = $this->file_name_length . $this->file_data_length . $this->file_name . $this->file_data;

            return $info;
        }


        /**
         * 解密文件 -> 核心方法
         *
         * @param $pic_file   解密的图片
         * @return bool
         */
        public function decryptFile($pic_file, $path = null)
        {
            // 文件是否存在
            if (!is_file($pic_file))
            {
                $this->errno = -1;

                return false;
            }


            // 获取文件类型
            $mime = getimagesize($pic_file);
            // 是否为图片文件
            if (!$mime)
            {
                $this->errno = 3;

                return false;
            }

            // 加密不同类型的文件
            $img_data = "";
            switch ($mime['mime'])
            {
                case "image/png":
                    $img_data = $this->decryptPNG($pic_file);
                    break;
                case "image/x-ms-bmp":
                    $img_data = $this->decryptBMP($pic_file);
                    break;
                default:
                    $this->errno = 4;

                    return false;
                    break;
            }


            if ($this->file_name == "" || $this->file_data == "")
            {
                $this->errno = -3;

                return false;
            }


            // 文件名
            $this->file_name = base64_decode($this->file_name);


            if (is_null($path))
            {
                return ['file_name' => $this->file_name, 'file_data' => $this->file_data];
            }
            else
            {
                $path = trim($path, "/\\");

                // 写入新文件
                if (file_put_contents($path . "/" . $this->file_name, $this->file_data))
                {
                    return true;
                }
                else
                {
                    $this->errno = -2;

                    return false;
                }
            }

            
        }

        /**
         * 解密 BMP 文件图
         * @param $bmp
         */
        private function decryptBMP($bmp)
        {
            // 获取文件偏移数据位置
            $offset = $this->getOffsetPoint($bmp);

            $pf = fopen($bmp, 'rb');
            // 读出与内容无关的信息
            fread($pf, $offset);


            // 读取位图中的加密信息
            for ($i = 1; !feof($pf); ++$i)
            {
                // 3 个字节代表一个像素
                $red   = fread($pf, 1);
                $green = fread($pf, 1);
                $blue  = fread($pf, 1);
                $alpha = fread($pf, 1);


                if ($i <= 4)
                {
                    // 1 ~ 4 四个字节    是文件名长度数据
                    $this->file_name_length .= $alpha;

                }
                elseif ($i <= 16)
                {
                    // 5 ~ 16 八个字节    是文件数据长度
                    $this->file_data_length .= $alpha;

                }
                elseif ($i <= (16 + $this->file_name_length))
                {
                    // 16 ~ $this->file_name_length  是文件名
                    $this->file_name .= $alpha;
                }
                elseif ($i <= (16 + $this->file_name_length + $this->file_data_length))
                {
                    // (16 + $this->file_name_length) ~ $this->file_data_length 是文件数据
                    $this->file_data .= $alpha;
                }
                else
                {
                    break;
                }

            }
        }


        /**
         * 解密 PNG 图片
         * @param $png
         */
        private function decryptPNG($png)
        {
            $pf = fopen($png, 'rb');

            // 1：8b -> 文件头信息   第一个字节是 137 超出了 ASCII 字符， 所以显示不正常
            $file_head = fread($pf, 8);

            while (!feof($pf))
            {
                // 2: 数据块长度
                $chunk_length = fread($pf, 4);

                $chunk_length = unpack('N', $chunk_length)[1];

                // 数据块类型
                $chunk_type_code = fread($pf, 4);



                // 是否是文件末尾
                if ($chunk_type_code != "IEND")
                {
                    // 3: 6b -> PLTE
                    $chunk_data = fread($pf, $chunk_length);
                    // crc
                    $crc = fread($pf, 4);
                }
                else
                {
                    // 标识
                    $flag = fread($pf, 4);

                    // 这个是加密的数据
                    if ($flag == "_GPS")
                    {
                        // 10 个字节是秘钥
                        $key = fread($pf, 10);

                        // 先 4 个字节是文件名长度
                        // 再 12 个字节是文件内容长度
                        $file_name_length = fread($pf, 4);
                        $file_data_length = fread($pf, 12);

                        $this->file_name = fread($pf, $file_name_length);
                        $this->file_data = fread($pf, $file_data_length);

                        // 解密数据
                        $this->file_data = $this->binEnAndDe($this->file_data, $key, false);
                    }

                    // 多读一个字节，让文件到末尾
                    $crc = fread($pf, 5);
                }


            }
            fclose($pf);
        }

        /**
         * 得到位图阵列相对于文件头的偏移
         * 
         * @param string    位图文件 ( 绝对路径 )
         * @return int
         */
        private function getOffsetPoint($filename)
        {
            $pf = fopen($filename, 'rb');

            // 第 1 2 个字节是 BM
            $exten = fread($pf, 2);

            // 位图文件的大小
            $size = fread($pf, 4);
            // 转为十进制 不带有符号的长模式[long]（通常是32位，按机器字节顺序） 32 位正好四个字节
            // $size = unpack('L', $size);

            // 第 7  8  9  10 字节是保留的 必须为 0
            $retain = fread($pf, 4);

            // 第 11 12 13 14 字节给出位图阵列相对于文件头的偏移
            $offset = fread($pf, 4);
            $offset = unpack('L', $offset);
            $offset = $offset[1];

            fclose($pf);

            return $offset;
        }

        /**
         * 获取错误信息
         *
         * @return string
         */
        public function getErrorMsg()
        {
            $this->setErrorMsg();

            return $this->errmsg;
        }

        /**
         * 设置错误信息
         */
        private function setErrorMsg()
        {
            switch ($this->errno)
            {
                // 解密错误
                case -1:
                    $this->errmsg = "要解密的图片文件不存在";
                    break;
                case -2:
                    $this->errmsg = "生成数据文件失败";
                    break;
                case -3:
                    $this->errmsg = "解密数据出错";
                    break;
                case -4:
                    $this->errmsg = "文件已被加密过";
                    break;

                // 加密错误
                case 1:
                    $this->errmsg = "加密到的 BMP 图片不存在";
                    break;
                case 2:
                    $this->errmsg = "要加密的文件不存在";
                    break;
                case 3:
                    $this->errmsg = "不是有效的图片文件";
                    break;
                case 4:
                    $this->errmsg = "不支持该文件类型";
                    break;
                case 5:
                    $this->errmsg = "需要加密的文件过大";
                    break;
                case 6:
                    $this->errmsg = "生成 BMP 文件错误";
                default:
                    $this->errmsg = "未知错误";
                    break;
            }
        }


        public function __destruct()
        {
            // 关闭文件
        }
    }