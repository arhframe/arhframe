<?php
package('arhframe.frameworkCss');
import('arhframe.frameworkCss.compiler.CompilerSass');
class ScssManager extends FrameworkCssManager
{
    public function __construct($fwFile, $moduleName=null, $folderFw=null, $folderCss=null)
    {
        parent::__construct('scss', new CompilerScss(), $fwFile, $moduleName, $folderFw, $folderCss);
    }
}
