<?php
use Symfony\Component\Yaml\Yaml;
import('arhframe.Router');
import('arhframe.eden.eden');
import('arhframe.cache.CacheManager');
import('arhframe.Config');
import('arhframe.yamlarh.Yamlarh');

/**
*
*/
class DependanceManager
{
    private static $_instance = null;
    private $router;
    private $dependance = array();
    private $dependanceFile = 'dependance.yml';
    private $cache;
    /**
     * [getInstance description]
     * @return [type] [description]
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
        self::$_instance = new DependanceManager();
        }

        return self::$_instance;
    }
    private function __construct()
    {
        $this->router = Router::getInstance();
        $this->cache = cache($this, false, true);
        $dependance = $this->cache->get('dependance');
        if (empty($dependance)) {
            $this->loadDependance($this->router->getModule());
            $this->cache->set('dependance', $this->dependance);
        } else {
            $this->dependance = $dependance;
        }
        $yamlarh = new Yamlarh('config');
        $yamlarh->loadDependance($this);
        Config::loadDependance($yamlarh->parse());
    }
    private function loadDependance($moduleName, $moduleNameOrig=null)
    {
        $fileName = dirname(__FILE__) .'/..'. DependanceManager::getModuleDirectory($moduleName) .'/'. $this->dependanceFile;
        if (!is_file($fileName)) {
            $file = fopen($fileName, 'a');
            fputs($file, 'depend:');
            fclose($file);
        }
        $dependance = Yaml::parse($fileName);
        if (empty($dependance['depend'])) {
            return;
        }
        if (empty($this->dependance)) {
            $this->dependance['depend'] = array();
        }
        foreach ($dependance['depend'] as $moduleNameDependance) {
            if ($moduleNameDependance == $moduleNameOrig) {
                throw new ArhframeException('Redondant dependance between module "'. $moduleNameOrig .'" and his dependance "'. $moduleName .'".');
            }
            if ($moduleNameDependance == $moduleName) {
                throw new ArhframeException('Module "'. $moduleName .'" depend on himself.');
            }
            if (!$this->isModuleExist($moduleNameDependance)) {
                throw new ArhframeException('Module "'. $moduleNameDependance .'" doesn\'t exist in "'. $moduleName .'" dependance file.');
            }
            if (!in_array($moduleNameDependance, $this->dependance['depend'])) {
                $this->dependance['depend'][] = $moduleNameDependance;
                $this->loadDependance($moduleNameDependance, $moduleName);
            }
        }
    }
    public function isModuleExist($moduleName)
    {
        return is_dir(dirname(__FILE__) .'/..'. DependanceManager::getModuleDirectory($moduleName));
    }
    public function getFile($fileName)
    {	
        $force = $this->force($fileName);
        
        if (!empty($force)) {
        	$moduleDirectory = DependanceManager::getModuleDirectory($force);
            return dirname(__FILE__) .'/..'. $moduleDirectory .'/'. DependanceManager::parseForceFileName($fileName);
        }
        if ($this->verifyFile($fileName,  $this->router->getModule())) {
        	$moduleDirectory = DependanceManager::getModuleDirectory($this->router->getModule());
            return dirname(__FILE__) .'/..'. $moduleDirectory .'/'. $fileName;
        }
        if (empty($this->dependance['depend'])) {
            throw new ArhframeException("File ". $fileName ." not found in modules.");
        }
        foreach ($this->dependance['depend'] as $value) {
            if ($this->verifyFile($fileName,  $value)) {
            	$moduleDirectory = DependanceManager::getModuleDirectory($value);
                return dirname(__FILE__) .'/..'. $moduleDirectory. '/'. $fileName;
            }
        }
        throw new ArhframeException("File ". $fileName ." not found in modules.");
    }
    public function getFolder($fileName)
    {
        $force = $this->force($fileName);
        if (!empty($force)) {
        	$moduleDirectory = DependanceManager::getModuleDirectory($force);
            return dirname(__FILE__) .'/..'. $moduleDirectory;
        }
        if ($this->verifyFile($fileName,  $this->router->getModule())) {
        	$moduleDirectory = DependanceManager::getModuleDirectory($this->router->getModule());
            return dirname(__FILE__) .'/..'. $moduleDirectory;
        }
        if (empty($this->dependance['depend'])) {
            throw new ArhframeException("File ". $fileName ." not found in modules.");
        }
        foreach ($this->dependance['depend'] as $value) {
            if ($this->verifyFile($fileName,  $value)) {
            	$moduleDirectory = DependanceManager::getModuleDirectory($value);
                return dirname(__FILE__) .'/..'. $moduleDirectory;
            }
        }
        throw new ArhframeException("File ". $fileName ." not found in modules.");

    }
    private function verifyFile($fileName, $moduleName)
    {
        return is_file(dirname(__FILE__) .'/..'. DependanceManager::getModuleDirectory($moduleName) .'/'. $fileName);
    }
    private function force($fileName)
    {
        if (isset($fileName[0]) && '@' == $fileName[0]) {
            if (false === $pos = strpos($fileName, '/')) {
                throw new ArhframeException(sprintf('Malformed module name "%s" (expecting "@modulename/file_name").', $fileName));
            }

            $moduleName = substr($fileName, 1, $pos - 1);
            $fileName = substr($fileName, $pos + 1);
        }
        if (empty($moduleName)) {
            return null;
        }
        if ($moduleName=='arhframe') {
            return '..'. ARHFRAME_DIRECTORY;
        }
        if (!$this->isModuleExist($moduleName)) {
            throw new ArhframeException('Module "'. $moduleName .'" doesn\'t exist.');
        }
        if ($this->verifyFile($fileName,  $moduleName)) {
            return $moduleName;
        }
        throw new ArhframeException("File ". $fileName ." not found in modules.");
    }
    public function getModuleFromFileName($fileName)
    {
        $force = $this->force($fileName);
        if (!empty($force)) {
            return $force;
        }
        if ($this->verifyFile($fileName,  $this->router->getModule())) {
            return $this->router->getModule();
        }
        if (empty($this->dependance['depend'])) {
            throw new ArhframeException("File ". $fileName ." not found in modules.");
        }
        foreach ($this->dependance['depend'] as $value) {
            if ($this->verifyFile($fileName,  $value)) {
                return $value;
            }
        }
        throw new ArhframeException("File ". $fileName ." not found in module");
    }
    public static function parseForce($fileName)
    {
    	
        if (isset($fileName[0]) && '@' == $fileName[0]) {
            if (false === $pos = strpos($fileName, '/')) {
                throw new ArhframeException(sprintf('Malformed module name "%s" (expecting "@modulename/file_name").', $fileName));
            }
            $moduleName = substr($fileName, 1, $pos - 1);
        }
        if ($moduleName=='arhframe') {
            $moduleName = '..'. ARHFRAME_DIRECTORY;
        }

        return $moduleName;
    }
    public static function getModuleDirectory($moduleName){
    	$forcedAt = false;
    	if (isset($moduleName[0]) && '@' != $moduleName[0]) {
    		$moduleName = '@'. $moduleName;
    		$forcedAt = true;
    	}
    	$moduleDirectory = MODULE_DIRECTORY;
    	if(DependanceManager::isModuleArhframe($moduleName)){
    		$moduleDirectory = MODULE_DIRECTORY_ARHFRAME;
    		$moduleName = DependanceManager::getModuleFromArhframe($moduleName);
    		$forcedAt = false;
    	}
    	if($forcedAt){
    		$moduleName = substr($moduleName, 1);
    	}
    	$finalModuleName = DependanceManager::parseForce($moduleName);
    	if(empty($finalModuleName)){
    		$finalModuleName = $moduleName;
    	}
    	return $moduleDirectory.'/'.$finalModuleName;
    }
    public static function parseForceFileName($fileName)
    {
        if (isset($fileName[0]) && '@' == $fileName[0]) {
            if (false === $pos = strpos($fileName, '/')) {
                throw new ArhframeException(sprintf('Malformed module name "%s" (expecting "@modulename/file_name").', $fileName));
            }

            $fileName = substr($fileName, $pos + 1);
        }

        return $fileName;
    }
    public function getDependance()
    {
        if(empty($this->dependance['depend'])){
            return array();
        }
        foreach ($this->dependance['depend'] as $key => $depend){
        	if($depend[0] == '@'){
        		$this->dependance['depend'][$key] = substr($depend, 1);
        	}
        }
        return $this->dependance['depend'];
    }
    public static function isModuleArhframe($modulename){
    	$modulename = substr($modulename, 0, 4);
    	return $modulename == '@af_';
    }
    public static function getModuleFromArhframe($modulename){
    	if(!DependanceManager::isModuleArhframe($modulename)){
    		return null;
    	}
    	return substr($modulename, 4);
    }
    public function getCurrentModule()
    {
        return $this->router->getModule();
    }
}
