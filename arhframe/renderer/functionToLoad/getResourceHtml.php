<?php
function getResourceHtml($resource)
{
    if (empty($resource)) {
        throw new ArhframeException("Resource name can't be empty");
    }
    import('arhframe.ResourcesManager');
    $resourcesManager = new ResourcesManager($resource);
    $resourcesManager->setHtml(true);

    return $resourcesManager->getHtml();
}
