
<?php

    require 'PngEncrypt.php';
    $png = new PngEncrypt();


    $png->encrypt('data/data.txt', 'data/bg.png');
    // $png->decrypt('gps.png');