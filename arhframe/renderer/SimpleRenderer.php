<?php
import('arhframe.renderer.AbstractRenderer');

class SimpleRenderer extends AbstractRenderer
{
    public function __construct()
    {
        parent::__construct();
        import('arhframe.renderer.filterToLoad.**');
        import('arhframe.renderer.functionToLoad.**');
        $this->setName('Template php');
    }
    public function createRenderer($page, $array=null)
    {
        parent::createRenderer($page, $array);
    }
    public function getHtml()
    {
        if (!empty($this->array)) {
            $array = $this->array;
            extract($array);
        }
        if (!$this->isFile()) {
            return $this->page;
        }
        ob_start();
        require $this->page;
        $content = ob_get_clean();

        return $content;
    }
}
