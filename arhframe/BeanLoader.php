<?php

import('arhframe.ioc.IocArt');
import('arhframe.Config');
import('arhframe.cache.CacheManager');
import('arhframe.eden.eden');
/**
*
*/

/**
 * Class for manipulate bean from iocart and load the context in iocart
 * @author Arthur Halet <arthurh.halet@gmail.com>
 */
class BeanLoader
{
    private $ioc;
    private $iocFileInit = '/arhframe/ioc/context/main-context.yml';
    private static $_instance = null;
    private $cacheIsUsed = false;
    private function __construct()
    {
        $this->ioc = new IocArt($this->iocFileInit);
        $this->loadContextFromMods();
        $this->loadContext();
    }
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new BeanLoader();
        }

        return self::$_instance;
    }
    public function loadContextFromMods()
    {
        $folders = (new Folder(__DIR__ .'/..'. MODSARH_DIRECTORY))->getFolders();
        if (empty($folders)) {
            return;
        }
        foreach ($folders as $folder) {
            $file = new File($folder->absolute() .'/iocart.yml');
            if ($file->isFile()) {
                $this->ioc->importYml(MODSARH_DIRECTORY .'/'. $folder->getName() .'/iocart.yml');
            }
        }
    }
    public function addBean($beanId, $beanValue)
    {
        $this->ioc->addBean($beanId, $beanValue);

        return $this;
    }
    public function removeBean($beanId)
    {
        $this->ioc->removeBean($beanId);

        return $this;
    }
    public function getBean($beanId)
    {
        return $this->ioc->getBean($beanId);
    }
    public function loadContext()
    {
        $this->ioc->loadContext();

        return $this;
    }
}
