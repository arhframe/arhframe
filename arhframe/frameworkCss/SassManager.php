<?php
package('arhframe.frameworkCss');
import('arhframe.frameworkCss.compiler.CompilerSass');
class SassManager extends FrameworkCssManager
{
    public function __construct($fwFile, $moduleName=null, $folderFw=null, $folderCss=null)
    {
        parent::__construct('sass', new CompilerSass(), $fwFile, $moduleName, $folderFw, $folderCss);
    }
}
