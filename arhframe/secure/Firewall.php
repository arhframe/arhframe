<?php
package('arhframe.secure');
import('arhframe.providers.user.*');
import('arhframe.module.ArhframeUser.entities.*');

/**
 *
 */
class Firewall
{
    private $firewalls;
    private $choice;
    private $providers;
    private $encoder;
    private $provider;
    private $authentifiers;
    private $authentifier;
    private $request;
    private $roleAnonymous = 'ROLE_USER';
    private $sessionName = 'afUser';
    private $msgNeedLogged = 'You need to be logged.';
    private $msgWrongAuth = 'you can\'t access to this page.';
    private $isLogout = false;

    function __construct()
    {

    }

    public function chooseFirewalls()
    {
        $pathInfo = $this->request->getServerRequest('PATH_INFO');
        if (empty($pathInfo)) {
            $pathInfo = '/';
        }
        $logouts = array();
        foreach ($this->firewalls['firewalls'] as $firewallName => $firewall) {
            if (preg_match('#' . $firewall['pattern'] . '#', $pathInfo)) {
                $this->choice = $firewall;
            }
            if (!empty($firewall['logout'])) {
                $logouts[$firewallName] = $firewall['logout'];
            }
        }
        if (in_array($pathInfo, $logouts)) {
            $logouts = array_flip($logouts);
            $this->logout($this->firewalls['firewalls'][$logouts[$pathInfo]]);
        }
    }

    public function logout($firewall)
    {
        if (!empty($firewall['httpBasic'])) {
            $this->authentifier = $this->authentifiers['httpBasic'];

        } else if (!empty($firewall['httpDigest'])) {
            $this->authentifier = $this->authentifiers['httpDigest'];

        }
        if (empty($this->authentifier)) {
            return;
        }

        $this->authentifier->logOut();
        $this->isLogout = true;
        $this->request->getSession()->remove($this->sessionName);

    }

    /**
     * @return mixed
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * @param mixed $providers
     * @Required
     */
    public function setProviders($providers)
    {
        $this->providers = $providers;
    }

    public function loadAccessControl()
    {
        $rolesControl = array();
        $ipControl = array();
        $pathInfo = $this->request->getServerRequest('PATH_INFO');
        if (empty($pathInfo)) {
            $pathInfo = '/';
        }
        foreach ($this->firewalls['access_control'] as $accessControl) {
            if (!preg_match('#' . $accessControl['path'] . '#', $pathInfo)) {
                continue;
            }
            if (!is_array($accessControl['role'])) {
                $rolesControl[] = $accessControl['role'];
            } else {
                $rolesControl = array_merge($rolesControl, $accessControl['role']);
            }
            if (!is_array($accessControl['ip'])) {
                $ipControl[] = $accessControl['ip'];
            } else {
                $ipControl = array_merge($ipControl, $accessControl['ip']);
            }
        }
        if (empty($rolesControl) && empty($ipControl)) {
            return;
        }
        if (!$this->isSessionActive()) {
            header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbiden");
            exit($this->msgNeedLogged);
        }
        $user = $this->request->getSession()->get($this->sessionName);
        $canAccess = true;

        foreach ($rolesControl as $role) {
            $roleObject = new \ArhframeUser\entities\Role();
            $roleObject->setRole($role);
            $roleObject->setName($role);
            if (!$user->hasRole($roleObject)) {
                $canAccess = false;
                break;
            }
        }
        if (!$canAccess) {
            header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbiden");
            exit($this->msgWrongAuth);
        }
        if (empty($ipControl) || empty($ipControl[0])) {
            return;
        }
        $m6Firewall = new \M6Web\Component\Firewall\Firewall();
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        $connAllowed = $m6Firewall
            ->setDefaultState(false)
            ->addList($ipControl, 'local', true)
            ->setIpAddress($ip)
            ->handle();

        if (!$connAllowed) {
            header($_SERVER["SERVER_PROTOCOL"] . " 403 Forbiden");
            exit($this->msgWrongAuth . '. Wrong ip: ' . $ip);
        }
    }

    public function loadFirewall()
    {

        $this->chooseFirewalls();
        if ($this->isSessionActive() || $this->isLogout || empty($this->choice)) {
            return;
        }

        $pathInfo = $this->request->getServerRequest('PATH_INFO');

        if ($this->choice['logout'] == $pathInfo &&
            !empty($pathInfo)
        ) {
            return;
        }

        $this->loadEncoder();
        $provider = $this->firewalls['providers'][$this->choice['provider']];

        $this->loadMemoryProvider($provider);
        if (!empty($this->choice['httpBasic'])) {
            $this->authentifier = $this->authentifiers['httpBasic'];
            $this->authentifier->setRealm($this->choice['httpBasic']['realm']);
        } else if (!empty($this->choice['httpDigest'])) {
            $this->authentifier = $this->authentifiers['httpDigest'];
            $this->authentifier->setRealm($this->choice['httpDigest']['realm']);
        }
        $this->authentifier->setProvider($this->provider);
        $this->authentifier->setEncoder($this->encoder);
        $this->authentifier->authentificate();
        if ($this->authentifier->isAuthentifier()) {

            $this->createSessionUser($this->authentifier->getUser());
            return;
        }

        if ($this->choice['anonymous']) {
            $this->createSessionUser($this->createAnonymous());
            return;
        }
        $this->createViewNotLogged();
    }

    private function createViewNotLogged()
    {
        exit($this->msgNeedLogged);
    }

    private function isSessionActive()
    {
        $user = $this->request->getSession()->get($this->sessionName);
        if (empty($user)) {
            return false;
        }
        return true;
    }

    private function createSessionUser($user)
    {
        $this->request->getSession()->set($this->sessionName, $user);
    }

    private function createAnonymous()
    {
        $user = new \ArhframeUser\entities\User();
        $role = new \ArhframeUser\entities\Role();
        $role->setName($this->roleAnonymous);
        $role->setRole($this->roleAnonymous);
        $user->setUsername('anonymous');
        $user->addRole($role);
        return $user;
    }

    private function loadMemoryProvider($provider)
    {
        if (empty($provider['memory'])) {
            return;
        }
        $this->provider = $this->providers['memory'];
        $this->provider->setUsers($provider['memory']['users']);
        $this->provider->setRoles($this->firewalls['role_hierarchy']);
        $this->provider->setEncoder($this->encoder);
        $this->provider->loadUsers();
    }

    private function loadEncoder()
    {
        $this->encoder->setEncoders($this->firewalls['encoders']);
        if (!empty($this->firewalls['salt'])) {
            $this->encoder->setSalt($this->firewalls['salt']);
        }
    }

    /**
     * @return mixed
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * @param mixed $encoder
     * @Required
     */
    public function setEncoder(Encoder $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @return mixed
     */
    public function getAuthentifiers()
    {
        return $this->authentifiers;
    }

    /**
     * @param mixed $authentifier
     * @Required
     */
    public function setAuthentifiers($authentifiers)
    {
        $this->authentifiers = $authentifiers;
    }

    /*
    @Required
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->request->getSession()->start();
    }

    /*
     * @Required
     */
    public function setFirewalls(array $firewalls)
    {
        $this->firewalls = $firewalls['securityuser'];
    }

    public function getLogout()
    {
        return $this->choice['logout'];
    }
}