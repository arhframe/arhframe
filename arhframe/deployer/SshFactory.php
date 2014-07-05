<?php
import('arhframe.exception.*');

/**
 *
 */
class SshFactory
{
    private $host;
    private $password;
    private $user;
    private $key;
    private $ssh;

    function __construct($config)
    {
        if (empty($config['host'])) {
            throw new ArhframeException("Host must be set");
        }
        if (empty($config['username'])) {
            throw new ArhframeException("Username must be set");
        }
        if (empty($config['port'])) {
            $config['port'] = 22;
        }
        $port = (int)$config['port'];
        if (empty($config['timeout'])) {
            $config['timeout'] = 10;
        }
        $timeout = (int)$config['timeout'];
        $this->ssh = new Net_SSH2($config['host'], $port, $timeout);
        $this->host = $config['host'];
        $this->password = $config['password'];
        $this->user = $config['username'];
        $this->key = $config['privateKey'];
    }

    private function createConnectionRsa()
    {
        $key = new Crypt_RSA();
        if (!empty($this->password)) {
            $key->setPassword($this->password);
        }
        if (is_file($this->key)) {
            $file = new File($this->key);
            $this->key = $file->getContent();
        }
        $key->loadKey($this->key);
        if (!$this->ssh->login($this->user, $key)) {
            throw new ArhframeException("Login Failed");
        }
    }

    private function createConnectionPassword()
    {
        if (!$this->ssh->login($this->user, $this->password)) {
            throw new ArhframeException("Login Failed");
        }
    }

    public function getSsh()
    {
        if (!empty($this->key)) {
            $this->createConnectionRsa();
        } else {
            $this->createConnectionPassword();
        }
        return $this->ssh;
    }
}