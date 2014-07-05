<?php
import('arhframe.renderer.AbstractRenderer');
class SimpleTemplateCollector extends DebugBar\DataCollector\DataCollector implements DebugBar\DataCollector\Renderable
{
    private $renderers;
    private $timeDataCollector;
    private $nameRenderer;
    public function __construct(AbstractRenderer $renderer, DebugBar\DataCollector\TimeDataCollector $timeDataCollector=null)
    {
        $this->addRenderer($renderer);
        $this->timeDataCollector = $timeDataCollector;
    }
    public function addRenderer(AbstractRenderer $renderer)
    {
        $this->renderers[] = $renderer;
        if ($this->nameRenderer == 'template') {
            return;
        }
        if (!empty($this->nameRenderer) && $this->nameRenderer != $renderer->getName()) {
            $this->nameRenderer = 'template';

            return;
        }
        if (empty($this->nameRenderer)) {
            $this->nameRenderer = $renderer->getName();
        }
    }
    /**
     * {@inheritDoc}
     */
    public function collect()
    {
        $templates = array();
        $accuRenderTime = 0;

        foreach ($this->renderers as $tpl) {
            if (!empty($this->timeDataCollector)) {
                $this->timeDataCollector->addMeasure($tpl->getName().'.render('. basename($tpl->getPageName()). ')', $tpl->getExecStartTime(), $tpl->getExecEndTime());
            }
            $execTime = $tpl->getExecEndTime()-$tpl->getExecStartTime();
            $accuRenderTime += $execTime;
            $templates[] = array(
                'name' => $tpl->getPageName(),
                'render_time' => $execTime,
                'render_time_str' => $this->formatDuration($execTime)
            );
        }

        return array(
            'nb_templates' => count($this->renderers),
            'templates' => $templates,
            'accumulated_render_time' => $accuRenderTime,
            'accumulated_render_time_str' => $this->formatDuration($accuRenderTime)
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->nameRenderer;
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
