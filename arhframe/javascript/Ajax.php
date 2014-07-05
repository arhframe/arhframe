<?php
import('arhframe.NeedleManager');
import('arhframe.Router');

/**
*
*/
class Ajax
{
    private $url;
    public function __construct($url=null)
    {
        if (empty($url)) {
            $this->url = Router::getCurrentRoute();
        } elseif (stristr(trim($url), "http://") !== false) {
            $this->url = $url;
        } else {
            $this->url = Router::writeRoute($url);
        }

    }
    public function getHtml()
    {
        $text = NeedleManager::getJqueryHtml();
    }
}
