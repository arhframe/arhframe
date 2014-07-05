<?php
import('arhframe.yamlarh.Yamlarh');
import('arhframe.basefile.simpleconst');
Class Config{
    private static $_instance = null;
    private $configFile = 'config';
    private $configUnload;
    private $configParam;
    private $dependanceManager;
    private function __construct($config='config')
    {
        $yamlarh = new Yamlarh($this->configFile);
        $this->configUnload = $yamlarh->parse();
        $this->configParam = $this->transformConfig($this->configUnload);
    }
    public static function getInstance($config='config', $array=false)
    {
        if (empty($config)) {
            $config = 'config';
        }
        if (is_null(self::$_instance)) {
        self::$_instance = new Config($config);
        }
        if ($array) {
            return self::$_instance->configUnload;
        }

        return self::$_instance->configParam;
    }
    public static function loadDependance($array)
    {
        if (is_null(self::$_instance)) {
            return;
        }
        $instance = self::$_instance;
        $instance->setConfigUnload($array);
        $instance->setConfigParam($instance->transformConfig($instance->getConfigUnload()));
    }
    public function setConfigParam($configParam)
    {
        $this->configParam = $configParam;
    }
    public function setConfigUnload($array)
    {
        $this->configUnload = $array;
    }
    public function getConfigUnload()
    {
        return $this->configUnload;
    }
    public function transformConfig($array)
    {
        $object = new stdClass;
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $object->$key = $this->transformConfig($value);
            } else {
                $object->$key = $value;
            }
        }

        return $object;
    }
}
