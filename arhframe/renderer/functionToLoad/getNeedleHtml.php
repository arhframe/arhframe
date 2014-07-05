<?php
function getNeedleHtml($resource)
{
    if (empty($resource)) {
        throw new ArhframeException("Resource name can't be empty");
    }
    import('arhframe.NeedleManager');
    $resourcesManager = new NeedleManager($resource);
    $resourcesManager->setHtml(true);

    return $resourcesManager->getHtml();
}
