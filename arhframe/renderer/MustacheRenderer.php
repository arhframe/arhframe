<?php
import('arhframe.renderer.AbstractRenderer');
import('arhframe.Config');
import('vendor.mustache.mustache.src.Mustache.Autoloader');
/**
*
*/
class MustacheRenderer extends AbstractRenderer
{
    private $mustache;
    public function __construct()
    {
        parent::__construct();
        $this->setName('mustache');
    }
    public function createRenderer($page, $array=null)
    {
        Mustache_Autoloader::register();
        $pathinfo = pathinfo($page);

        $this->array = $array;
        $this->page = $page;
        $this->pageName = $page;
        if (in_array($pathinfo['extension'], $this->extensions) && empty($this->folder)) {
            $forced = DependanceManager::parseForce($page);
            $page = DependanceManager::parseForceFileName($page);
            if (!empty($forced)) {
                $forced = '@'. $forced .'/';
            }
            $moduleName = $this->dependanceManager->getModuleFromFileName($forced .'view/'. $page);
            if ($moduleName != $this->dependanceManager->getCurrentModule()) {
                $this->page = '@'. $moduleName .'/'. $page;
            } else {
                $this->page = $page;
            }
            $loaders = array();
            $loaders[] = new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../..'. MODULE_DIRECTORY. '/'. $this->dependanceManager->getCurrentModule() .'/view');
            $dependance = $this->dependanceManager->getDependance();
            if (!empty($dependance)) {
                foreach ($dependance as $value) {
                    $loaders[] = new Mustache_Loader_FilesystemLoader(dirname(__FILE__).'/../..'. DependanceManager::getModuleDirectory($value) .'/view', $value);
                }
            }
            $loader = new Mustache_Loader_CascadingLoader($loaders);
        } elseif (in_array($pathinfo['extension'], $this->extensions)) {
            $loader = new Mustache_Loader_FilesystemLoader($this->folder);
        } else {
            $this->isFile = false;
            $loader = new Mustache_Loader_StringLoader();
        }

        $optionMustache = array();
        $config = Config::getInstance();
        if(!empty($config->config->charset)){
            $optionMustache['charset'] = $config->config->charset;
        }else{
            $optionMustache['charset'] = 'utf-8';
        }
        if ($this->cache->isCacheActive()) {
            $optionMustache['cache'] = $this->cache->getCacheFolder();
        }
        $optionMustache['loader'] = $loader;
        $optionMustache['helpers']= $this->getHelpers();
        if(!empty($this->mustache)){
            unset($this->mustache);
        }
        $this->mustache = new Mustache_Engine($optionMustache);

    }
    public function getHtml()
    {
        $file = new File($this->page);
        if (empty($this->array)) {
            return $this->mustache->load($file->getBase());
        } else {
            $tpl = $this->mustache->loadTemplate($file->getBase());
            return $tpl->render($this->array);
        }
    }
    private function getHelpers(){
        import('arhframe.renderer.filterToLoad.**');
        import('arhframe.renderer.functionToLoad.**');
        $helpers = array();
        $folderFunction = new Folder(dirname(__FILE__).'/functionToLoad');
        $files = $folderFunction->getFiles('/\.php$/', true);
        foreach ($files as $file) {
            $function = $file->getBase();
            $helpers[$function] = $function;
        }
        $folderFunction = new Folder(dirname(__FILE__).'/filterToLoad');
        $files = $folderFunction->getFiles('/\.php$/', true);
        foreach ($files as $file) {
            $function = $file->getBase();
            $helpers[$function] = $function;
        }
        return $helpers;
    }
    public function isOutput()
    {
        return true;
    }
    public function __destruct()
    {
      
    }
}
