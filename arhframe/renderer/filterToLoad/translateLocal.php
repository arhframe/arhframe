<?php
function translateLocal($key=null, $localization=null)
{
    if (empty($key)) {
        throw new ArhframeException("Translation key can't be empty");
    }
    if (empty($localization)) {
        throw new ArhframeException("Translation localization can't be empty");
    }

   import('arhframe.LanguageManager');
   $lang = LanguageManager($localization);
   $args = func_get_args();
   unset($args[1]);
   $return = @call_user_func_array(array($lang, "get"), $args);
   if (empty($return)) {
    unset($args[0]);
    throw new ArhframeException('Not enough argument for translation "'. $lang->getAsString($key) .'" arguments give: '. implode(', ', $args));
   }

   return $return;
}
