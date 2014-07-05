<?php
import('arhframe.renderer.AbstractRenderer');
import('vendor.twig.twig.lib.Twig.Autoloader');
/**
*
*/
class TwigRenderer extends AbstractRenderer
{
    private $twig;
    private $autoescape = null;
    public function __construct()
    {
        parent::__construct();
        $this->setName('twig');
    }
    public function setAutoescape($autoescape)
    {
        $this->autoescape= $autoescape;
    }
    public function createRenderer($page, $array=null)
    {
        Twig_Autoloader::register();
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

            $loader = new Twig_Loader_Filesystem(dirname(__FILE__).'/../..'. MODULE_DIRECTORY. '/'. $this->dependanceManager->getCurrentModule() .'/view');
            $dependance = $this->dependanceManager->getDependance();
            if (!empty($dependance)) {
                foreach ($dependance as $value) {
                    $loader->addPath(dirname(__FILE__).'/../..'. DependanceManager::getModuleDirectory($value) .'/view', $value);
                }
            }
        } elseif (in_array($pathinfo['extension'], $this->extensions)) {
            $loader = new Twig_Loader_Filesystem($this->folder);
        } else {
            $this->isFile = false;
            $loader = new Twig_Loader_String();
        }
        $optionTwig = array(
                'auto_reload' => true,
                'autoescape' => $this->autoescape
            );
        if ($this->cache->isCacheActive()) {
            $optionTwig['cache'] = $this->cache->getCacheFolder();
        }
        if (empty($this->twig)) {
            $this->twig = $this->getDebugBarManager()->addTwigCollector(new Twig_Environment($loader, $optionTwig));
        } else {
            $this->twig->setLoader($loader);
        }

        $this->loadFunctionTwig();
        $this->loadFilterTwig();

    }
    public function getHtml()
    {
        if (empty($this->array)) {
            return $this->twig->render($this->page);
        } else {
            return $this->twig->render($this->page, $this->array);
        }
    }
    private function loadFunctionTwig()
    {
        $folder = dirname(__FILE__).'/functionToLoad';
        foreach (scandir ($folder) as $key => $value) {
            $file = $folder.'/'.$value;
            $pathinfo = pathinfo($file);
            if (is_file($file) && $pathinfo['extension']=='php') {
                require_once $file;
                $function = new Twig_SimpleFunction($pathinfo['filename'], $pathinfo['filename'],array('is_safe' => array('html')));
                $this->twig->addFunction($function);
            }
        }
    }
    private function loadFilterTwig()
    {
        $folder = dirname(__FILE__).'/filterToLoad';
        foreach (scandir ($folder) as $key => $value) {
            $file = $folder.'/'.$value;
            $pathinfo = pathinfo($file);
            if (is_file($file) && $pathinfo['extension']=='php') {
                require_once $file;
                $function = new Twig_SimpleFilter($pathinfo['filename'], $pathinfo['filename']);
                $this->twig->addFilter($function);
            }
        }
    }
    public function setCollectorToDebugBar()
    {
    }
    public function isOutput()
    {
        return true;
    }
    public function __destruct()
    {
        
    }
}
