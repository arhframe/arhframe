<?php

import('arhframe.eden.eden');
import('arhframe.Config');
import('arhframe.BeanLoader');

class SimpleDbManager
{
    private $driver;
    private $host;
    private $port=null;
    private $username;
    private $password=null;
    private $dbname;
    private $path;
    private $dirty=false;
    private $bdd;
    private $bdds;
    private static $_instance = null;
    private $debugBarManager;
    private $bddName;
    public function __construct()
    {
    }
    private function connect()
    {
        $bddName = $this->bddName;
        $bddInfo = Config::getInstance()->bdd->$bddName;
        if (is_array($bddInfo)) {
            $this->setDriver(strtolower($bddInfo['driver']));
            $this->setHost($bddInfo['host']);
            $this->setPort($bddInfo['port']);
            $this->setUsername($bddInfo['username']);
            $this->setPassword($bddInfo['password']);
            $this->setDbname($bddInfo['dbname']);
            $this->setPath($bddInfo['path']);
        } elseif (is_object($bddInfo)) {
            $this->setDriver(strtolower($bddInfo->driver));
            $this->setHost($bddInfo->host);
            $this->setPort($bddInfo->port);
            $this->setUsername($bddInfo->username);
            $this->setPassword($bddInfo->password);
            $this->setDbname($bddInfo->dbname);
            $this->setPath($bddInfo->path);
        } else {
            throw new ArhframeException("Not enough information about database.");

        }
    }
    private function getMysqlDb()
    {
        if (empty($this->host)) {
            throw new ArhframeException("Database host not specified for mysql.");
        }
        if (empty($this->dbname)) {
            throw new ArhframeException("Database name not specified for mysql.");
        }
        if (empty($this->username)) {
            throw new ArhframeException("Database username not specified for mysql.");
        }

        if ($this->dirty) {
            $this->bdd = eden('mysql', $this->host, $this->dbname, $this->username, $this->password, $this->port);
        }

        return $this->bdd;
    }
    private function getPostgreDb()
    {
        if (empty($this->host)) {
            throw new ArhframeException("Database host not specified for postgre.");
        }
        if (empty($this->dbname)) {
            throw new ArhframeException("Database name not specified for postgre.");
        }
        if (empty($this->username)) {
            throw new ArhframeException("Database username not specified for postgre.");
        }
        if ($this->dirty) {
            $this->bdd = eden('postgre', $this->host, $this->dbname, $this->username, $this->password, $this->port);
        }

        return $this->bdd;
    }
    private function getSqliteDb()
    {
        if (empty($this->path)) {
            throw new ArhframeException("Database path file not specified for sqlite.");
        }
        if ($this->dirty) {
            $this->bdd = eden('sqlite', $this->path);
        }

        return $this->bdd;
    }
    private function getFirstDbName()
    {
        $tabBddName = (array) Config::getInstance()->bdd;
        if (empty($tabBddName)) {
            return null;
        }
        foreach ($tabBddName as $name => $value) {
            return $name;
        }
    }
    public function getDb($bddName=null)
    {
        if (empty($bddName)) {
            $this->bddName = $this->getFirstDbName();
        } else {
            $this->bddName = $bddName;
        }
        $bdds = $this->bdds;
        if (!empty($bdds[$this->bddName])) {
            $this->bdd = $bdds[$this->bddName];

            return $this->bdd;
        }
        $this->connect();
        $db = null;
        if ($this->driver == 'pdo_mysql') {
            $db = $this->getMysqlDb();
        } elseif ($this->driver == 'pdo_pgsql') {
            $db = $this->getPostgreDb();
        } elseif ($this->driver == 'pdo_sqlite') {
            $db = $this->getSqliteDb();
        }
        if (!empty($db)) {
            $db = $this->debugBarManager->addPdoCollector($this->bdd->getConnection());
            $this->bdd->setConnection($db);
        } else {
            throw new ArhframeException("No database set in config file.");

        }
        $this->bdds[$this->bddName] = $this->bdd;

        return $this->bdd;
    }
    public function setDriver($driver)
    {
        if (empty($driver)) {
            return;
        }
        $driver = strtolower($driver);
        if ($driver != 'pdo_mysql' && $driver != 'pdo_pgsql' && $driver !='pdo_sqlite') {
            throw new ArhframeException($driver ." is not a valid database driver");
        }
        $this->driver = $driver;
        $this->dirty = true;
    }
    public function getDriver()
    {
        return $this->driver;
    }
    public function setHost($host)
    {
        $this->host = $host;
        $this->dirty = true;
    }
    public function getServer()
    {
        return $this->server;
    }
    public function setPort($port)
    {
        if (empty($port)) {
            $this->port = null;
            $this->dirty = true;

            return;
        }
        $this->port = $port;
        $this->dirty = true;
    }
    public function getPort()
    {
        return $this->port;
    }
    public function setUsername($username)
    {
        $this->username = $username;
        $this->dirty = true;
    }
    public function getUsername()
    {
        return $this->username;
    }
    public function setPassword($password)
    {
        if (empty($password)) {
            $this->password = null;
            $this->dirty = true;

            return;
        }
        $this->password = $password;
        $this->dirty = true;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function setDbname($dbname)
    {
        $this->dbname = $dbname;
        $this->dirty = true;
    }
    public function getDbname()
    {
        return $this->dbname;
    }
    public function setPath($path)
    {
        $path = trim($path);
        if (substr($path, 0, 1) != '/') {
            $path = '/';
        }
        $path = dirname(__FILE__) .'/..'.$path;
        $this->path = $path;
        $this->dirty = true;
    }
    public function getPath()
    {
        return $this->path;
    }
    /**
     * @Required
     */
    public function setDebugBarManager(DebugBarManager $debugBarManager)
    {
        $this->debugBarManager = $debugBarManager;
    }
    public function getDebugBarManager()
    {
        return $this->debugBarManager;
    }
}
