<?php
package('arhframe.frameworkCss');

/**
*
*/
class FactoryCssCompilerManager
{

    public static function getCssCompilerManager($type, $fwFile, $moduleName=null, $folderFw=null, $folderCss=null)
    {
        $className = ucfirst($type).'Manager';
        if (!class_exists($className)) {
            throw new ArhframeException("Compiler css '$type' doesn't exist");
        }

        return new $className($fwFile, $moduleName, $folderFw, $folderCss);
    }
}
