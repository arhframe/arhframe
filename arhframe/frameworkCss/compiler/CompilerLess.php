<?php
package('arhframe.frameworkCss.compiler');
import('arhframe.less.lessc');
import('arhframe.Config');

/**
*
*/
class CompilerLess implements CompilerFwCss
{
    private $less;
    private $config;
    public function __construct()
    {
        $this->config = Config::getInstance();
        if ($this->config->assetics->less->installed) {
            return;
        }
        $this->less = new lessc;
    }
    public function compile($in, $out)
    {
        if ($this->config->assetics->less->installed) {
            exec("lessc \"$in\" > \"$out\"");

            return;
        }
        $this->less->checkedCompile($in, $out);
    }
    public function getName()
    {
        return 'less';
    }
}
