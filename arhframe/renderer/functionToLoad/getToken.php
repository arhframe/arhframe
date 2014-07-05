<?php
function getToken($withKey=false)
{
    import('arhframe.secure.Secure');
    $secure = Secure();
    if (empty($withKey)) {
        return $secure->getToken();
    }

    return $secure->getTokenKey() ."=". $secure->getToken();

}
