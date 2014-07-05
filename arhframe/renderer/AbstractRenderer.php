<?php
import('arhframe.DependanceManager');
import('arhframe.cache.CacheManager');
import('arhframe.yamlarh.Yamlarh');
abstract class AbstractRenderer
{
    protected $dependanceManager;
    protected $page;
    protected $pageName;
    protected $array;
    protected $folder = null;
    protected $isFile = true;
    private $debugBarManager;
    private $execStartTime=0;
    private $execEndTime=0;
    private $name;
    protected $extensions = array();
    public function __construct()
    {
        $this->cache = cache($this);
        $this->dependanceManager = DependanceManager::getInstance();
    }
    public function setFolder($folder)
    {
        $this->folder = $folder;
    }
    public function getPage()
    {
        return $this->page;
    }
    public function setPage($page)
    {
        $this->page = $page;
    }
    public function getPageName()
    {
        return $this->pageName;
    }
    public function setPageName($pageName)
    {
        $this->pageName = $pageName;
    }
    public function getArray()
    {
        return $this->array;
    }
    public function isFile()
    {
        return $this->isFile;
    }
    private function setExtension()
    {
        $yamlarh = new Yamlarh('renderers');
        $this->extensions = $yamlarh->parse();
        $this->extensions = $this->extensions[$this->name];
        if (empty($this->extensions)) {
            $this->extensions = array();
        }
    }
    public function createRenderer($page, $array=null)
    {
        $this->pageName = $page;
        $this->page = $page;
        $this->array = $array;
        $pathinfo = pathinfo($page);
        if (!is_array($this->extensions)) {
            $extension = array($this->extensions);
        }

        $this->extensions = $extension;
        if (empty($this->folder)) {
            $forced = DependanceManager::parseForce($page);
            $pageName = DependanceManager::parseForceFileName($page);
            if (!empty($forced)) {
                $forced = '@'. $forced .'/';
            }
            $this->page = $this->dependanceManager->getFile($forced .'view/'. $pageName);
        } else {
            $this->page = $this->folder .'/'. $page;
        }

    }
    public function setCollectorToDebugBar()
    {
        $this->getDebugBarManager()->addTemplateCollector($this);
    }
    abstract public function getHtml();
    public function isOutput()
    {
        return false;
    }
    public function setName($name)
    {
        $this->name = $name;
        $this->setExtension();
    }
    public function getName()
    {
        return $this->name;
    }
    public function getExtensions()
    {
        return $this->extensions;
    }
    public function setExecStartTime($execStartTime)
    {
        $this->execStartTime = $execStartTime;
    }
    public function getExecStartTime()
    {
        return $this->execStartTime;
    }
    public function setExecEndTime($execEndTime)
    {
        $this->execEndTime = $execEndTime;
    }
    public function getExecEndTime()
    {
        return $this->execEndTime;
    }
    public function setDebugBarManager(DebugBarManager $debugBarManager)
    {
        $this->debugBarManager = $debugBarManager;
        $this->setCollectorToDebugBar();
    }
    public function getDebugBarManager()
    {
        return $this->debugBarManager;
    }
}
