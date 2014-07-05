<?php
import('arhframe.Config');
import('arhframe.yamlarh.Yamlarh');
import('arhframe.htmlmanipulator.HtmlProxy');
import('arhframe.Router');
import('arhframe.LanguageManager');
import('arhframe.cache.CacheManager');
/**
*
*/
class FactoryRenderer
{
    private $renderers;
    private $rendererByDefault = 'SimpleRenderer';
    private $page;
    private $debugBarManager;
    private $renderer;
    private $extensions;
    private $htmlProxy;
    private $keyCache = '/View/';
    private $languageManager;
    public function __construct()
    {
        $config = Config::getInstance(null, true);
        if (!empty($config['renderer'])) {
            $this->renderers = (is_array($config['renderer']) ? $config['renderer'] : array($config['renderer']));
        } else {
            $this->renderers = array('simple');
        }
        $yamlarh = new Yamlarh(__DIR__ .'/renderers.yml');
        $this->extensions = $yamlarh->parse();
    }
    public function getRenderersAvailable()
    {
        return $this->renderers;
    }
    public function createRenderer($page, $array=null)
    {
        $this->page = $page;
        $cache = cache($this, true, true)->get($this->page.$this->keyCache. $this->languageManager->getLocalization());
        if (Router::getInstance()->isStatic() && !empty($cache)) {
            return $this;
        }
        $pathinfo = pathinfo($page);
        $extension = strtolower($pathinfo['extension']);
        $extension = $this->getExtensionRenderer($extension);
        if (!empty($extension)) {
            $this->getRenderer(strtolower($pathinfo['extension']));
        } else {
            $this->getRenderer();
        }
        $this->renderer->createRenderer($page, $array);

        return $this;
    }
    public function getRenderer($extension=null)
    {
        $extension = $this->getExtensionRenderer($extension);
        $renderer = null;
        if (!empty($extension)) {
            $renderer = ucfirst($extension).'Renderer';
        }
        try {
            import('arhframe.renderer.'.$renderer);
        } catch (Exception $e) {
            throw new ArhframeException("No template engine renderer found for engine '". ucfirst($extension) ."'.");
        }

        if (!empty($renderer) && class_exists($renderer)) {
            return $this->getRendererObject($renderer);
        }
        if (!empty($extension)) {
            return null;
        }
        foreach ($this->renderers as $extension) {
            $renderer = $this->getRenderer($extension);
            if (!empty($renderer)) {
                return $renderer;
            }
        }
        $rendererName = $this->rendererByDefault;

        return $this->renderer;
    }
    private function getExtensionRenderer($extension)
    {
        if (empty($extension)) {
            return null;
        }
        if (empty($this->extensions)) {
            return $extension;
        }
        foreach ($this->extensions as $extensionRenderer => $extensionsPossible) {
            if (in_array($extension, $extensionsPossible)) {
                return $extensionRenderer;
            }
        }

        return null;
    }
    private function getRendererObject($renderer)
    {
        $this->renderer = new $renderer();
        $this->renderer->setDebugBarManager($this->debugBarManager);

        return $this->renderer;
    }
    public function getHtml()
    {
        $cache = cache($this, true, true)->get($this->page.$this->keyCache. $this->languageManager->getLocalization());
        if (!Router::getInstance()->isStatic() || empty($cache)) {
            if (empty($this->renderer)) {
                throw new ArhframeException("You must use createRenderer before use this method.");
            }
            $time_start = microtime(true);
            $html = $this->renderer->getHtml();
            $time_end = microtime(true);
            $this->renderer->setExecStartTime($time_start);
            $this->renderer->setExecEndTime($time_end);
            $pathinfo = pathinfo($this->renderer->getPageName());
            if (!$this->renderer->isFile() && !empty($pathinfo['extension'])) {
                throw new ArhframeException("No template engine '". $pathinfo['extension'] ."' found for template page '". $this->renderer->getPageName() ."'.");

            }
        } else {
            $html = $cache;
        }
        if (Router::getInstance()->isStatic() && empty($cache)) {
            cache($this, true, true)->set($this->page.$this->keyCache. $this->languageManager->getLocalization(), $html);
        }
        $this->htmlProxy->setHtml($html);
        $this->debugBarManager->formatHtmlForDebugBar();
        $html = $this->htmlProxy->getHtml();
        $this->debugBarManager->modifySendSystem();

        return $html;
    }
    /**
     * @Required
     */
    public function setDebugBarManager(DebugBarManager $debugBarManager)
    {
        $this->debugBarManager = $debugBarManager;
    }
    public function getDebugBarManager()
    {
        return $this->debugBarManager;
    }
    /*
     * @Required
     */
    public function setHtmlProxy(HtmlProxy $htmlProxy)
    {
        $this->htmlProxy = $htmlProxy;
    }
    /*
    * @Required
     */
    public function setRenderers($renderers)
    {
        $this->renderers = $renderers;
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
}
