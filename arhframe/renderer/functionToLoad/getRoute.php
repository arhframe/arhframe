<?php
function getRoute()
{
    import('arhframe.Router');
    $route = Router::getInstance();
    try {
        if (func_num_args()>0) {
            return Router::writeRoute(call_user_func_array(array($route, "getRouteByName"), func_get_args()));
        } else {
            return $route->getCurrentRoute();
        }
    } catch (Exception $e) {
        throw new ArhframeException($e->getMessage());
    }
}
