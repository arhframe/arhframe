<?php
package('arhframe.frameworkCss');
import('arhframe.eden.eden');
import('arhframe.ResourcesManager');
import('arhframe.less.lessc');
import('arhframe.basefile.const');
import('arhframe.Router');

/**
*
*/
abstract class FrameworkCssManager
{
    private $folderFw;
    private $folderCss;
    private $compiler;
    private $nameFw;
    private $resourcesManager;
    private $file = null;
    private $fileBase;
    private $cssFileName;
    private $moduleName;
    private $optimize = true;
    public function __construct($nameFw, CompilerFwCss $compiler, $fwFile, $moduleName=null, $folderFw=null, $folderCss=null)
    {
        $this->nameFw = $nameFw;
        if (empty($moduleName)) {
            $this->moduleName = $moduleName;
        } else {
            $this->moduleName = Router::getInstance()->getModule();
        }

        $router = Router::getInstance();
        $this->compiler = $compiler;
        if (empty($folderFw)) {
            $this->folderFw = new Folder(dirname(__FILE__).'/../..'.MODULE_DIRECTORY .'/'. $this->moduleName .'/resources/'. $this->nameFw);
        } else {
            $this->folderFw = new Folder($folderFw);
        }
        if (empty($folderCss)) {
            $folderCssFinal = dirname(__FILE__).'/../..'.MODULE_DIRECTORY .'/'. $this->moduleName .'/resources/css';
        } else {
            $folderCssFinal = $folderCss;
        }
        if (!is_dir($folderCssFinal)) {
            @mkdir($folderCssFinal, 0777, true);
        }
        $this->folderCss = new Folder($folderCssFinal);
        if (empty($folderFw)) {
            $this->resourcesManager = new ResourcesManager($fwFile);
            $this->file = $this->resourcesManager->getFile();
        } else {
            $this->file = new File($folderFw .'/'. $fwFile);
        }
        if (strtolower($this->file->getExtension()) != $this->nameFw) {
            throw new ArhframeException($fwFile ." is not a ". $this->nameFw." file");
        }
        $this->fileBase = new File($fwFile);
        $this->cssFileName = $this->folderCss->absolute() . $this->fileBase->getFolder() ."/". $this->fileBase->getBase() .".css";
        $folder =  new Folder($this->folderCss->absolute() . $this->fileBase->getFolder());
        $folder->create();
    }
    public function compile()
    {
        if (empty($this->file)) {
            throw new ArhframeException("No ". $this->nameFw." file choose");
        }
        $this->compiler->compile($this->file->absolute(), $this->cssFileName);
    }
    public function getFile()
    {
        if (empty($this->file)) {
            throw new ArhframeException("No ". $this->nameFw." file choose");
        }
        $this->compile();

        return new File($this->cssFileName);
    }
    public function getFinalName($folder = null)
    {
        if (empty($folder)) {
            return MODULE_DIRECTORY .'/'. $this->moduleName .'/resources/css'. $this->fileBase->getFolder() .'/'. $this->fileBase->getBase() .'.css';
        } else {
            return $folder .'/'. $this->fileBase->getBase() .'.css';
        }
    }
    public function getHttpName($folder = null)
    {
        if (empty($this->file)) {
            throw new ArhframeException("No ". $this->nameFw." file choose");
        }
        $this->compile();
        $this->optimizeCss();

        return SERVERNAME.$this->getFinalName($folder);
    }
    public function setWithOptimize($optimize)
    {
        $this->optimize = (boolean) $optimize;
    }
    public function optimizeCss()
    {
        if (!$this->optimize) {
            return ;
        }
        $optimizerCss = new OptimizerCss($this->getFinalName(), new ResourcesManager());
        $optimizerCss->optimizeCss();
    }
    public static function compileAll(CompilerFwCss $compiler, $folder=null)
    {
        $router = Router::getInstance();
        $folderFw = new Folder(dirname(__FILE__).'/../..'.MODULE_DIRECTORY .'/'. $router->getModule() .'/resources/'. $compiler->getName() . $folder);
        $folderCss = new Folder(dirname(__FILE__).'/../..'.MODULE_DIRECTORY .'/'. $router->getModule() .'/resources/css'. $folder);
        $folderCss->create();
        $files = $folderFw->getFiles('/\.'. $compiler->getName() .'$/', true);
        $folders = $folderFw->getFolders();
        foreach ($files as $key => $value) {
            $compiler->compile($value->absolute(), $folderCss->absolute() ."/". $value->getBase() .".css");
        }
        foreach ($folders as $key => $value) {
            compilerManager::compileAll($folder .'/'. $value->getName());
        }
    }
    public function __toString()
    {
        return $this->getHttpName();
    }
}
