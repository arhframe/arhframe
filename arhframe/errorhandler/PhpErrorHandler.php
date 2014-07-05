<?php
package('arhframe.errorhandler');
import('arhframe.phperror.*');
/**
*
*/
class PhpErrorHandler extends AbstractErrorHandler
{

    public function __construct($option=null)
    {
        parent::__construct($option);
    }
    public function register()
    {
        require( dirname(__FILE__).'/../phperror/php_error.php');
        \php_error\reportErrors(array(
            'error_reporting_on' => E_ALL & ~E_NOTICE
        ));
    }
}
