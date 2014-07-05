<?php
package('arhframe.frameworkCss.compiler');
interface CompilerFwCss
{
    public function compile($in, $out);
    public function getName();
}
