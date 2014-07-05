<?php
function includePage($page, $array=null)
{
    import('arhframe.BeanLoader');
    if (!empty($array) && !is_array($array)) {
        $array = array($array);
    }
    $factory = BeanLoader::getInstance()->getBean('arhframe.FactoryRenderer');
    $factory->getDebugBarManager()->setNoFormat(true);
    $html = $factory->createRenderer($page, $array)->getHtml();
    $factory->getDebugBarManager()->setNoFormat(false);
    echo $html;
}
