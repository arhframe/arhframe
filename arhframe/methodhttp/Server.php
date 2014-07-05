<?php
import('arhframe.methodhttp.MethodHttp');
/**
*
*/
class Server extends MethodHttp
{

    public function __construct()
    {
        parent::__construct($_SERVER);
    }
}
