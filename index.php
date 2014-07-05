<?php
define("ROOT", __DIR__);
require_once dirname(__FILE__).'/arhframe/javastyleloader/JavaStyleLoader.php';
import('arhframe.errorhandler.ErrorHandler');
errorHandler(true);
import('arhframe.exception.*');
import('arhframe.file.*');
import('vendor.autoload');
import('arhframe.Config');

$config = Config::getInstance();
errorHandler();
import('arhframe.Router');
import('arhframe.DependanceManager');
import('arhframe.ResourcesManager');
import('arhframe.Controller');

import('arhframe.basefile.const');
import('arhframe.secure.Secure');
import('arhframe.BeanLoader');
import('arhframe.LoggerManager');
function doFolderForModuleDirectory($moduleDirectory){
	ResourcesManager::doFolder(substr($moduleDirectory, 1) . '/'. Router::getInstance()->getModule() .'/resources');

	foreach (DependanceManager::getInstance()->getDependance() as $key => $module) {
		if(DependanceManager::isModuleArhframe('@'. $module)){
			continue;
		}
		ResourcesManager::doFolder(substr($moduleDirectory, 1) . '/'. $module .'/resources');
	}
}
doFolderForModuleDirectory(MODULE_DIRECTORY);
function unregister_GLOBALS()
{
	if (!ini_get('register_globals')) {
		return;
	}

	// Vous pouvez vouloir modifier cela pour avoir une erreur plus jolie
	if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
		die('Tentative d\'effacement des GLOBALS d�tect�e');
	}

	// Les variables � ne jamais effacer
	$noUnset = array('GLOBALS',  '_GET',
	'_POST',    '_COOKIE',
	'_REQUEST', '_SERVER',
	'_ENV',     '_FILES');

	$input = array_merge($_GET,    $_POST,
	$_COOKIE, $_SERVER,
	$_ENV,    $_FILES,
	isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());

	foreach ($input as $k => $v) {
		if (!in_array($k, $noUnset) && isset($GLOBALS[$k])) {
			unset($GLOBALS[$k]);
		}
	}
}
if($config->config->compileauto){
	include_once dirname(__FILE__).'/arhframe/basefile/compile.php';
}

unregister_GLOBALS();
if(!empty($config->config->charset)){
	header('Content-type: text/html; charset='. $config->config->charset);
}else{
	header('Content-type: text/html; charset=utf-8');
}

$route = Router::getInstance();
DependanceManager::getInstance();

$ioc = BeanLoader::getInstance();
@include_once(dirname(__FILE__). '/module/'. $route->getModule().'/controller/'. ucfirst($route->getController()) .'Controller.php');
$beanId = substr(MODULE_DIRECTORY, 1) .'.'. $route->getModule() .'.controller.'. ucfirst($route->getController()) .'Controller';
$beanValue = array('class'=>$beanId, 'extend'=>'arhframe.controller', 'namespace'=>$route->getModule() .'\\controller');
$ioc->addBean($beanId, $beanValue);
$controller = $route->getModule() .'\\controller\\'. ucfirst($route->getController()) .'Controller';
$action = strtolower($route->getAction()) .'Action';
if(!class_exists($controller)){
	throw new Exception('Can\'t find controller "'. ucfirst($route->getController())
		.'" for route: "'. $route->getNameRoute()
		.'" check your routes, controllers name or namespace (should be "'. $route->getModule() .'\\controller".)');
}
$ioc->loadContext();
import('arhframe.debug.VarDump');
$secure = $ioc->getBean('arhframe.secure');
if(!$secure->isTokenMethodValid()){
	die('security token has expired');
}
logger('Route')->addInfo($route->__toString());
$object = $ioc->getBean($beanId);
$object->beforeStartController();
$object->__before();
$content = $object->$action();
if(is_object($content) && $content instanceof Response){
	$content = $content->getContent();
}else if(!is_string($content)){
	$content = print_r($content);
}
echo $content;
$object->__after();
$doctrine = $ioc->getBean('arhframe.DoctrineManager');
//$doctrine->export('/export', 'inklusive');
$ioc->removeBean($beanId);




