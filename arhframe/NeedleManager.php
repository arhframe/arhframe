<?php
import('arhframe.ResourcesManager');
/**
*
*/
class NeedleManager extends ResourcesManager
{

    public function __construct($needle=null)
    {
            parent::__construct($needle, 'needle');
    }
    public static function getJqueryHtml()
    {
        return '<script type="text/javascript">
        if (typeof jQuery == \'undefined\') {
            document.write("<script type=\"text/javascript\" src=\"'. NeedleManager::getJquery() .'\"><\/script>");
        }

        </script>';
    }
    public static function getJquery()
    {
        $needle = new NeedleManager('jquery.js');

        return  $needle->getHttpFile();
    }
    public function optimizerCss($filename)
    {
    }
    public static function loadPluginJquery($pluginFile,$pluginName, $script=null)
    {
        $needle = new NeedleManager($pluginFile);

        return'<script type="text/javascript">
        jQuery(document).ready(function ($) {
            var notLoad = false;
            if (!$().' . $pluginName .') {
                notLoad = true;
                $.ajax({
                  url: "'. $needle->getHttpFile() .'",
                  dataType: "script",
                  success: function (script, textStatus, jqXHR) {
                    '. $script .'
                  }
                });
            }
            if (!notLoad) {
                '. $script .'
            }

        });
    </script>';
    }
}
