<?php

    class PngEncrypt
    {
        /**
         * 加密
         * @param $encrypt
         * @param $png
         */
        public function encrypt($encrypt, $png)
        {

            // 文件名
            $file_name = base64_encode(basename($encrypt));
            $file_name_length =  str_pad(strlen($file_name), 4, "0", STR_PAD_LEFT);
            $file_data = file_get_contents($encrypt);
            $file_data_length =  str_pad(strlen($file_data), 12, "0", STR_PAD_LEFT);
            $file_info = "_GPS" . $file_name_length . $file_data_length . $file_name . $file_data;



            $pf = fopen($png, 'rb');

            $data = "";
            // 1：8b -> 文件头信息   第一个字节是 137 超出了 ASCII 字符， 所以显示不正常
            $file_head = fread($pf, 8);
            $data .= $file_head;


            while (!feof($pf))
            {
                // 2: 数据块长度
                $chunk_length = fread($pf, 4);
                $data .= $chunk_length;
                $chunk_length = unpack('N', $chunk_length)[1];

                // 数据块类型
                $chunk_type_code = fread($pf, 4);
                $data .= $chunk_type_code;


                // 是否是文件末尾
                if ($chunk_type_code != "IEND")
                {
                    // 3: 6b -> PLTE
                    echo $chunk_type_code;
                    $chunk_data = fread($pf, $chunk_length);
                    $data .= $chunk_data;
                    // crc
                    $crc = fread($pf, 4);
                    $data .= $crc;
                }
                else
                {
                    // 多写几个数据看看
                    $data .= $file_info;

                    // 多读一个字节，让文件到末尾
                    $crc = fread($pf, 5);
                    $data .= $crc;
                }

                echo "数据块名字：{$chunk_type_code}   大小：" . $chunk_length . '<br />';

            }
            fclose($pf);

            file_put_contents('gps.png', $data);
        }

        /**
         * 解密
         */
        public function decrypt($png)
        {
            $pf = fopen($png, 'rb');

            // 文件内容
            $file_name = "";
            $file_data = "";


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
                        // 先 4 个字节是文件名长度
                        // 再 12 个字节是文件内容长度
                        $file_name_length = fread($pf, 4);
                        $file_data_length = fread($pf, 12);

                        $file_name = base64_decode(fread($pf, $file_name_length));
                        $file_data = fread($pf, $file_data_length);

                       file_put_contents($file_name, $file_data);
                    }

                    // 多读一个字节，让文件到末尾
                    $crc = fread($pf, 5);
                }


            }
            fclose($pf);
        }

    }