<?php
package('arhframe.errorhandler');
/**
*
*/
class WhoopsErrorHandler extends AbstractErrorHandler
{

    protected function __construct($option=null)
    {
        parent::__construct($option);
    }
    public static function getInstance($options=null) {
 
     if(is_null(self::$_instance)) {
       self::$_instance = new WhoopsErrorHandler($options);  
     }
     $instance = self::$_instance;
     $instance->setOption($options);
     return self::$_instance;
   }
    public function register()
    {
        $whoops = new Whoops\Run();

        // Configure the PrettyPageHandler:
        $errorPage = new Whoops\Handler\PrettyPageHandler();

        $errorPage->setPageTitle($this->option->title); // Set the page's title
        $errorPage->setEditor($this->option->editor);         // Set the editor used for the "Open" link

        $whoops->pushHandler($errorPage);
        $whoops->register();
    }
    public function unregister(){

    }
}
