<?php
define("ROOT", __DIR__ . '/../..');
require_once dirname(__FILE__) . '/../javastyleloader/JavaStyleLoader.php';
import('arhframe.Config');
import('vendor.autoload');
import('arhframe.errorhandler.ErrorHandler');
import('arhframe.ResourcesManager');
import('arhframe.LoggerManager');
$config = Config::getInstance();

new ErrorHandler();
/*$folder = new Folder(__DIR__ .'/../user/Resources/translations');
$files = $folder->getFiles('/\.(yml|yaml)$/');

foreach ($files as $file){
	echo print_r($file);
	$extract = explode('.', $file->getBase());
	$lang = $extract[1];
	if($extract[0] == 'FOSUserBundle'){
		$extract[0] = 'lang';
	}
	$fileFinal = new File($file->getFolder() .'/'. $lang .'/'. $extract[0] .'.yml');
	$fileFinal->setContent($file->getContent());
}*/
import('arhframe.ioc.IocArt');
$ioc = new IocArt('/arhframe/ioc/context/deploy.yml');
$ioc->loadContext();
$deploy = $ioc->getBean('arhframe.deployApi');
$deploy->deploy();
//var_dump($deploy->getDeployConfig());