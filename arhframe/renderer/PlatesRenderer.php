<?php

import('arhframe.renderer.AbstractRenderer');
import('arhframe.eden.eden');
/**
*
*/
class PlatesRenderer extends AbstractRenderer
{
    private $plate;
    private $html;
    private $loader;
    public function __construct()
    {
        parent::__construct();
        import('arhframe.renderer.filterToLoad.**');
        import('arhframe.renderer.functionToLoad.**');
        $this->setName('plates');

    }
    public function createRenderer($page, $array=null)
    {
        $this->pageName = $page;
        $extension = array('plates', 'plt', 'tplt');
        $this->page = $page;
        $this->array = $array;
        $pathinfo = pathinfo($page);
        $this->extensions = $extension;
        $forced = DependanceManager::parseForce($page);
        $page = DependanceManager::parseForceFileName($page);
        if (!empty($forced)) {
            $forced = '@'. $forced .'/';
        }
        $moduleName = $this->dependanceManager->getModuleFromFileName($forced .'view/'. $page);
        if ($moduleName != $this->dependanceManager->getCurrentModule()) {
            $this->page =  $moduleName .'::'. $page;
        } else {
            $this->page = $page;
        }
        $plateEngine = new \League\Plates\Engine(dirname(__FILE__).'/../..'. MODULE_DIRECTORY. '/'. $this->dependanceManager->getCurrentModule() .'/view');
        $plateEngine->setFileExtension(null);
        // Add any any additional folders
        $dependance = $this->dependanceManager->getDependance();
        if (!empty($dependance)) {
            foreach ($dependance as $value) {
                $plateEngine->addFolder($value, dirname(__FILE__).'/../..'. DependanceManager::getModuleDirectory($value) .'/view');
            }
        }
        $this->plate = new \League\Plates\Template($plateEngine);
    }
    public function getHtml()
    {
        if (empty($this->array)) {
            return $this->plate->render($this->page);
        } else {
            return $this->plate->render($this->page, $this->array);
        }
    }
    public function getPlate()
    {
        return $this->plate;
    }
    public function __destruct()
    {
    }
}
