<?php
function getResource($resource=null)
{
    if (empty($resource)) {
        throw new ArhframeException("Resource name can't be empty");
    }
    import('arhframe.ResourcesManager');
    $resourcesManager = new ResourcesManager($resource);

    return $resourcesManager->getHttpFile();
}
