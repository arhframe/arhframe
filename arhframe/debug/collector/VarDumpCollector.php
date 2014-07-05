<?php
import('arhframe.debug.VarDump');
class VarDumpCollector extends DebugBar\DataCollector\DataCollector implements DebugBar\DataCollector\Renderable
{
    private $arrayValue=array();
    private $count = 1;
    public function __construct(VarDump $varDump)
    {
        $this->arrayValue = $varDump->getOutput();
    }
    public function addVarDump(VarDump $varDump)
    {
        $this->count++;
        $this->arrayValue = array_merge_recursive_distinct($this->arrayValue, $varDump->getOutput());
    }
    public function collect()
    {
        return array('count'=>$this->count, 'values'=>$this->arrayValue);
    }

    public function getName()
    {
        return 'Var dump';
    }
    public function getWidgets()
    {
        return array(
            'Var dump' => array(
                'icon' => 'eye',
                'widget' => 'PhpDebugBar.Widgets.VariableListWidget',
                'map' => 'Var dump.values',
                'default' => '{}'
            ),
            'Var dump:badge' => array(
                'map' => 'Var dump.count',
                'default' => 0
            )
        );
    }
}
