<?php
namespace umtools\controller;
class UmtoolsController extends \Controller{
    function indexAction(){
        return $this->render("layout.twig");
    }
}
?>
