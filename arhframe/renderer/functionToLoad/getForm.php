<?php
function getForm($form=null, $route=null, $method='POST')
{
    if (empty($form)) {
        throw new ArhframeException("Form name can't be empty");
    }
    import('arhframe.FormManager');
   $form = new FormManager($form, $route, $method);

   return $form;
}
