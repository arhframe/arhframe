<?php
import('arhframe.Config');
if (empty($_SERVER["REQUEST_SCHEME"])) {
    if (!empty($_SERVER["HTTPS"])) {
        $_SERVER["REQUEST_SCHEME"] = 'https';
    } else {
        $_SERVER["REQUEST_SCHEME"] = 'http';
    }
}
$servername = dirname($_SERVER['SCRIPT_NAME']);
if ($servername == '/') {
    $servername = null;
}
if (!($_SERVER['SERVER_PORT'] == 80 && $_SERVER["REQUEST_SCHEME"] == 'http') &&
    !($_SERVER['SERVER_PORT'] == 443 && $_SERVER["REQUEST_SCHEME"] == 'https')
) {
    $port = ':' . $_SERVER['SERVER_PORT'];
}
define("SERVERNAME", $_SERVER["REQUEST_SCHEME"] . '://' . $_SERVER["SERVER_NAME"] . $port . $servername);

include_once __DIR__ . '/simpleconst.php';
