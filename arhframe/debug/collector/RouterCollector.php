<?php
import('arhframe.Router');
class RouterCollector extends DebugBar\DataCollector\DataCollector implements DebugBar\DataCollector\Renderable
{
    public function collect()
    {
        $infoRoute = Router::getInstance()->__toString();
        $infoRoute = explode("\n", $infoRoute);
        foreach ($infoRoute as $infoGlobal) {
            $infos = explode(': ', $infoGlobal);
            $infos[1] = trim($infos[1]);
            if (empty($infos[1])) {
                continue;
            }
            $array[trim($infos[0])] = trim($infos[1]);
        }
        $array['Possible routes'] = $this->formatVar(Router::getInstance()->route);

        return $array;
    }

    public function getName()
    {
        return 'router';
    }
    public function getWidgets()
    {
        return array(
            'router' => array(
                'icon' => 'road',
                'widget' => 'PhpDebugBar.Widgets.VariableListWidget',
                'map' => 'router',
                'default' => '{}'
            )
        );
    }
}
