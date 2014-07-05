<?php
function getNeedle($resource=null)
{
    if (empty($resource)) {
        throw new ArhframeException("Resource name can't be empty");
    }
    import('arhframe.NeedleManager');
    $resourcesManager = new NeedleManager($resource);

    return $resourcesManager->getHttpFile();
}
