<?php
import('arhframe.eden.eden');
import('arhframe.mobiledetect.Mobile_Detect');
/**
*
*/
class DeviceManager extends Mobile_Detect
{
    private $width = 0;
    private $height=0;
    private $detect;
    private $init = false;
    private static $_instance = null; 
    public function __construct(){
        
    }
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
        self::$_instance = new DeviceManager();
        }

        return self::$_instance;
    }
    public function getWidth()
    {
        return $this->width;
    }
    public function getHeight()
    {
        return $this->height;
    }
    public function init(){
        if($this->init){
            return;
        }
        parent::__construct();
        if ($this->isMobile()) {
            $this->width = 480;
        }
        if ($this->isTablet()) {
            $this->width = 1024;
        }
        $this->init = true;
    }
}
