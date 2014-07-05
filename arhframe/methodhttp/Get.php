<?php
import('arhframe.methodhttp.MethodHttp');
/**
*
*/
class Get extends MethodHttp
{

    public function __construct()
    {
        parent::__construct($_GET);
    }
}
