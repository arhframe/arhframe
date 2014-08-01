<?php
function logoutPath()
{
    $ioc = BeanLoader::getInstance();
    $firewall = $ioc->getBean('arhframe.firewall');
    return SERVERNAME . $firewall->getLogout();
}