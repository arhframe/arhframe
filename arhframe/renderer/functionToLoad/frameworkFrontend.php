<?php
function frameworkFrontend($frameworkName)
{
    import('arhframe.frameworkFrontend.FrameworkFrontendManager');
    $frameworkCssManager = new FrameworkFrontendManager($frameworkName);

    return $frameworkCssManager;
}
