<?php
class FormCollector extends DebugBar\DataCollector\DataCollector implements DebugBar\DataCollector\Renderable
{
    private $templates;
    private $accuRenderTime = 0;
    public function addForm($name, $renderTime)
    {
        $this->accuRenderTime += $renderTime;
        $this->templates[] = array(
            'name' => $name,
            'render_time' => $renderTime,
            'render_time_str' => $this->formatDuration($renderTime)
        );
    }
    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        return array(
            'nb_templates' => count($this->templates),
            'templates' => $this->templates,
            'accumulated_render_time' => $this->accuRenderTime,
            'accumulated_render_time_str' => $this->formatDuration($this->accuRenderTime)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'Form';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets()
    {
        return array(
            $this->getName() => array(
                'icon' => 'leaf',
                'widget' => 'PhpDebugBar.Widgets.TemplatesWidget',
                'map' => $this->getName(),
                'default' => '[]'
            ),
            $this->getName().':badge' => array(
                'map' => $this->getName().'.nb_templates',
                'default' => 0
            )
        );
    }
}
