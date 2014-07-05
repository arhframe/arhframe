<?php
import('arhframe.cache.CacheManager');
import('arhframe.yamlarh.Yamlarh');
/**
*
*/
class HelperManager
{
    private $helpers = array();
    public function __construct()
    {
    }
    private function loadHelper()
    {
        if (empty($this->helpers)) {
            return;
        }
    }
    public function getHelper($name)
    {
        if (empty($this->helpers[$name])) {
            throw new ArhframeException("Helper '". $name ."' does not exist.");
        }
        import($this->helpers[$name]['import']);
        $class = new ReflectionClass($this->helpers[$name]['class']);
        if (func_num_args()>1) {
            $args = func_get_args();
            unset($args[0]);
            $object = $class->newInstanceArgs($args);
        } else {
            $object = $class->newInstance();
        }

        return $object;
    }
    /**
     * @Required
     */
    public function setHelperYaml($array)
    {
        $array = $array['helpers'];
        if (empty($array)) {
            return;
        }
        $this->helpers = $array;
        $this->loadHelper();
    }
}
