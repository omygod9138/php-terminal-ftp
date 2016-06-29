<?php
@ob_end_clean();
@ob_flush();
@flush();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include('Ftp.php');
@ini_set('zlib.output_compression',0);
@ini_set('implicit_flush',1);
set_time_limit(0);
define('FTP_URL', 'www.redidea.com.tw');
define('USERNAME', 'john');
define('PASSWORD', 'john');
$linebreak =( php_sapi_name() == 'cli' ) ? PHP_EOL : "<br>";
$settings = array('ftp_url' => FTP_URL, 'username'=>USERNAME, 'password'=>PASSWORD, 'conn'=>$wow_db );
$work = new Ftp($settings);
echo "work start ".date('H:i:s').$linebreak;
$work->start();
