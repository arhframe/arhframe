<?php
import('arhframe.secure.Secure');
import('arhframe.eden.eden');
import('arhframe.renderer.FactoryRenderer');
import('arhframe.*');
import('arhframe.yamlarh.Yamlarh');

/**
 * 
 */
class Controller
{
    /**
     * The request object
     * @var Request
     */
    private $request;
    /**
     * The Renderer object
     * @var FactoryRenderer
     */
    private $renderer = null;
    /**
     * The Language Manager object
     * @var LanguageManager
     */
    private $languageManager = null;
    /**
     * [$secure description]
     * @var [type]
     */
    private $secure;
    /**
     * [$config description]
     * @var [type]
     */
    private $config;
    /**
     * [$cache description]
     * @var [type]
     */
    private $cache;
    /**
     * [$doctrineManager description]
     * @var [type]
     */
    private $doctrineManager;
    /**
     * [$helperManager description]
     * @var [type]
     */
    private $helperManager;

    private $simpleDbManager;
    public function __construct()
    {
        $this->config = Config::getInstance();
        $this->cache = cache($this, true, true);
    }
    /**
     * [getCacheManager description]
     * @return [type] [description]
     */
    public function getCacheManager()
    {
        return $this->cache;
    }
    /**
     * [clearCache description]
     * @return [type] [description]
     */
    public function clearCache()
    {
        $this->cache->clear();
    }
    /**
     * [getRenderer description]
     * @return [type] [description]
     */
    public function getRenderer()
    {
        return $this->renderer;
    }
    /**
     * [changeLang description]
     * @param  [type] $localization [description]
     * @return [type] [description]
     */
    public function changeLang($localization)
    {
        $this->languageManager->setLocalization($localization);
    }
    /**
     * [render description]
     * @param  [type] $page  [description]
     * @param  array  $array [description]
     * @return [type] [description]
     */
    public function render($page, $array=array())
    {
        $html = $this->createRenderer($page, $array)->getHtml();
        return new Response($html);
    }
    /**
     * [getArrayDefault description]
     * @return [type] [description]
     */
    private function getArrayDefault()
    {
        return array('app' => $this);
    }
    /**
     * [createRenderer description]
     * @param  [type] $page  [description]
     * @param  array  $array [description]
     * @return [type] [description]
     */
    public function createRenderer($page, $array=array())
    {
        $array = array_merge($this->getArrayDefault(), $array);

        return $this->renderer->createRenderer($page, $array);
    }
    /**
     * [getRender description]
     * @param  [type] $page  [description]
     * @param  [type] $array [description]
     * @return [type] [description]
     */
    public function getRender($page, $array=null)
    {
        $html = $this->createRenderer($page, $array)->getHtml();
        return new Response($html);
    }
    /**
     * [getConfig description]
     * @return [type] [description]
     */
    public function getConfig()
    {
        return $this->config;
    }
    /**
     * [getSimpleDbManager description]
     * @return [type] [description]
     */
    public function getSimpleDbManager()
    {
        return $this->simpleDbManager;
    }
    /**
     * [getDb description]
     * @return [type] [description]
     */
    public function getDb($bddName=null)
    {
        return $this->simpleDbManager->getDb($bddName);
    }
    /**
     * [renderXml description]
     * @param  [type] $value   [description]
     * @param  string $rootTag [description]
     * @return [type] [description]
     */
    public function renderXml($value, $rootTag="root")
    {
        return xml_encode($value, $rootTag);
    }
    /**
     * [renderYml description]
     * @param  [type] $value [description]
     * @return [type] [description]
     */
    public function renderYml($array)
    {
        return Yamlarh::dump($array);
    }
    /**
     * [renderJson description]
     * @param  [type] $array [description]
     * @return [type] [description]
     */
    public function renderJson($array=null)
    {
        return json_encode($array);
    }
    /**
     * [getSecure description]
     * @return [type] [description]
     */
    public function getSecure()
    {
        return $this->secure;
    }
    /**
     * [getToken description]
     * @return [type] [description]
     */
    public function getToken()
    {
        return $this->secure->getToken();
    }
    /**
     * [getInfoRequest description]
     * @param  [type] $var [description]
     * @return [type] [description]
     */
    public function getInfoRequest($var = null)
    {
        return $this->request->getInfoRequest($var);
    }
    /**
     * [getRouter description]
     * @return [type] [description]
     */
    public function getRouter()
    {
        return $this->request->getRouter();
    }
    /**
     * [getRequest description]
     * @return [type] [description]
     */
    public function getRequest()
    {
        return $this->request;
    }
    /**
     * [getSession description]
     * @return [type] [description]
     */
    public function getSession()
    {
        return $this->request->getSession();
    }
    /**
     * [getCookie description]
     * @return [type] [description]
     */
    public function getCookie()
    {
        return $this->request->getCookie();
    }
    /**
     * [getPost description]
     * @return [type] [description]
     */
    public function getPost()
    {
        return $this->request->getPost();
    }
    /**
     * [getGet description]
     * @return [type] [description]
     */
    public function getGet()
    {
        return $this->request->getGet();
    }
    /**
     * [getForm description]
     * @param  [type] $form   [description]
     * @param  [type] $route  [description]
     * @param  string $method [description]
     * @return [type] [description]
     */
    public function getForm($form, $route=null, $method='POST')
    {
        return new FormManager($form, $route, $method);
    }
    /**
     * [getFile description]
     * @param  [type] $file [description]
     * @return [type] [description]
     */
    public function getFile($file)
    {
        return new File($file);
    }
    /**
     * [getFolder description]
     * @param  [type] $folder [description]
     * @return [type] [description]
     */
    public function getFolder($folder)
    {
        return new Folder($folder);
    }
    /**
     * [string description]
     * @param  [type] $string [description]
     * @return [type] [description]
     */
    public function string($string)
    {
        return eden('type', $string);
    }
    /**
     * [getTimezone description]
     * @param  [type] $zone [description]
     * @param  [type] $time [description]
     * @return [type] [description]
     */
    public function getTimezone($zone, $time)
    {
        return eden('timezone', $zone, $time);
    }
    /**
     * [getImage description]
     * @param  [type] $image     [description]
     * @param  [type] $extension [description]
     * @return [type] [description]
     */
    public function getImage($image, $extension=null)
    {
        if (empty($extension)) {
            $extension = $this->getFile($image)->getExtension();
        }

        return eden('image', $image, $extension);
    }
    /**
     * [getResource description]
     * @param  [type] $resource [description]
     * @return [type] [description]
     */
    public function getResource($resource)
    {
        $resourcesManager = new ResourcesManager($resource);

        return $resourcesManager;
    }
    /**
     * [getImageResource description]
     * @param  [type] $resource [description]
     * @return [type] [description]
     */
    public function getImageResource($resource)
    {
        $img = new ImageManager($resource);

        return $img;
    }
    /**
     * [loadTranslation description]
     * @param  [type] $localization [description]
     * @return [type] [description]
     */
    public function loadTranslation($localization=null)
    {
        $this->languageManager->setLocalization($localization);
    }
    /**
     * [translate description]
     * @param  [type] $key          [description]
     * @param  [type] $value        [description]
     * @param  [type] $localization [description]
     * @return [type] [description]
     */
    public function translate($key, $value, $localization=null)
    {
        if (empty($this->languageManager)) {
            $this->loadTranslation($localization);
        }
        $this->languageManager->translate($key, $value);
    }
    /**
     * [getTranslation description]
     * @param  [type] $key [description]
     * @return [type] [description]
     */
    public function getTranslation($key=null)
    {
        if (empty($this->languageManager)) {
            $this->loadTranslation();
        }
        $args = func_get_args();

        return call_user_func_array(array($this->languageManager, "get"), $args);
    }
    /**
     * [getTranslationLocal description]
     * @param  [type] $key          [description]
     * @param  [type] $localization [description]
     * @return [type] [description]
     */
    public function getTranslationLocal($key=null,  $localization=null)
    {
        if (empty($this->languageManager)) {
            $this->loadTranslation($localization);
        }
        $args = func_get_args();
       unset($args[1]);

       return call_user_func_array(array($this->languageManager, "get"), $args);
    }
    /**
     * [getLocalization description]
     * @return [type] [description]
     */
    public function getLocalization()
    {
        return $this->languageManager->getLocalization();
    }
    /**
     * [getDevice description]
     * @return [type] [description]
     */
    public function getDevice()
    {
        return new DeviceManager();
    }
    /**
     * [getEntityManager description]
     * @return [type] [description]
     */
    public function getEntityManager($bddName=null)
    {
        return $this->doctrineManager->getEntityManager($bddName);
    }
    /**
     * [helper description]
     * @param  [type] $name [description]
     * @return [type] [description]
     */
    public function helper($name)
    {
        if (func_num_args()>1) {
            $args = func_get_args();
            $helper = call_user_func_array(array($this->helperManager, 'getHelper'), $args);
        } else {
            $helper = $this->helperManager->getHelper($name);
        }

        return $helper;
    }
    public function beforeStartController()
    {
        $this->request->verifySecurity();
    }
    /**
     * [__before description]
     * @return [type] [description]
     */
    public function __before()
    {
    }
    /**
     * [__after description]
     * @return [type] [description]
     */
    public function __after()
    {
    }
    /**
     * @Required
     * [setLanguageManager description]
     * @param LanguageManager $languageManager [description]
     */
    public function setLanguageManager(LanguageManager $languageManager)
    {
        $this->languageManager = $languageManager;
    }
    /**
     * @Required
     * [setSecure description]
     * @param Secure $secure [description]
     */
    public function setSecure(Secure $secure)
    {
        $this->secure = $secure;
    }
    /**
     * @Required
     * [setRequest description]
     * @param Request $request [description]
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }
    /**
     * @Required
     * [setRenderer description]
     * @param FactoryRenderer $renderer [description]
     */
    public function setRenderer(FactoryRenderer $renderer)
    {
        $this->renderer = $renderer;
    }
    /**
     * @Required
     * [setDoctrineManager description]
     * @param DoctrineManager $doctrineManager [description]
     */
    public function setDoctrineManager(DoctrineManager $doctrineManager)
    {
        $this->doctrineManager = $doctrineManager;
    }
    /**
     * @Required
     * [setSimpleDbManager description]
     * @param SimpleDbManager $simpleDbManager [description]
     */
    public function setSimpleDbManager(SimpleDbManager $simpleDbManager)
    {
        $this->simpleDbManager = $simpleDbManager;
    }
    /**
     * @Required
     * [setHelperManager description]
     * @param HelperManager $helperManager [description]
     */
    public function setHelperManager(HelperManager $helperManager)
    {
        $this->helperManager = $helperManager;
    }
    public function __destruct(){
        
    }
}
