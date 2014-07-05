<?php
use Symfony\Component\Yaml\Yaml;

import('arhframe.cache.CacheManager');
import('arhframe.eden.eden');
import('arhframe.Config');
import('arhframe.DeviceManager');
import('arhframe.DependanceManager');
import('arhframe.basefile.const');
define("ROUTEDIR", dirname(__FILE__) . '/../config/route');

/**
 * Class Router
 */
Class Router
{
    private static $_instance = null;
    private $routeModuleFile = 'route/routing.yml';
    private $routeRequest;
    private $info = array();
    public $route;
    private $controller;
    private $action;
    private $nameroute;
    private $pattern;
    private $type = 'Everything';
    private $config;
    private $deviceManager;
    private $module;
    private $cache;
    private $method;
    private $validation;
    private $static;
    private $redirect = null;

    /**
     *
     */
    private function __construct()
    {
        $this->cache = cache($this, false);
        $this->config = Config::getInstance();
        $this->config = $this->config->config;
        $this->deviceManager = DeviceManager::getInstance();
        $this->routeRequest = $_SERVER['PATH_INFO'];
        if (empty($this->routeRequest)) {
            $this->routeRequest = '/';
        }
        $route = $this->cache->get('route');
        if (empty($route)) {
            $this->loadRoute();
            $this->cache->set('route', $this->route);
        } else {
            $this->route = $route;
        }
        $this->getRoute();
    }

    /**
     * @param String null $folder
     */
    private function loadRoute($folder = null)
    {
        if (empty($folder)) {
            $folder = new Folder(ROUTEDIR);
            $this->route = array();
        }

        $files = $folder->getFiles('/\.yml$/', true);
        foreach ($files as $key => $value) {
            $this->parseFile($value);
        }
    }

    /**
     * @param File $file
     */
    private function parseFile($file)
    {
        $ymlParsed = Yaml::parse($file->getContent());
        foreach ($ymlParsed as $key => $value) {
            if ($key == "@import") {
                unset($ymlParsed[$key]);
                if (!is_array($value)) {
                    $this->getFromImport($value, $file);
                } else {
                    foreach ($value as $fileName) {
                        $this->getFromImport($fileName, $file);
                    }
                }

            }
        }
        $this->parseFromModuleDirectory(MODULE_DIRECTORY, $file, $ymlParsed);
        //$this->parseFromModuleDirectory(MODULE_DIRECTORY_ARHFRAME, $file, $ymlParsed);
    }

    /**
     * @param $moduleDirectory
     * @param $file
     * @param $ymlParsed
     */
    private function parseFromModuleDirectory($moduleDirectory, $file, $ymlParsed)
    {
        $pregModule = preg_match('#\/' . substr($moduleDirectory, 1) . '\/([^\/]*)\/#', $file->getFolder(), $matchesModule);
        if (!empty($matchesModule)) {
            $arrayFile = $file->getArray();
            $moduleName = $matchesModule[1];
            foreach ($ymlParsed as $key => $value) {
                $ymlParsed[$key]['module'] = $moduleName;
            }
        }
        $this->route = array_merge($this->route, $ymlParsed);
    }

    /**
     * @param $fileName
     * @param $file
     * @throws ArhframeException
     */
    private function getFromImport($fileName, $file)
    {
        if ($fileName[0] == '/') {
            $fileFinalName = dirname(__FILE__) . '/..' . $fileName;
        } else {
            $fileFinalName = $file->getFolder() . '/' . $fileName;
        }
        if (!is_file($fileFinalName) && $this->isRouteModuleExist($fileName)) {
            $fileFinalName = dirname(__FILE__) . '/..' . DependanceManager::getModuleDirectory($fileName) . '/' . $this->routeModuleFile;
        } elseif (!is_file($fileFinalName)) {
            throw new ArhframeException("The route file " . $file->absolute() . " can't found module or route file " . $fileName);
        }
        $this->parseFile(new File($fileFinalName));

    }

    /**
     * @param $moduleName
     * @return bool
     */
    public function isRouteModuleExist($moduleName)
    {

        return is_file(dirname(__FILE__) . '/..' . DependanceManager::getModuleDirectory($moduleName) . '/' . $this->routeModuleFile);
    }

    /**
     * @return null|Router
     */
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new Router();
        }

        return self::$_instance;
    }

    /**
     *
     */
    public function getRoute()
    {
        $maxPattern = 0;
        foreach ($this->route as $key => $value) {
            $pattern = preg_replace("#(\\\{\w*\\\})#", '(.*)', preg_quote($value['pattern']));
            $methodCorrect = true;
            $typeCorrect = true;
            if ($this->pattern == $value['pattern'] && empty($value['method'])) {
                $methodCorrect = ("GET" == strtoupper($_SERVER['REQUEST_METHOD']));
            } elseif (!empty($value['method'])) {
                $methodCorrect = (strtoupper($value['method']) == strtoupper($_SERVER['REQUEST_METHOD']));
            }
            if ($this->pattern == $value['pattern'] && empty($value['type'])) {
                $typeCorrect = true;
            } elseif (!empty($value['type'])) {
                if (strtolower($value['type']) == 'all') {
                    $typeCorrect = true;
                } else {
                    $is = 'is' . ucfirst($value['type']);
                    $this->deviceManager->init();
                    $typeCorrect = $this->deviceManager->$is();
                }

            }
            if (preg_match('#^' . $pattern . '?#', $this->routeRequest) && strlen($pattern) >= $maxPattern && $methodCorrect && $typeCorrect) {
                $this->validation = $value['validation'];
                preg_match_all('#^' . $pattern . '?#', $this->routeRequest, $tabInfo);
                preg_match_all("#\{\w*\}#", $value['pattern'], $tabNameInfo);
                foreach ($tabNameInfo[0] as $element => $nameInfo) {
                    $nameInfo = substr($nameInfo, 1, strlen($nameInfo) - 2);
                    $this->info[$nameInfo] = $tabInfo[$element + 1][0];
                }
                if (!$this->validateInfo()) {
                    continue;
                }
                $maxPattern = strlen($pattern);
                $this->controller = $value['controller'];
                $this->action = $value['action'];
                $this->nameroute = $key;
                $this->pattern = $value['pattern'];
                $this->type = $value['type'];
                $this->method = $value['method'];
                $this->module = $value['module'];
                $this->static = $value['static'];
                $this->redirect = $value['redirect'];

            }
        }
        if(!empty($this->redirect)){

            $this->redirect();
        }
    }
    private function redirect(){
        header('Location: '. $this->redirect);
        exit();
    }
    /**
     * @return bool
     */
    private function validateInfo()
    {
        $validation = $this->validation;
        $infos = $this->info;
        if (empty($validation) || empty($infos) || !is_array($validation) || !is_array($infos)) {
            return true;
        }
        foreach ($infos as $name => $value) {
            if (empty($validation[$name])) {
                continue;
            }
            $preg = preg_match('#' . $validation[$name] . '#', $value);
            if (empty($preg)) {
                $this->info = null;
                $this->validation = null;
                return false;
            }
        }
        return true;

    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @return mixed
     */
    public function getNameRoute()
    {
        return $this->nameroute;
    }

    /**
     * @return mixed
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return bool
     */
    public function isStatic()
    {
        return (!empty($this->static) && empty($this->config->devmode));
    }

    /**
     * @return bool
     */
    public static function isRewrite()
    {
        if (stristr($_SERVER['REQUEST_URI'], Config::getInstance()->config->pagerouter) === false) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public static function getCurrentRoute()
    {
        if (Router::isRewrite()) {
            return SERVERNAME . $_SERVER['PATH_INFO'];
        }

        return SERVERNAME . '/' . Config::getInstance()->config->pagerouter . $_SERVER['PATH_INFO'];

    }

    /**
     * @param $pattern
     * @return string
     */
    public static function writeRoute($pattern)
    {
        if (Router::isRewrite()) {
            return SERVERNAME . $pattern;
        }

        return SERVERNAME . '/' . Config::getInstance()->config->pagerouter . '/' . $pattern;

    }

    /**
     * @param $name
     * @return mixed
     * @throws ArhframeException
     */
    public function getRouteByName($name)
    {
        $args = func_get_args();
        $route = $this->route;
        if (empty($route[$name])) {
            throw new ArhframeException('The route "' . $name . '" doesn\'t exist.');

        }
        $pattern = $route[$name]['pattern'];
        preg_match_all("#\{\w*\}#", $pattern, $tabNameInfo);
        if (count($tabNameInfo[0]) > func_num_args() - 1) {
            $nbValue = count($tabNameInfo[0]) - func_num_args() + 1;
            throw new ArhframeException("You need to follow the pattern \"" . $pattern . "\" miss " . $nbValue . " value.");
        }
        for ($i = 0; $i < count($tabNameInfo[0]); $i++) {
            $pattern = preg_replace("#\{\w*\}#", $args[$i + 1], $pattern, 1);
        }

        return $pattern;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "Route path: " . $this->getCurrentRoute()
        . "\nRoute name: " . $this->getNameRoute()
        . "\nRoute controller: " . $this->getController()
        . "\nRoute module: " . $this->getController()
        . "\nRoute action: " . $this->getAction()
        . "\nRoute method: " . $this->getMethod()
        . "\nRoute is static: " . ($this->static ? 'true' : 'false')
        . "\nDevice: " . $this->type;
    }
}
