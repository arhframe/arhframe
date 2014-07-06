<?php

class UserCollector extends DebugBar\DataCollector\DataCollector implements DebugBar\DataCollector\Renderable
{
    private $arrayValue = array();

    public function __construct()
    {

        $this->arrayValue = $this->getFromObject($_SESSION['afUser']);

    }

    public function getFromObject($value, $toReturn = array())
    {
        $values = (array)$value;
        foreach ($values as $key => $value) {
            if (is_object($value) || is_array($value)) {

                $toReturn[$this->getValueName($key)] = print_r($value, true);
            } else {
                $toReturn[$this->getValueName($key)] = $value;
            }

        }
        return $toReturn;
    }

    public function getValueName($value)
    {
        $valeTab = explode("\0", $value);
        return $valeTab[count($valeTab) - 1];
    }

    public function collect()
    {
        return array('values' => $this->arrayValue);
    }

    public function getName()
    {

        return 'Logged in ' . $_SESSION['afUser']->getUsername();
    }

    public function getWidgets()
    {
        return array(
            'Logged in ' . $_SESSION['afUser']->getUsername() => array(
                'icon' => 'user',
                'widget' => 'PhpDebugBar.Widgets.VariableListWidget',
                'map' => 'Logged in ' . $_SESSION['afUser']->getUsername() . '.values',
                'default' => '{}'
            )
        );
    }
}
