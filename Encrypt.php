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
         * @param $encrypt_file     加密文件( 绝对路径 )
         * @param $bmp              位图路径( 绝对路径 )
         * @param string $new_name  加密出来的文件 ( *.bmp )
         * @return bool
         */
        public function encryptFile($encrypt_file, $bmp, $new_name = 'gps.bmp')
        {
            // 文件是否存在
            if (!is_file($bmp))
            {
                $this->errno = 1;

                return false;
            }

            // 文件是否存在
            if (is_file($encrypt_file))
            {
                // 初始化文件信息
                $this->initFileInfo($encrypt_file);
            }
            else
            {
                // 不是文件
                $this->errno = 2;

                return false;
            }


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
                $this->errno = 3;

                return false;
            }

            // 把内容写成新图片
            if (file_put_contents($new_name, $encrypt_img))
            {
                return true;
            }
            else
            {
                $this->errno = 4;

                return false;
            }

        }

        /**
         * 初始化文件信息
         * 
         * @param $encrypt_file     文件路径 ( 绝对路径 )
         */
        private function initFileInfo($encrypt_file)
        {
            $file = basename($encrypt_file);

            // base64 加密后的文件名
            $this->file_name = base64_encode($file);
            // 文件名字长度 四个字节长度存储
            $this->file_name_length = str_pad(strlen($this->file_name), 4, '0', STR_PAD_LEFT);
            // 把文件信息添加到文件内容前面
            $this->file_data = file_get_contents($encrypt_file);
            // 文件数据长度 八个字节长度存储
            $this->file_data_length = str_pad(strlen($this->file_data), 8, '0', STR_PAD_LEFT);
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
            $info[] = 4 + 8 + intval($this->file_name_length) + intval($this->file_data_length);

            // 数据信息  和上面的 大小一一对应
            $info[] = $this->file_name_length . $this->file_data_length . $this->file_name . $this->file_data;

            return $info;
        }


        /**
         * 解密文件 -> 核心方法
         * 
         * @param  $bmp 位图路径( 绝对路径 )
         * @return bool
         */
        public function decryptFile($bmp)
        {
            // 文件是否存在
            if (!is_file($bmp))
            {
                $this->errno = -1;

                return false;
            }

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
                elseif ($i <= 12)
                {
                    // 5 ~ 12 八个字节    是文件数据长度
                    $this->file_data_length .= $alpha;

                }
                elseif ($i <= (12 + $this->file_name_length))
                {
                    // 13 ~ $this->file_name_length  是文件名
                    $this->file_name .= $alpha;
                }
                elseif ($i <= (12 + $this->file_name_length + $this->file_data_length))
                {
                    // (12 + $this->file_name_length) ~ $this->file_data_length 是文件数据
                    $this->file_data .= $alpha;
                }
                else
                {
                    break;
                }

            }

            // 文件名
            $this->file_name = base64_decode($this->file_name);
       
            // 写入新文件
            if (file_put_contents($this->file_name, $this->file_data))
            {
                return true;
            }
            else
            {
                $this->errno = -2;

                return false;
            }
            
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
                    $this->errmsg = "要解密的 BMP 文件不存在";
                    break;
                case -2:
                    $this->errmsg = "生成数据文件失败";
                    break;

                // 加密错误
                case 1:
                    $this->errmsg = "加密到的 BMP 图片不存在";
                    break;
                case 2:
                    $this->errmsg = "要加密的文件不存在";
                    break;
                case 3:
                    $this->errmsg = "需要加密的文件过大";
                    break;
                case 4:
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