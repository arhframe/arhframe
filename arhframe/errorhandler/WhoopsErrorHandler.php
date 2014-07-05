<?php
package('arhframe.errorhandler');
/**
*
*/
class WhoopsErrorHandler extends AbstractErrorHandler
{

    public function __construct($option=null)
    {
        parent::__construct($option);
    }
    public function register()
    {
        $whoops = new Whoops\Run();

        // Configure the PrettyPageHandler:
        $errorPage = new Whoops\Handler\PrettyPageHandler();

        $errorPage->setPageTitle($this->option->title); // Set the page's title
        $errorPage->setEditor($this->option->editor);         // Set the editor used for the "Open" link

        $whoops->pushHandler($errorPage);
        $whoops->register();
    }
}
