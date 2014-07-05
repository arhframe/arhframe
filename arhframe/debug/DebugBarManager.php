<?php

import('arhframe.Config');
import('arhframe.basefile.const');
import('arhframe.debug.collector.*');
import('arhframe.LoggerManager');
import('arhframe.debug.VarDump');
import('arhframe.htmlmanipulator.HtmlProxy');
/**
*
*/
function DebugBar()
{
    return BeanLoader::getInstance()->getBean('arhframe.debugBarManager');
}
class DebugBarManager
{
    private $debugBar;
    private $debugBarRenderer;
    private $timeCollector;
    private $noFormat = false;
    private $htmlProxy;
    public function __construct()
    {
        if (!Config::getInstance()->config->debug) {
            return ;
        }
        $this->debugBar = new DebugBar\DebugBar();
        $this->timeCollector = new DebugBar\DataCollector\TimeDataCollector();
        $this->debugBar->addCollector(new DebugBar\DataCollector\MessagesCollector());
        $this->debugBar->addCollector(new DebugBar\DataCollector\PhpInfoCollector());
        $this->debugBar->addCollector(new RequestArhframeCollector());
        $this->addConfigCollector(Config::getInstance('config', true));
        $this->debugBar->addCollector(new RouterCollector());
        $this->debugBarRenderer = $this->debugBar->getJavascriptRenderer();
        $this->debugBarRenderer->setBaseUrl(SERVERNAME . $this->debugBarRenderer->getBaseUrl());

        $this->debugBar->addCollector(new DebugBar\DataCollector\MemoryCollector());
        $this->debugBar->addCollector(new DebugBar\DataCollector\ExceptionsCollector());
        $this->setFolder();

    }
    public function info($string)
    {
        $this->debugBar['messages']->info($string);
    }
    public function warning($string)
    {
        $this->debugBar['messages']->warning($string);
    }
    public function error($string)
    {
        $this->debugBar['messages']->error($string);
    }
    public function emergency($string)
    {
        $this->debugBar['messages']->emergency($string);
    }
    public function critical($string)
    {
        $this->debugBar['messages']->critical($string);
    }
    public function notice($string)
    {
        $this->debugBar['messages']->notice($string);
    }
    public function debug($string)
    {
        $this->debugBar['messages']->debug($string);
    }
    public function setFolder($folder = null)
    {
        if (!Config::getInstance()->config->debug) {
            return ;
        }
        if (empty($folder)) {
            $folder = Config::getInstance()->config->debug_folder_for_analysis;
        }

        if (empty($folder)) {
            return;
        }
        $folder = dirname(__FILE__) .'/../..'. $folder;
        if (!is_dir($folder)) {
            throw new ArhframeException('Debug folder for analysis "'. $this->cacheConfig['folder'] .'" doesn\'t exist.');
        }
        $this->debugBar->setStorage(new DebugBar\Storage\FileStorage($folder));
    }
    public function addDoctrineCollector($debugStack)
    {
        if (!Config::getInstance()->config->debug) {
            return ;
        }
        $this->debugBar->addCollector(new DebugBar\Bridge\DoctrineCollector($debugStack));
    }
    public function addMonologCollector($logger)
    {
        if (!Config::getInstance()->config->debug) {
            return ;
        }
        try {
            $this->debugBar['monolog']->addLogger($logger);
        } catch (Exception $e) {
            $this->debugBar->addCollector(new DebugBar\Bridge\MonologCollector($logger));
        }
    }
    public function formatHtmlForDebugBar()
    {
        if (!Config::getInstance()->config->debug || $this->noFormat) {
            return;
        }
        try {
            $this->debugBar->addCollector($this->timeCollector);
        } catch (DebugBar\DebugBarException $e) {

        }
        $this->htmlProxy->appendHead($this->debugBarRenderer->renderHead());
        $this->htmlProxy->appendBody($this->debugBarRenderer->render());
    }
    public function modifySendSystem()
    {
        if ($this->htmlProxy->isNoRewrite()) {
            //$this->debugBar->sendDataInHeaders();
        }

        return;
    }
    public function addTwigCollector($env)
    {
        if (!Config::getInstance()->config->debug) {
            return $env;
        }
        $env = new DebugBar\Bridge\Twig\TraceableTwigEnvironment($env, $this->timeCollector);
        $this->debugBar->addCollector(new DebugBar\Bridge\Twig\TwigCollector($env));

        return $env;
    }
    public function addPdoCollector($pdo)
    {
        if (!Config::getInstance()->config->debug) {
            return $pdo;
        }
        $pdo = new DebugBar\DataCollector\PDO\TraceablePDO($pdo);
        $this->debugBar->addCollector(new DebugBar\DataCollector\PDO\PDOCollector($pdo));

        return $pdo;
    }
    public function addConfigCollector($data)
    {
        if (!Config::getInstance()->config->debug) {
            return ;
        }
        $this->debugBar->addCollector(new DebugBar\DataCollector\ConfigCollector($data));
    }
    public function addTemplateCollector($renderer)
    {
        if (!Config::getInstance()->config->debug) {
            return ;
        }
        try {
            $this->debugBar[$renderer->getName()]->addRenderer($renderer);
        } catch (Exception $e) {
            $this->debugBar->addCollector(new SimpleTemplateCollector($renderer, $this->timeCollector));
        }
    }
    public function addVarDumpCollector(VarDump $varDump)
    {
        if (!Config::getInstance()->config->debug) {
            return ;
        }

        try {
            $this->debugBar['Var dump']->addVarDump($varDump);
        } catch (Exception $e) {
            try{
                $this->debugBar->addCollector(new VarDumpCollector($varDump));
            }catch (Exception $p) {
                throw new Exception($e);
                
            }
            
        }
    }
    public function addFormCollector($name, $renderTime)
    {
        if (!Config::getInstance()->config->debug) {
            return ;
        }
        try {
            $this->debugBar['Form']->addForm($name, $renderTime);

            return;
        } catch (Exception $e) {
            $this->debugBar->addCollector(new FormCollector());
        }
        $this->debugBar['Form']->addForm($name, $renderTime);
    }
    public function setNoFormat($noFormat)
    {
        $this->noFormat = $noFormat;
    }
    public function getDebugBar()
    {
        return $this->$debugBar;
    }

    /*
     * @Required
     */
    public function setHtmlProxy(HtmlProxy $htmlProxy)
    {
        $this->htmlProxy = $htmlProxy;
    }
}
