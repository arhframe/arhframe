<?php
import('arhframe.compressor.Compressor');
import('arhframe.frameworkCss.LessManager');
import('arhframe.eden.eden');
import('arhframe.Config');
import('arhframe.basefile.const');
import('arhframe.basefile.simpleconst');
import('arhframe.NeedleManager');
import('arhframe.cache.CacheManager');
import('arhframe.yamlarh.Yamlarh');

/**
*
*/
class FrameworkFrontendManager
{
    private $frameworkName;
    private $compressor;
    private $lessFiles;
    private $cssFiles;
    private $bootstrap;
    private $bootstrapFilename = 'bootstrap.yml';
    private $config;
    private $output = null;
    public function __construct($frameworkName)
    {
        $this->frameworkName = $frameworkName;
        $cache = cache($this, false, false);
        $output = $cache->get($this->frameworkName);
        if (!empty($output)) {
            return;
        }
        if (!is_dir(dirname(__FILE__) .'/../..'. FRAMEWORK_CSS_DIRECTORY .'/'. $frameworkName)) {
            throw new ArhframeException("CSS Framework doesn't exist: '". $frameworkName ."'");
        }
        $this->config = Config::getInstance();
        $this->frameworkName = $frameworkName;
        $this->compressor = new Compressor(FRAMEWORK_CSS_DIRECTORY .'/'. $frameworkName, FRAMEWORK_CSS_DIRECTORY .'/'. $frameworkName);
        $file = dirname(__FILE__) .'/../..'. FRAMEWORK_CSS_DIRECTORY .'/'. $frameworkName .'/'. $this->bootstrapFilename;
        if (!is_file($file)) {
            throw new ArhframeException($this->bootstrapFilename ." not found for framework css '". $frameworkName ."'");
        }
        $yamlarh = new Yamlarh(FRAMEWORK_CSS_DIRECTORY .'/'. $frameworkName .'/'. $this->bootstrapFilename);
        $this->bootstrap = $yamlarh->parse();
    }
    private function isMultipleLoad($filename)
    {
        return $filename[strlen($filename)-1] == '*';
    }
    private function getFolder($filename)
    {
        $parsedFolder = explode('/', $filename);
        $folder = dirname(__FILE__) .'/../..'. FRAMEWORK_CSS_DIRECTORY .'/'. $this->frameworkName;
        if (count($parsedFolder) <=1) {
            return $folder;
        }
        unset($parsedFolder[count($parsedFolder)-1]);

        return $folder .'/'. implode('/', $parsedFolder);
    }
    private function getFiles($bootstrapKey, $extension)
    {
        $completeFiles = array();
        $files = $this->bootstrap[$bootstrapKey];
        if (empty($files)) {
            return null;
        }
        if (!is_array($files)) {
            $files = array($files);
        }
        foreach ($files as $key => $file) {
            $parameter = null;
            if (is_array($file)) {
                $parameter = $file;
                $file = $key;
            }
            $parameter['folderName'] = dirname($file);
            if ($parameter['folderName'] == '.') {
                $parameter['folderName'] = '';
            }
            if ($this->isMultipleLoad($file)) {
                $folder = new Folder($this->getFolder($file));
                $filesMultipleEden = $folder->getFiles('/\.'. $extension .'$/');
                $filesMultiple = null;
                foreach ($filesMultipleEden as $value) {
                    $filesMultiple[$value->absolute()] = $parameter;
                }
                $completeFiles = array_merge($completeFiles, $filesMultiple);
                continue;
            }
            $completeFiles[dirname(__FILE__) .'/../..'. FRAMEWORK_CSS_DIRECTORY .'/'. $this->frameworkName .'/'. $file] = $parameter;
        }

        return $completeFiles;
    }
    private function loadLess()
    {
        $files = $this->getFiles('less', 'less');
        if (empty($files)) {
            return;
        }
        $output = null;
        $filesCompressor = null;
        foreach ($files as $filename => $parameter) {
            if (isset($parameter['responsive']) && $parameter['responsive']!=$this->config->config->responsive) {
                continue;
            }
            $lessManager = new LessManager(basename($filename), '',
                dirname($filename), dirname($filename) .'/../css');
            $lessManager->setWithOptimize(false);
            if(($this->bootstrap['autocompilecss'] || $parameter['compile'])
                && (!isset($parameter['compile']) || (isset($parameter['compile']) && $parameter['compile']))
                ){
                $filesCompressor[] = $lessManager->getFile();
            } else {
                $folderForHttp = FRAMEWORK_CSS_DIRECTORY .'/'. $this->frameworkName .'/'. $parameter['folderName'] .'/../css';
                $output .= $this->putFileInParameter('<link href="'. $lessManager->getHttpName($folderForHttp) .'" rel="stylesheet" type="text/css"/>', $parameter['conditional']) . "\n";
            }
        }
        if ($this->bootstrap['autocompilecss']) {
            $this->output .= $this->compressor->getCssCompressHtml($filesCompressor) . "\n";
        }
        $this->output .= $output;
    }
    private function loadSass()
    {
        $files = $this->getFiles('sass', 'sass');
        if (empty($files)) {
            return;
        }
        $output = null;
        $filesCompressor = null;
        foreach ($files as $filename => $parameter) {
            if (isset($parameter['responsive']) && $parameter['responsive']!=$this->config->config->responsive) {
                continue;
            }
            $lessManager = new SassManager(basename($filename), '',
                dirname($filename), dirname($filename) .'/../css');
            $lessManager->setWithOptimize(false);
            if(($this->bootstrap['autocompilecss'] || $parameter['compile'])
                && (!isset($parameter['compile']) || (isset($parameter['compile']) && $parameter['compile']))
                ){
                $filesCompressor[] = $lessManager->getFile();
            } else {
                $folderForHttp = FRAMEWORK_CSS_DIRECTORY .'/'. $this->frameworkName .'/'. $parameter['folderName'] .'/../css';
                $output .= $this->putFileInParameter('<link href="'. $lessManager->getHttpName($folderForHttp) .'" rel="stylesheet" type="text/css"/>', $parameter['conditional']) . "\n";
            }
        }
        if ($this->bootstrap['autocompilecss']) {
            $this->output .= $this->compressor->getCssCompressHtml($filesCompressor) . "\n";
        }
        $this->output .= $output;
    }
    private function loadScss()
    {
        $files = $this->getFiles('scss', 'scss');
        if (empty($files)) {
            return;
        }
        $output = null;
        $filesCompressor = null;
        foreach ($files as $filename => $parameter) {
            if (isset($parameter['responsive']) && $parameter['responsive']!=$this->config->config->responsive) {
                continue;
            }
            $lessManager = new ScssManager(basename($filename), '',
                dirname($filename), dirname($filename) .'/../css');
            $lessManager->setWithOptimize(false);
            if(($this->bootstrap['autocompilecss'] || $parameter['compile'])
                && (!isset($parameter['compile']) || (isset($parameter['compile']) && $parameter['compile']))
                ){
                $filesCompressor[] = $lessManager->getFile();
            } else {
                $folderForHttp = FRAMEWORK_CSS_DIRECTORY .'/'. $this->frameworkName .'/'. $parameter['folderName'] .'/../css';
                $output .= $this->putFileInParameter('<link href="'. $lessManager->getHttpName($folderForHttp) .'" rel="stylesheet" type="text/css"/>', $parameter['conditional']) . "\n";
            }
        }
        if ($this->bootstrap['autocompilecss']) {
            $this->output .= $this->compressor->getCssCompressHtml($filesCompressor) . "\n";
        }
        $this->output .= $output;
    }
    private function loadCss()
    {
        $files = $this->getFiles('css', 'css');
        if (empty($files)) {
            return;
        }
        $filesCompressor = null;
        $output = null;
        foreach ($files as $filename => $parameter) {
            if (isset($parameter['responsive']) && $parameter['responsive']!=$this->config->config->responsive) {
                continue;
            }
            if(($this->bootstrap['autocompilecss'] || $parameter['compile'])
                && (!isset($parameter['compile']) || (isset($parameter['compile']) && $parameter['compile']))
                ){
                $filesCompressor[] = new File($filename);
            } else {
                $cssHttpName = SERVERNAME . FRAMEWORK_CSS_DIRECTORY .'/'. $this->frameworkName .'/'. $parameter['folderName'] .'/'. basename($filename);
                $output .= $this->putFileInParameter('<link href="'. $cssHttpName .'" rel="stylesheet" type="text/css"/>', $parameter['conditional']) . "\n";
            }
        }
        if ($this->bootstrap['autocompilecss']) {
            $this->output .= $this->compressor->getCssCompressHtml($filesCompressor) . "\n";
        }
        $this->output .= $output;
    }
    public function loadNeedle()
    {
        $needles = $this->bootstrap['needle'];
        if (empty($needles)) {
            return;
        }
        if (!is_array($needles)) {
            $needles = array($needles);
        }
        foreach ($needles as $key => $needleFile) {
            $parameter = null;
            if (is_array($needleFile)) {
                $parameter = $needleFile;
                $needleFile = $key;
            }
            $needle = new NeedleManager($needleFile);
            $this->output .= $this->putFileInParameter($needle->getHtml(), $parameter['conditional']) . "\n";
        }
    }
    private function loadScript()
    {
        $script = $this->bootstrap['script'];
        if (empty($script)) {
            return;
        }
        if (is_array($script)) {
            $script = implode(";\n", $script);
        }
        $this->output .=  '<script type="text/javascript">'. $script .'</script>'. "\n";
    }
    private function loadJs()
    {
        $files = $this->getFiles('js', 'js');
        if (empty($files)) {
            return;
        }
        $filesCompressor = null;
        $output = null;
        foreach ($files as $filename => $parameter) {
            if(($this->bootstrap['autocompilecss'] || $parameter['compile'])
                && (!isset($parameter['compile']) || (isset($parameter['compile']) && $parameter['compile']))
                ){
                $filesCompressor[] = new File($filename);
            } else {
                $jsHttpName = SERVERNAME . FRAMEWORK_CSS_DIRECTORY .'/'. $this->frameworkName .'/'. $parameter['folderName'] .'/'. basename($filename);
                $output .= $this->putFileInParameter('<script type="text/javascript" src="'. $jsHttpName .'"></script>', $parameter['conditional']) . "\n";
            }
        }
        if ($this->bootstrap['autocompilejs']) {
            $this->output .=  $this->compressor->getJsCompressHtml($filesCompressor) . "\n";
        }
        $this->output .= $output;
    }
    private function putFileInParameter($file, $parameter)
    {
        if (empty($parameter)) {
            return $file;
        }

        return sprintf($parameter, $file);
    }
    public function getOutput()
    {
        $cache = cache($this, false, false);
        $output = $cache->get($this->frameworkName);
        if (!empty($output)) {
            return $output;
        }
        $this->loadCss();
        $this->loadLess();
        $this->loadSass();
        $this->loadScss();
        $this->loadNeedle();
        $this->loadJs();
        $this->loadScript();
        $cache->set($this->frameworkName, $this->output);

        return $this->output;
    }

    public function __toString()
    {
        return $this->getOutput();
    }
}
