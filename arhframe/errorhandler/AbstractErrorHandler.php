<?php
package('arhframe.errorhandler');
/**
*
*/
abstract class AbstractErrorHandler
{
    protected $option;
    protected static $_instance = null;
    protected function __construct($option=null)
    {
        $this->option = $option;
    }
    public static function getInstance($options=null) {
 
       if(is_null(self::$_instance)) {
         self::$_instance = new Singleton();  
       }
     $instance = self::$_instance;
     $instance->setOption($options);
       return self::$_instance;
   }
   public function setOption($options = null){
   	$this->option = $options;
   }
    abstract public function register();
    abstract public function unregister();
}
