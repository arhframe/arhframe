<?php
import('arhframe.Config');
$config = Config::getInstance();
$servername = null;
if (!empty($config->config->servername)) {
    $servername = '/'. $config->config->servername;
}
if ($_SERVER['SERVER_PORT']==80) {

    define("SERVERNAME", 'http://'.$_SERVER['SERVER_NAME']. $servername);
} else {
    define("SERVERNAME", 'http://'.$_SERVER['SERVER_NAME'].':'. $_SERVER['SERVER_PORT'] . $servername);
}
include_once __DIR__ .'/simpleconst.php';
