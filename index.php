<?php
define("ROOT", __DIR__);
require_once dirname(__FILE__) . '/arhframe/javastyleloader/JavaStyleLoader.php';
import('arhframe.App');
$app = new App();
$app->run();



