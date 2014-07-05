<?php
package('arhframe.errorhandler');
/**
*
*/
abstract class AbstractErrorHandler
{
    protected $option;
    public function __construct($option=null)
    {
        $this->option = $option;
    }
    abstract public function register();

}
