<?php

/**
*
*/
abstract class MethodHttp implements ArrayAccess, Iterator
{
    private $method;
    public function __construct(&$method)
    {
        $this->method = &$method;
    }

    public function get($key)
    {
        return $this->method[$key];
    }
    public function set($key, $value)
    {
        $this->method[$key] = $value;
    }
    public function remove($key)
    {
        unset($this->method[$key]);
    }
    public function clear()
    {
        foreach ($this->method as $key => $value) {
            $this->remove($key);
        }
    }
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->method[] = $value;
        } else {
            $this->method[$offset] = $value;
        }
    }
    public function offsetExists($offset)
    {
        return isset($this->method[$offset]);
    }
    public function offsetUnset($offset)
    {
        unset($this->method[$offset]);
    }
    public function offsetGet($offset)
    {
        return isset($this->method[$offset]) ? $this->method[$offset] : null;
    }
     public function rewind()
     {
        reset($this->method);
    }

    public function current()
    {
        $var = current($this->method);

        return $var;
    }

    public function key()
    {
        $var = key($this->method);

        return $var;
    }

    public function next()
    {
        $var = next($this->method);

        return $var;
    }

    public function valid()
    {
        $key = key($this->method);
        $var = ($key !== NULL && $key !== FALSE);

        return $var;
    }
}
