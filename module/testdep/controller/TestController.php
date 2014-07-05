<?php

namespace testdep\controller;
class TestController extends \Controller{
    function indexAction(){
        $this->render("jojo.twig", array('a_variable' => 'ok ça marche '. $this->getInfoRequest('slug'), 'image'=>'atos.jpg'));
    }
    function indexPostAction(){
        $this->render("jojo.twig", array('a_variable' => 'ok ça marche post'));
    }
    function indexIPhoneAction(){
        $this->render("jojo.twig", array('a_variable' => 'ok ça marche iphone'));
    }
}
?>
