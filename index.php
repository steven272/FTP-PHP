<?php
define('FTP_HOST', '');
define('FTP_USER', '');
define('FTP_PASS', '');

require_once 'Ftp_class.php';

$ftpObject = new Ftp_class();

$ftpObject->connect(FTP_HOST, FTP_USER, FTP_PASS);





