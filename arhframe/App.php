<?php
import('arhframe.errorhandler.ErrorHandler');
import('arhframe.exception.*');
import('arhframe.file.*');
import('vendor.autoload');
import('arhframe.Config');

errorHandler();
import('arhframe.Router');
import('arhframe.DependanceManager');
import('arhframe.ResourcesManager');
import('arhframe.Controller');

import('arhframe.basefile.const');
import('arhframe.secure.Secure');
import('arhframe.BeanLoader');
import('arhframe.LoggerManager');

class App
{
    private $config;
    private $route;
    private $ioc;
    private $beanId;
    private $action;

    function __construct()
    {
        $this->config = Config::getInstance();
        $this->doFolderForModuleDirectory(MODULE_DIRECTORY);
        $this->unregister_GLOBALS();
        $this->compileAuto();
        $this->route = Router::getInstance();
        DependanceManager::getInstance();

        $this->ioc = BeanLoader::getInstance();
        $this->loadFirewall();
        if (!$this->route->isArhframeController()) {
            $this->loadFromRoute();
        } else {
            $this->loadFromArhframeController();
        }

        $this->ioc->loadContext();
        $this->secure();
        logger('Route')->addInfo($this->route->__toString());
    }

    public function loadFromArhframeController()
    {
        $yamlarh = new Yamlarh(__DIR__ . '/arhframeController.yml');
        $controllers = $yamlarh->parse();
        $controller = $this->route->getArhframeController();
        if (empty($controllers[$controller])) {
            throw new Exception("The arhframe controller '$controller' does't exist");
        }
        $this->beanId = $controllers[$controller];
        $beanValue = array('class' => $this->beanId, 'extend' => 'arhframe.controller');
        $this->ioc->addBean($this->beanId, $beanValue);
        $this->action = 'action';
    }

    public function doFolderForModuleDirectory($moduleDirectory)
    {
        ResourcesManager::doFolder(substr($moduleDirectory, 1) . '/' . Router::getInstance()->getModule() . '/resources');

        foreach (DependanceManager::getInstance()->getDependance() as $key => $module) {
            if (DependanceManager::isModuleArhframe('@' . $module)) {
                continue;
            }
            ResourcesManager::doFolder(substr($moduleDirectory, 1) . '/' . $module . '/resources');
        }
    }

    public function charset()
    {
        if (!empty($this->config->config->charset)) {
            header('Content-type: text/html; charset=' . $this->config->config->charset);
        } else {
            header('Content-type: text/html; charset=utf-8');
        }

    }

    public function compileAuto()
    {
        if (!$this->config->config->compileauto) {
            return;
        }
        include_once ROOT . '/arhframe/basefile/compile.php';
    }

    public function loadFirewall()
    {
        $firewall = $this->ioc->getBean('arhframe.firewall');
        $firewall->loadFirewall();
        $firewall->loadAccessControl();
    }

    public function loadFromRoute()
    {
        @include_once(ROOT . '/module/' . $this->route->getModule() . '/controller/' . ucfirst($this->route->getController()) . 'Controller.php');
        $beanId = substr(MODULE_DIRECTORY, 1) . '.' . $this->route->getModule() . '.controller.' . ucfirst($this->route->getController()) . 'Controller';
        $beanValue = array('class' => $beanId, 'extend' => 'arhframe.controller', 'namespace' => $this->route->getModule() . '\\controller');
        $this->ioc->addBean($beanId, $beanValue);
        $controller = $this->route->getModule() . '\\controller\\' . ucfirst($this->route->getController()) . 'Controller';
        $this->action = strtolower($this->route->getAction()) . 'Action';
        if (!class_exists($controller)) {
            throw new Exception('Can\'t find controller "' . ucfirst($this->route->getController())
                . '" for route: "' . $this->route->getNameRoute()
                . '" check your routes, controllers name or namespace (should be "' . $this->route->getModule() . '\\controller".)');
        }
        $this->beanId = $beanId;
    }

    public function secure()
    {
        import('arhframe.debug.VarDump');
        $secure = $this->ioc->getBean('arhframe.secure');
        if (!$secure->isTokenMethodValid()) {
            die('security token has expired');
        }
    }

    public function run()
    {
        $object = $this->ioc->getBean($this->beanId);
        if ($this->route->isArhframeController()) {
            if (!($object instanceof AbstractControllerArhframe)) {
                throw new Exception("Arhframe controller must extend AbstractControllerArhframe");
            }
            $object->setExtractData($this->route->getDataExtract());
        }
        $object->beforeStartController();
        $object->__before();
        $action = $this->action;
        $content = $object->$action();
        if (is_object($content) && $content instanceof Response) {
            $content = $content->getContent();
        } else if (!is_string($content) && $content !== null) {
            $content = print_r($content);
        }
        if ($content !== null) {
            echo $content;
        }

        $object->__after();
    }

    public function unregister_GLOBALS()
    {
        if (!ini_get('register_globals')) {
            return;
        }


        if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
            die('cant unregister globals');
        }


        $noUnset = array('GLOBALS', '_GET',
            '_POST', '_COOKIE',
            '_REQUEST', '_SERVER',
            '_ENV', '_FILES');

        $input = array_merge($_GET, $_POST,
            $_COOKIE, $_SERVER,
            $_ENV, $_FILES,
            isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());

        foreach ($input as $k => $v) {
            if (!in_array($k, $noUnset) && isset($GLOBALS[$k])) {
                unset($GLOBALS[$k]);
            }
        }
    }
}