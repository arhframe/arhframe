<?php
function loadJquery()
{
    import('arhframe.NeedleManager');
    $resourcesManager = new NeedleManager('jquery.js');
    $resourcesManager->setHtml(true);

    return $resourcesManager->getHtml();
}
