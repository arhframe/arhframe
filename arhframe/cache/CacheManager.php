<?php
package('arhframe.cache');
import('arhframe.eden.eden');
import('arhframe.Config');
import('arhframe.fct_recur');
import('arhframe.Router');

/**
 * Function to get/create/update easily your cache by prefix or/and route or/and module
 * Function to get/create/update easily your cache by prefix or/and route or/and module
 * @param mixed $prefix often an object or value name for id
 * @param boolean $route use route to create the cache key
 * @param boolean $module use module to create the cache key
 * @return CacheManager
 */
function cache($prefix, $route=true, $module=false)
{
    $cacheManager = CacheManager::getInstance();
    if (is_object($prefix)) {
        $prefix = get_class($prefix);
    } elseif (is_array($prefix)) {
        $prefix = serialize($prefix);
    }
    if ($route && $module) {
        $router = Router::getInstance();
        $prefix = $router->getModule() .'/'. $router->getNameRoute() .'.'. $prefix;
    } elseif ($module) {
        $router = Router::getInstance();
        $prefix = $router->getModule() .'/'. $prefix;
    } elseif ($route) {
        $router = Router::getInstance();
        $prefix = $router->getNameRoute() .'/'. $prefix;
    }
    $cacheManager->setPrefix($prefix);

    return $cacheManager;
}

/**
 * Clear the cache
 */
function clearCache()
{
    $cacheManager = CacheManager::getInstance();
    $cacheManager->clear();
}
/**
*
*/

/**
 * The cache manager
 * you can use:
 * - memcache
 * - file
 * - apc
 * - xcache
 */
class CacheManager
{
    private $cacheConfig = null;
    /*
        Cache type:
            -memcache
            -file
            -apc
            -xcache

     */
    private $typeCache = null;
    private $cache = null;
    private $folder = null;
    private $folderName = null;
    private $prefix = null;
    private $expiration = null;
    private static $_instance = null;


    /**
     * Constructor
     */
    private function __construct()
    {
        $this->cacheConfig = Config::getInstance(null, true);
        $this->cacheConfig = $this->cacheConfig['cache'];
        $type = trim($this->cacheConfig['type']);
        if (empty($this->cacheConfig['folder'])) {
            throw new ArhframeException("You must specified cache folder.");
        }
        $folder = dirname(__FILE__) .'/../..'. $this->cacheConfig['folder'];
        if (!is_dir($folder)) {
            throw new ArhframeException('Cache folder "'. $this->cacheConfig['folder'] .'" doesn\'t exist.');
        }
        $this->folder = new Folder($folder);
        if (empty($this->cacheConfig) || empty($type) || Config::getInstance()->config->devmode) {
            return;
        }
        if (!empty($this->cacheConfig['lifetime']) && (int) $this->cacheConfig['lifetime'] >0) {
            $this->expiration = (int) $this->cacheConfig['lifetime'];
        }
        
        $this->folderName = $this->cacheConfig['folder'];
        switch (strtolower(trim($this->cacheConfig['type']))) {
            case 'memcache':
            case 'memcached':
                $this->constructMemcache();
                break;
            case 'xcache':
                $this->cache = new XCache();
                $this->typeCache = 'xcache';
                break;
            case 'file':
                $this->constructFilecache();
                break;
            case 'apc':
                $this->cache = eden('apc');
                $this->typeCache = 'apc';
                break;
            default:
                throw new ArhframeException('Cache "'. $this->cacheConfig['type'] .'" not supported.');
        }
    }
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
        self::$_instance = new CacheManager();
        }

        return self::$_instance;
    }
    private function constructFilecache()
    {
        $this->cache = eden('cache', (string) $this->folder->absolute());
        $this->typeCache = 'file';
    }
    private function constructMemcache()
    {
        $servers = $this->cacheConfig['server'];
        if (empty($servers)) {
            throw new ArhframeException("You must specified one server for memcache at least.");
        }
        $currentServer = current($servers);
        $currentServerName = key($servers);
        if (empty($currentServer['port'])) {
            $port = '11211';
        } else {
            $port = (int) $currentServer['port'];
        }
        try {
            $this->verifyConnection($currentServerName, $port);
        } catch (Exception $e) {
            throw new ArhframeException($e->getMessage());
        }
        $this->cache = eden('memcache', $currentServerName, $port);
        $this->typeCache = "memcache";
        unset($servers[$currentServerName]);
        if (empty($servers)) {
            return;
        }
        foreach ($servers as $serverName => $server) {
            try {
                $this->verifyServerInfo($server, $serverName);
            } catch (Exception $e) {
                throw new ArhframeException($e->getMessage());
            }
            $this->cache->addServer($serverName, (int) $server['port'], (boolean) $server['persistent'], (int) $server['cost']);
        }

    }
    private function verifyConnection($serverName, $port)
    {
        if (!isPortOpen($serverName, (int) $port)) {
            throw new ArhframeException("Can't connect to memcache server \"". $serverName .'".');
        }
    }
    private function verifyServerInfo($server, $serverName)
    {
        if (empty($server['port'])) {
            throw new ArhframeException("You must specified a port for memcache server \"". $serverName .'".');
        }
        if (empty($server['cost'])) {
            throw new ArhframeException('You must specified a cost for memcache server "'. $serverName .'".');
        }
        if (empty($server['persistent'])) {
            throw new ArhframeException('You must specified a persistent for memcache server "'. $serverName .'".');
        }
        try {
            $this->verifyConnection($serverName, $server['port']);
        } catch (Exception $e) {
            throw new ArhframeException($e->getMessage());
        }
    }
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }
    public function getPrefix()
    {
        return $this->prefix;
    }
    public function clear()
    {
        if ($this->typeCache != 'file' && !empty($this->typeCache)) {
            $this->cache->clear();
        }
        $this->folder->clear();
    }
    private function getKeyFormat($key)
    {
        return $this->prefix .'/'. $key;
    }
    private function getKeyFormatFile($key)
    {
        $key = $this->prefix .'/'. $key;
        $explodeKey = explode('/', $key);
        if (empty($explodeKey)) {
            return $key;
        }
        foreach ($explodeKey as $key => $value) {
            $explodeKey[$key] = preg_replace('#[^A-Za-z0-9]#', '', base64_encode($value));
        }

        return implode('/', $explodeKey);
    }
    public function set($key, $data, $expire=null)
    {
        if (empty($this->typeCache)) {
            return;
        }
        if (empty($expire)) {
            $expire = $this->expiration;
        }
        if ($this->typeCache == 'xcache' && is_object($data)) {
            return $this->cache->set($this->getKeyFormat($key), serialize($data), $expire);
        }
        if ($this->typeCache == 'apc' || $this->typeCache == 'xcache') {
            return $this->cache->set($this->getKeyFormat($key), $data, $expire);
        } elseif ($this->typeCache == 'file') {
            $file = new File((string) $this->folder->absolute() .'/'. $this->getKeyFormatFile($key) .'.phpc');
            if (!is_dir($file->getFolder())) {
            	try {
            		mkdir($file->getFolder(), 0777, true);
            	} catch (Exception $e) {
            		throw new Exception($file->getFolder());
            	}
                
            }

            return $this->cache->set($this->getKeyFormat($key), $this->getKeyFormatFile($key) .'.'. $file->getExtension(), serialize($data));
        } else {
            return $this->cache->set($this->getKeyFormat($key), $data, null, $expire);
        }
    }
    public function remove($key)
    {
        if (empty($this->typeCache)) {
            return;
        }
        $this->cache->remove($this->getKeyFormat($key));
    }
    public function get($key)
    {
        if (empty($this->typeCache)) {
            return null;
        }
        if ($this->typeCache == 'file') {
            if (!empty($this->expiration) && (time()-$this->cache->getCreated($key))>=$this->expiration) {
                $this->remove($key);

                return null;
            }

            return unserialize($this->cache->get($this->getKeyFormat($key)));
        }
        $data = $this->cache->get($this->getKeyFormat($key));
        if ($this->typeCache != 'xcache') {
            return $data;
        }
        $unserialize = @unserialize($data);
        if ($data === 'b:0;' || $unserialize !== false) {
            return $unserialize;
        }

        return $data;
    }
    public function getCacheFolder()
    {

        return $this->folder->absolute();
    }
    public function getCacheFolderName()
    {
        return $this->folderName;
    }
    public function isCacheActive()
    {
        return !empty($this->typeCache);
    }
    public function getCacheObject()
    {
    }
}
