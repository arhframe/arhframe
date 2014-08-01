<?php
package('arhframe.errorhandler');
import('arhframe.phperror.*');
/**
*
*/
class PhpErrorHandler extends AbstractErrorHandler
{
    private $phpError;
    protected function __construct($option=null)
    {
        parent::__construct($option);
    }
    public function register()
    {
        require( dirname(__FILE__).'/../phperror/php_error.php');
        $this->phpError = \php_error\reportErrors(array(
            'error_reporting_on' => E_ALL & ~E_NOTICE
        ));
    }
    public static function getInstance($options=null) {
 
     if(is_null(self::$_instance)) {
       self::$_instance = new PhpErrorHandler($options);  
     }
     $instance = self::$_instance;
     $instance->setOption($options);
     return self::$_instance;
   }
    public function unregister(){
        if(empty($this->phpError)){
            return;
        }
        $this->phpError->turnOff();
    }
}
