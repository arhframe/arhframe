<?php
import('arhframe.Config');
$servername = dirname($_SERVER['SCRIPT_NAME']);
if ($_SERVER['SERVER_PORT']==80) {

    define("SERVERNAME", 'http://'.$_SERVER['SERVER_NAME']. $servername);
} else {
    define("SERVERNAME", 'http://'.$_SERVER['SERVER_NAME'].':'. $_SERVER['SERVER_PORT'] . $servername);
}
include_once __DIR__ .'/simpleconst.php';
