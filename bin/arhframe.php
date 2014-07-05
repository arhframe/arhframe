<?php
error_reporting(E_ALL & ~E_NOTICE);
define("ROOT", __DIR__ . '/..');
require_once dirname(__FILE__) . '/../arhframe/javastyleloader/JavaStyleLoader.php';
import('arhframe.Config');
import('vendor.autoload');
import('arhframe.errorhandler.ErrorHandler');
import('arhframe.ResourcesManager');
import('arhframe.LoggerManager');
$config = Config::getInstance();
import('bin.command.*');
$console = new ConsoleKit\Console();
$console->addCommand('DeployCommand');
$console->run();
//var_dump($deploy->getDeployConfig());