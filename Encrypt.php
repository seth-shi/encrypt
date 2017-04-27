<?php


    class Encrypt
    {
        private $data;
        private $pf;

        // 需要加密或者解密的文件
        public function __construct()
        {

        }


        /**
         * 加密文件
         * @param $bmp        bmp 文件 必需
         * @param $encrypt    加密内容
         */
        public function encryptFile($bmp, $content)
        {
            // 判断是否为文件
            if (is_file($content))
            {

                // 把文件信息添加到文件内容前面
                $encrypt = $content;
            }
            else
            {
                // 四个 0 表示不是文件
                $encrypt = "0000" . $content;
            }


            // 获取文件偏移数据位置
            $offset = $this->getOffsetPoint($bmp);

            $pf = fopen($bmp, 'rb');
            // 图片数据
            $data = "";
            $data = fread($pf, $offset);

            $length = strlen($encrypt);
            for ($i = 0; !feof($pf); ++$i)
            {
                // 3 个字节代表一个像素
                $red   = fread($pf, 1);
                $green = fread($pf, 1);
                $blue  = fread($pf, 1);
                $alpha = fread($pf, 1);

                // 把图片写入 $data  如果读取完 $encrypt 就使用原来 bmp 的内容
                if ($i < $length)
                {
                    $data .= $red . $green . $blue . $data{$i};
                }
                else
                {
                    $data .= $red . $green . $blue . $alpha;
                }
            }

            fclose($pf);

            if ($length > $i)
            {
                echo "需要加密的文件过大，图像文件已被破坏";
            }

            file_put_contents('gps.bmp', $data);

            echo "加密成功";
        }

        /**
         * 获取文件信息
         * @param $filename
         * @return string
         */
        private function getFileInfo($filename)
        {
            $name = basename($filename);
            // 文件名长度
            $size = strlen($name);
            $size = str_pad($size, 4, '0', STR_PAD_LEFT);

            // 文件内容长度 20 个字节存储
            $length = strlen(file_get_contents($filename));
            $length = str_pad($size, 20, '0', STR_PAD_LEFT);

            // 文件名长度 一个字节可以存储 255 个数 没想出个所以然， 所以用四个字节存储
            // 文件名长度  文件名
            $info = $size . $name . $length;

            return $info;
        }


        public function decryptFile($bmp)
        {
            // 获取文件偏移数据位置
            $offset = $this->getOffsetPoint($bmp);

            $pf = fopen($bmp, 'rb');
            // 读出与内容无关的信息
            fread($pf, $offset);

            // 加密文件
            // $filename = "";
            // 给个大于 3 的值判断
            // $name_length = 100;
            // 文件内容
            $data = "";
            // 内容长度
            // $length = 1000;

            for ($i = 0; !feof($pf); ++$i)
            {
                // 3 个字节代表一个像素
                $red   = fread($pf, 1);
                $green = fread($pf, 1);
                $blue  = fread($pf, 1);
                $alpha = fread($pf, 1);

                $data .= $alpha;
            }

            file_put_contents('gps.txt', $data);
        }

        /**
         * 参数必须是 bmp 文件
         * 得到位图阵列相对于文件头的偏移
         * @param string $filename
         * @return string
         */
        private function getOffsetPoint($filename = "bmp.bmp")
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


        public function __destruct()
        {
            // 关闭文件
        }
    }