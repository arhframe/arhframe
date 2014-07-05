<?php

namespace Everzet\Jade\Filter;
import('arhframe.DependanceManager');
import('arhframe.BeanLoader');
import('arhframe.renderer.JadeRenderer');
use Everzet\Jade\Jade;
use Everzet\Jade\Parser;
use Everzet\Jade\Lexer\Lexer;
use Everzet\Jade\Dumper\PHPDumper;
use Everzet\Jade\Visitor\AutotagsVisitor;

class ImportFilter implements FilterInterface
{
    private $jade;
    private $dependanceManager;
    private $debugBar;
    public function __construct()
    {
        $this->debugBar = \BeanLoader::getInstance()->getBean('arhframe.debugBarManager');
        $this->dependanceManager = \DependanceManager::getInstance();
    }
    /**
     * Filter text.
     *
     * @param string $text       text to filter
     * @param array  $attributes filter options from template
     * @param string $indent     indentation string
     *
     * @return string filtered text
     */
    public function filter($text, array $attributes, $indent)
    {
        $dumper = new PHPDumper();
        $dumper->registerVisitor('tag', new AutotagsVisitor());
        $dumper->registerFilter('javascript', new JavaScriptFilter());
        $dumper->registerFilter('cdata', new CDATAFilter());
        $dumper->registerFilter('php', new PHPFilter());
        $dumper->registerFilter('style', new CSSFilter());
        $dumper->registerFilter('import', new ImportFilter());
        // Initialize parser & Jade
        $parser = new Parser(new Lexer());
        $this->jade = new Jade($parser, $dumper);
        $pages = explode("\n", $text);
        $html = null;
        foreach ($pages as $page) {
            $page = trim($page);
            $renderer = new \JadeRenderer();
            $renderer->setDebugBarManager($this->debugBar);
            $renderer->setPageName($page);
            $time_start = microtime(true);
            $page = $this->getPage($page);
            $html .= $indent . $this->jade->render($page);
            $time_end = microtime(true);

            $renderer->setPage($page);
            $renderer->setExecStartTime($time_start);
            $renderer->setExecEndTime($time_end);
            $html .= "\n";
        }

        return $html;
    }
    public function getPage($page)
    {
        $forced = \DependanceManager::parseForce($page);
        $pageName = \DependanceManager::parseForceFileName($page);
        if (!empty($forced)) {
            $forced = '@'. $forced .'/';
        }
        $page = $this->dependanceManager->getFile($forced .'view/'. $pageName);

        return $page;
    }
}
