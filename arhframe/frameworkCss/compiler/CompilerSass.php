<?php
package('arhframe.frameworkCss.compiler');
import('arhframe.phpsass.SassParser');
import('arhframe.Config');

/**
*
*/
class CompilerSass implements CompilerFwCss
{
    private $sass;
    private $config;
    public function __construct()
    {
        $this->config = Config::getInstance();
        if ($this->config->assetics->sass->installed) {
            return;
        }
        $options = array(
            'style' => 'nested',
            'cache' => FALSE,
            'syntax' => 'sass',
            'debug' => FALSE,
            'callbacks' => array(
                'warn' => 'warn',
                'debug' => 'debug'
            ),
        );
        $this->sass = new SassParser($options);
    }
    public function compile($in, $out)
    {
        if ($this->config->assetics->sass->installed) {
            exec("sass ". $in ." ". $out);

            return;
        }
        file_put_contents($out, $this->sass->toCss($in));
    }
    public function getName()
    {
        return 'sass';
    }
}
