<?php
import('arhframe.BeanLoader');
function debug($var, $toDebugBar=true)
{
    $varDump = \BeanLoader::getInstance()->getBean('arhframe.varDump');
    $varDump->setVarDump($var, $toDebugBar);
    if (!$toDebugBar) {
        echo $varDump;
    }

    return $varDump;
}

class VarDump
{
    private $key;
    private $var;
    private $backTrace;
    private $debugBar;
    public function __construct()
    {
    }
    private function generateKey()
    {
        $backTrace = debug_backtrace();
        $this->backTrace = $backTrace[2];

        return $this->backTrace['file'] .':'. $this->backTrace['line'];
    }
    public function getOutput()
    {
        return array($this->key => print_r($this->var, true));
    }
    public function __toString()
    {
        ob_start();
        \var_dump($this->var);
        $var = ob_get_contents();
        ob_clean();

        return 'Var dump in file '. $this->backTrace['file'].' at line '.
        $this->backTrace['line'] .":\n<br/>\n". $var;
    }
    public function setVarDump($var, $toDebugBar = true)
    {
        $this->key = $this->generateKey();
        $this->var = $var;
        if ($toDebugBar) {
            $this->debugBar->addVarDumpCollector($this);
        }
    }
    /**
     * @Required
     */
    public function setDebugBar($debugBar)
    {
        $this->debugBar = $debugBar;
    }
}
