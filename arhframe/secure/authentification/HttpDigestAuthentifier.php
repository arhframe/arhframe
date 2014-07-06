<?php
package('arhframe.secure.authentification');
import('arhframe.Request');
import('arhframe.exception.*');

/**
 *
 */
class HttpDigestAuthentifier implements IAuthentifier
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
        $data = $this->http_digest_parse($this->request->getServerRequest('PHP_AUTH_DIGEST'));
        try {
            $this->user = $this->provider->getUser($data['username']);
        } catch (ArhframeProviderMemoryException $e) {
            $this->user = null;
        }
        if (empty($this->user)) {
            $this->authentifier = false;
            return;
        }


        // Génération de réponse valide
        $A1 = md5($data['username'] . ':' . $this->realm . ':' . $this->user->getPassword());
        $A2 = md5($_SERVER['REQUEST_METHOD'] . ':' . $data['uri']);
        $valid_response = md5($A1 . ':' . $data['nonce'] . ':' . $data['nc'] . ':' . $data['cnonce'] . ':' . $data['qop'] . ':' . $A2);
        if (!empty($this->user) &&
            $valid_response == $data['response']
        ) {
            $this->authentifier = true;
        } else {
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="' . $this->realm .
                '",qop="auth",nonce="' . uniqid() . '",opaque="' . md5($this->realm) . '"');

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

    public function http_digest_parse($txt)
    {
        // protection contre les données manquantes
        $needed_parts = array('nonce' => 1, 'nc' => 1, 'cnonce' => 1, 'qop' => 1, 'username' => 1, 'uri' => 1, 'response' => 1);
        $data = array();
        $keys = implode('|', array_keys($needed_parts));

        preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            $data[$m[1]] = $m[3] ? $m[3] : $m[4];
            unset($needed_parts[$m[1]]);
        }

        return $needed_parts ? false : $data;
    }

    public function setEncoder(Encoder $encoder)
    {
        $this->encoder = $encoder;
    }

    public function logOut()
    {
        $this->request->getServer()->set('PHP_AUTH_DIGEST', '');
        header('HTTP/1.1 401 Unauthorized');

    }
}