<?php
namespace test\controller;
use ArhframeUser\entities\User;
use ArhframeUser\entities\Role;
class TestController extends \Controller{
    function indexAction(){
//     	$em = $this->getEntityManager();
//     	$user = new User();
//     	$role = new Role();
//     	$role->setName('Admin');
//     	$role->setRole('ADMIN');
//     	$user->setUsername('arthur');
//     	$user->addRole($role);
//     	$user->setPassword('test');
//     	$user->setEmail("test@test.fr");
//     	$em->persist($role);
//     	$em->persist($user);
//         $em->flush();

        $response =  $this->render("@af_ArhframeUser/Security/login.html.twig", array('a_variable' => 'ok ça marche '. $this->getInfoRequest('slug'), 'image'=>'atos.jpg'));
    	return $response;
    }
    function indexPostAction(){
        $this->renderJson(array('a_variable' => 'ok ça marche post'));
    }
    function indexIPhoneAction(){
        $this->render("jojo.twig", array('a_variable' => 'ok ça marche iphone'));
    }
}
?>
