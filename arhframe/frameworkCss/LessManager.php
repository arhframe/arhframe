<?php
package('arhframe.frameworkCss');
import('arhframe.frameworkCss.compiler.CompilerLess');
class LessManager extends FrameworkCssManager
{
    public function __construct($fwFile, $moduleName=null, $folderFw=null, $folderCss=null)
    {
        parent::__construct('less', new CompilerLess(), $fwFile, $moduleName, $folderFw, $folderCss);
    }
}
