<?php
import('arhframe.Request');
import('arhframe.Config');
/**
*
*/
function Secure()
{
    return Secure::getInstance();
}
class Secure
{
    private $request;
    private $secureConfig;
    private $tokenKey = 'token';
    private static $_instance = null;
    public function __construct()
    {
        $this->secureConfig = Config::getInstance();
        $this->secureConfig = $this->secureConfig->secure;
        if (!empty($this->secureConfig->tokenKey)) {
            $this->tokenKey = $this->secureConfig->tokenKey;
        }
        if (is_null($_instance)) {
            $_instance = $this;
        }
    }
    public static function getInstance($config=null)
    {
        if (is_null(self::$_instance)) {
        self::$_instance = new Secure();
        }
        $instance = self::$_instance;
        $instance->setRequest(new Request());

        return self::$_instance;
    }
    private function createToken()
    {
        $this->request->getSession()->start();
        $token = crypt(uniqid('', true), $this->secureConfig->passphrase);
        $token .= crypt(uniqid('', true), $this->secureConfig->passphrase);
        $token = str_shuffle($token.time());
        $this->request->getSession()->set($this->tokenKey, $token);
    }
    public function getToken()
    {
        $this->request->getSession()->start();
        $tokenSession = $this->request->getSession()->get($this->tokenKey);
        if (!empty($tokenSession)) {
            return $tokenSession;
        } else {
            $this->createToken();
        }

        return $this->token;
    }
    public function regenToken()
    {
        $this->createToken();

        return $this;
    }
    public function getTokenKey()
    {
        return $this->tokenKey;
    }
    public function isTokenMethodValid()
    {
        $isValid = true;
        $getToken = $this->request->getGet()->get($this->tokenKey);
        $postToken = $this->request->getPost()->get($this->tokenKey);
        if ((!empty($getToken) AND $getToken != $this->getToken()) 
            || (!empty($postToken) AND $postToken != $this->getToken())) {
            $isValid =false;
        }

        return $isValid;
    }
    /**
     * @Required
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
}
