<?php
import('arhframe.methodhttp.MethodHttp');
/**
*
*/
class Post extends MethodHttp
{

    public function __construct()
    {
        parent::__construct($_POST);
    }
}
