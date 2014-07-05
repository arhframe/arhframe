<?php

use Symfony\Component\Yaml\Yaml;
import('arhframe.Config');
import('arhframe.DependanceManager');
import('arhframe.BeanLoader');
import('arhframe.yamlarh.Yamlarh');
 function LanguageManager($localization=null)
 {
    $languageManager = BeanLoader::getInstance()->getBean('arhframe.languagemanager');
    $languageManager->setLocalization($localization);

    return $languageManager;
 }

/**
*
*/
class LanguageManager
{
    private $request;
    private $localization;
    private $translation;

    private $config;
    private static $_instance = null;
    private $arrayTranslation = array();
    public function __construct($localization=null)
    {

        $this->config = Config::getInstance();
        $this->config = $this->config->config;

    }
    public static function getInstance($localization=null)
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new LanguageManager($localization);
        }

        return self::$_instance;
    }
    private function loadTranslation($module=null)
    {
        $dependanceManager = DependanceManager::getInstance();
        $dependances = $dependanceManager->getDependance();
        if (!empty($dependances)) {
            krsort($dependances);
            if (!empty($dependances)) {
                foreach ($dependances as $dependance) {
                    $this->load($dependance);
                }
            }
        }
        $this->load($dependanceManager->getCurrentModule());
    }

    private function load($module=null){
        $folder = dirname(__FILE__).'/..'. DependanceManager::getModuleDirectory($module) .'/resources/language';
        $this->loadFromFolder($folder, $this->localization);
        $this->loadFromFolder($folder, $this->generalLocalization($this->localization));
    }
    private function loadFromFolder($folder, $localization){
    	$folder = new Folder($folder);
    	if (empty($localization)) {
    		$this->arrayTranslation = null;

    		return;
    	}
    	$folder->append($localization);
    	if(!$folder->isFolder() && $localization != $this->config->defaultLang
    	&& $module == DependanceManager::getInstance()->getCurrentModule()){

    		$localization = $this->config->defaultLang;
    		$this->request->getSession()->set($this->config->keySessionLang, $localization);
    		$this->loadTranslation();

    		return;
    	} elseif (!$folder->isFolder()) {
    		return;
    	}
    	$files = $folder->getFiles('/\.(yml|yaml)$/', true);
    	if (empty($files)) {
    		return;
    	}

    	foreach ($files as $key => $value) {
    		$yamlarh = new Yamlarh($value->absolute());
    		$arrayTranslation = $this->arrayTranslation;
    		$arrayParsed = $yamlarh->parse();
    		$this->arrayTranslation = array_merge_recursive_distinct($arrayTranslation, $arrayParsed);
    	}
    	Yamlarh::browseVar($this->arrayTranslation);
    }
    public function getLocalization()
    {
        return $this->localization;
    }
    public function setLocalization($localization = null)
    {
        $sessionLang = $this->request->getSession()->get($this->config->keySessionLang);
        if (empty($localization) && empty($sessionLang)) {
            $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 5);
            $lang = explode('-', $lang);
            $lang[1] = strtoupper($lang[1]);
            $lang = implode('_', $lang);
            $lang = explode(',', $lang);
            $lang[1] = strtoupper($lang[1]);
            $lang = implode('_', $lang);
            $lang = substr($lang, 0, 5);
            $localization = $lang;
            $this->request->getSession()->set($this->config->keySessionLang, $localization);
        } elseif (empty($localization)) {
            $localization = $sessionLang;
        } else {
            $this->request->getSession()->set($this->config->keySessionLang, $localization);
        }
        if ($this->getLocalization() != $localization) {
            $this->arrayTranslation = array();
            $this->localization = $localization;
            $translations = cache($this, false, true)->get($this->generalLocalization($localization) .'/arrayTranslation');
            if(empty($translations)){
            	$translations = array();
            }

            $cacheTranslations = cache($this, false, true)->get($localization .'/arrayTranslation');
            if(!empty($cacheTranslations)){
            	$translations = array_merge_recursive_distinct($translations, $cacheTranslations);
            }
            if (empty($translations)) {
                $this->loadTranslation();
                cache($this, false, true)->set($this->localization .'/arrayTranslation', $this->arrayTranslation);
            } else {
                $this->arrayTranslation = $translations;
            }
        }
        $this->localization = $localization;
    }
    public function generalLocalization($localization){
    	$localization = explode('_', $localization);
    	return $localization[0];
    }
    public function translate($key, $value)
    {
        if (empty($this->arrayTranslation)) {
            return;
        }
        $this->arrayTranslation[$key]=$value;
    }
    public function get($key=null)
    {
        if (empty($key)) {
            return $this->arrayTranslation;
        }
        $value = $this->getFromKey($key);
        if ($value == $key) {
            return $key;
        }
        $args = func_get_args();
        $args[0] = $value;
        $return= call_user_func_array("sprintf", $args);

        return $return;
    }
    private function getFromKey($key){
    	if(!empty($this->arrayTranslation[$key])){
    		return $this->arrayTranslation[$key];
    	}
    	$keys = explode('.', $key);
    	if(empty($this->arrayTranslation[$keys[0]])){
    		return $key;
    	}
    	$value = null;
    	foreach ($keys as $keyValue){
    		if($value == null){
    			$value = $this->arrayTranslation[$keyValue];
    			continue;
    		}
    		if(empty($value[$keyValue])){
    			return $value;
    		}
    		$value = $value[$keyValue];
    	}

    	return $value;
    }
    public function getAsString($key=null)
    {
        if (empty($key)) {
            return $this->arrayTranslation;
        }
    	$value = $this->getFromKey($key);
        if ($value == $key) {
            return $key;
        }

        return $value;
    }
    /**
     * @Required
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
        $this->setLocalization($localization);
        $this->loadTranslation();
    }
}
