<?php
package('arhframe.errorhandler');

/**
*
*/
function errorHandler($debug=null){
    new ErrorHandler($debug);
}
class ErrorHandler
{

    public function __construct($debug=null)
    {
    	$config = null;
    	if(empty($debug)){
    		$config = Config::getInstance();
    		if (!$config->config->debug && !$config->config->devmode) {
    			if (!$config->config->debug) {
    				error_reporting(0);
    			}
    		
    			return;
    		}
    	}
        
        if (empty($config->debugguer->handler)) {
            $handler = $this->getHandler("php", $config->debugguer);
        } else {
            $handler = $this->getHandler($config->debugguer->handler, $config->debugguer);
        }
        $handler->register();
    }
    public function getHandler($handler=null, $option=null)
    {
        $renderer = null;
        if (!empty($handler)) {
            $renderer = ucfirst($handler).'ErrorHandler';
        }
        if (!empty($renderer) && class_exists($renderer)) {
            return new $renderer($option);
        } else {
            throw new ArhframeException("Error handler '". $handler ."' does not exist.");

        }
    }
}
