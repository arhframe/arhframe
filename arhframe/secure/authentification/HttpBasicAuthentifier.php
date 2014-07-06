<?php
package('arhframe.secure.authentification');
import('arhframe.Request');
import('arhframe.exception.*');

/**
 *
 */
class HttpBasicAuthentifier implements IAuthentifier
{
    private $authentifier;
    private $request;
    private $realm;
    private $provider;
    private $encoder;
    private $user;

    function __construct()
    {

    }

    function authentificate()
    {
        $this->authentifier = false;
        if (empty($this->realm)) {
            throw new ArhframeException("No realm set");
        }

        $username = $this->request->getServerRequest('PHP_AUTH_USER');
        try {
            $this->user = $this->provider->getUser($username);
        } catch (ArhframeProviderMemoryException $e) {
            $this->user = null;
        }

        if (!empty($this->user) &&
            $this->user->getPassword() == $this->encoder->crypt($this->request->getServerRequest('PHP_AUTH_PW'))
        ) {
            $this->authentifier = true;
        } else {
            header('WWW-Authenticate: Basic realm="' . $this->realm . '"');
            header('HTTP/1.0 401 Unauthorized');

        }
    }

    function isAuthentifier()
    {
        return $this->authentifier;
    }

    /*
    @Required
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    public function setRealm($realm)
    {
        $this->realm = $realm;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $provider
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
    }


    public function setEncoder(Encoder $encoder)
    {
        $this->encoder = $encoder;
    }

    public function logOut()
    {
        $this->request->getServer()->remove('PHP_AUTH_USER');
        header('HTTP/1.0 401 Unauthorized');
    }
}