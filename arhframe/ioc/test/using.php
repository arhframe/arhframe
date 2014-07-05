<?php
import('arhframe.ioc.IFoo');
import('arhframe.ioc.IUsing');
/**
*
*/
class using implements IUsing
{
    public $jojo;
    public $juju;
    public $foo;
    public function __construct()
    {
    }
    /**
     *
     * @Required
     */
    public function setFoo(IFoo $foo)
    {
        $this->foo = $foo;
    }
    /**
     *
     * @Required
     */
    public function setJuju($text)
    {
        $this->juju = $text;
    }
    public function setJojo($stream)
    {
        $this->jojo = $stream;
    }
}
