<?php
import('arhframe.Router');
import('arhframe.eden.eden');
import('arhframe.methodhttp.Post');
import('arhframe.methodhttp.Get');
import('arhframe.methodhttp.Server');
import('arhframe.secure.SecurityIssueIdentifier');

Class Request{
    private $request;
    private $router;
    private $session;
    private $cookie;
    private $post;
    private $get;
    private $server;
    private static $ISVERIFY = false;
    public function __construct()
    {
        $this->session = eden('session');
        $this->session->start();
        $this->post = new Post();
        $this->get = new Get();
        $this->server = new Server();
        $this->cookie = eden('cookie');
        $this->router = Router::getInstance();
    }
    public function verifySecurity()
    {
        if (Request::$ISVERIFY) {
            return;
        }
        $security = SecurityIssueIdentifier::getInstance();
        $security->identify($this->getArrayFromArrayAccess($this->post), 'POST');
        $security->identify($this->getArrayFromArrayAccess($this->get), 'GET');
        $security->identify($this->getArrayFromArrayAccess($this->cookie), 'COOKIE');
        $security->identify($this->getArrayFromArrayAccess($this->session), 'SESSION');
        $security->identify($this->router->getInfo(), 'ROUTER_INFO');
        Request::$ISVERIFY = true;
    }
    private function getArrayFromArrayAccess($data)
    {
        $array = array();
        foreach ($data as $key => $value) {
            $array[$key] = $value;
        }

        return $array;
    }
    public function getGetRequest($var = null)
    {
        if (empty($var)) {
            return $this->get;
        }

        return $this->get->get($var);
    }
    public function getPostRequest($var=null)
    {
        if (empty($var)) {
            return $this->getPost();
        }

        return $this->post->get($var);
    }
    public function getInfoRequest($var = null)
    {
        if (empty($var)) {
            return $this->router->getInfo();
        }
        $infos = $this->router->getInfo();

        return $infos[$var];
    }
    public function getServerRequest($var = null)
    {
        if (empty($var)) {
            return $this->server;
        }

        return $this->server->get($var);
    }
    public function getRouter()
    {
        return $this->router;
    }
    public function getSession()
    {
        return $this->session;
    }
    public function getCookie()
    {
        return $this->cookie;
    }
    public function getPost()
    {
        return $this->post;
    }
    public function getGet()
    {
        return $this->get;
    }
    public function getServer(){
        return $this->server;
    }
    public function getClientIp(){
        $httpClient = $this->getServerRequest('HTTP_CLIENT_IP');
        $httpX = $this->getServerRequest('HTTP_X_FORWARDED_FOR');
        if (!empty($httpClient)){
          $ip=$this->getServerRequest('HTTP_CLIENT_IP');
        }elseif (!empty($httpX)){
          $ip=$this->getServerRequest('HTTP_X_FORWARDED_FOR');
        }else{
          $ip=$this->getServerRequest('REMOTE_ADDR');
        }
        return $ip;
    }
}
