<?php
/**
*
*/
class XCache
{

    public function __construct()
    {
        if (!function_exists('xcache_get')) {
            throw new ArhframeException("XCache not installed");

        }
    }
    public function clear()
    {
        xcache_clear_cache(XC_TYPE_VAR, 0);
        xcache_clear_cache(XC_TYPE_PHP, 0);
    }
    public function get($key)
    {
        if (!xcache_isset($key)) {
            return null;
        }

        return xcache_get($key);
    }
    public function remove($key)
    {
        if (!xcache_isset($key)) {
            return false;
        }

        return xache_unset($key);
    }
    public function set($key, $data, $expire = NULL)
    {
        if (!empty($expire)) {
            return xcache_set($key, $data, $expire);
        } else {
            return xcache_set($key, $data);
        }
    }
}
