<?php
import('arhframe.eden.eden');
import('arhframe.basefile.const');
import('arhframe.ImageManager');
import('arhframe.frameworkCss.*');
import('arhframe.DependanceManager');
import('arhframe.markdown.*');
import('arhframe.ResourcesHttp');
import('arhframe.yamlarh.Yamlarh');
import('vendor.erusev.parsedown.Parsedown');
import('arhframe.file.File');
/**
*
*/
class ResourcesManager
{
    private $resources;
    private $resourcesOrig;
    private $extension;
    protected $dirname = null;
    private $isHtml=false;
    private $moduleName = null;
    private $force;
    private $markdownParser;
    private static $staticTyperesources = array('js'=>'js',
                                        'css'=>'css',
                                        'sass'=>'sass',
                                        'scss'=>'scss',
                                        'jpg'=>'image',
                                        'jpeg'=>'image',
                                        'gif'=>'image',
                                        'bmp'=>'image',
                                        'svg'=>'svg',
                                        'png'=>'image',
                                        'ico'=>'image',
                                        'less'=>'less',
                                        'md'=>'markdown',
                                        'markdown'=>'markdown',
                                        'coffee'=>'coffee',
                                        'coffeescript'=>'coffee',
                                        'cs'=>'coffee',
                                        'mp4' =>'video',
                                        'avi' => 'video',
                                        'flv' => 'video',
                                        'mov' => 'video',
                                        'ogv'=> 'video');
    private $typeResources;

    public function __construct($resources=null, $dirname =null)
    {
        $this->typeResources = self::$staticTyperesources;  
        if (empty($resources)) {
            return;
        }
        $this->resourcesOrig = $resources;
        if (ResourcesHttp::isResourcesHttp($resources)) {
            $resources = new ResourcesHttp($resources, $dirname);
            $resources = $resources->getResource();
        }

        $this->markdownParser = Parsedown::instance();
        $this->dirname = $dirname;
        $resources = trim($resources);
        $this->force = DependanceManager::parseForce($resources);

        $resources = DependanceManager::parseForceFileName($resources);
        if (substr($resources, 0, 1) == '/') {
            $resources = substr($resources, 1);
        }

        $this->resources = $resources;
        
        $this->extension = pathinfo($this->resources);
        $this->extension = $this->extension['extension'];
        if (empty($this->dirname)) {

            $force = null;
            if (!empty($this->force)) {
                $force = '@'.$this->force .'/';
            }
            $dependanceManager = DependanceManager::getInstance();
            $moduleName = $dependanceManager->getModuleFromFileName($force .'resources/'.$this->typeResources[strtolower($this->extension)] .'/'. $this->resources);
            $moduleDirectory = MODULE_DIRECTORY;
            if(DependanceManager::isModuleArhframe('@'. $moduleName)){
            	$moduleDirectory = MODULE_DIRECTORY_ARHFRAME;
            	$moduleName = DependanceManager::getModuleFromArhframe('@'.$moduleName);
            }
            $this->dirname = substr($moduleDirectory, 1) . '/'. $moduleName  .'/resources';
            $this->moduleName = $moduleName;
        }
        
    }
    public function getNameFile()
    {
        return $this->resources;
    }
    public function getFolder()
    {
        $folder = dirname(__FILE__) .'/../'. $this->dirname;
        if (empty($this->typeResources[strtolower($this->extension)])) {
            return null;
        }
        $folder .= '/'. $this->typeResources[strtolower($this->extension)];
        if (!is_dir($folder)) {
            return null;
        }

        return '/'. $this->dirname .'/'. $this->typeResources[strtolower($this->extension)];
    }
    public function getResource()
    {
        
        if ($this->typeResources[strtolower($this->extension)] == 'image' && $this->extension!='ico') {
            return eden('image', dirname(__FILE__) .'/..'. $this->getFolder() .'/'. $this->resources, strtolower($this->extension));
        } elseif ($this->typeResources[strtolower($this->extension)] == 'less') {
            try {
                $less = new LessManager($this->resources, $this->moduleName);

                return $less->getFile();
            } catch (Exception $e) {
                throw new ArhframeException($e->getMessage());

            }

        } else {
            return new File(dirname(__FILE__) .'/..'. $this->getFolder() .'/'. $this->resources);
        }
       

    }
    public function getFile()
    {
        return new File(dirname(__FILE__) .'/..'. $this->getFolder() .'/'. $this->resources);
    }
    public function getHtml()
    {
        if ($this->typeResources[strtolower($this->extension)] == 'image'  && $this->extension!='ico') {
            $force = null;
            if (!empty($this->force)) {
                $force = '@'. $this->force .'/';
            }
            $img = new ImageManager($this);
            $img->setHtml(true);

            return $img;
        } elseif ($this->typeResources[strtolower($this->extension)] == 'js') {
            return '<script type="text/javascript" src="'. $this->getHttpFile() .'"></script>';
        }else if($this->typeResources[strtolower($this->extension)] == 'css'
            || $this->typeResources[strtolower($this->extension)] == 'less'
            || $this->typeResources[strtolower($this->extension)] == 'sass'
            || $this->typeResources[strtolower($this->extension)] == 'scss'){
            return '<link href="'. $this->getHttpFile() .'" rel="stylesheet" type="text/css" />';
        } elseif ($this->typeResources[strtolower($this->extension)] == 'markdown') {
            $markdown = cache('markdown', false, false)->get($this->getHttpFile());
            if (empty($markdown)) {
                $markdownFile = new File($this->getHttpFile());
                $markdown = $this->markdownParser->parse($markdownFile->getContent());
                cache('markdown', false, false)->set($this->getHttpFile(), $markdown);
            }

            return $markdown;
        } elseif ($this->typeResources[strtolower($this->extension)] == 'coffee') {
            $coffee = cache('coffee', false, false)->get($this->getHttpFile());
            if (empty($coffee)) {
                $coffeeFile = new File($this->getHttpFile());
                $coffee = CoffeeScript\Compiler::compile($coffeeFile->getContent(), array('filename' => $file));
                cache('coffee', false, false)->set($this->getHttpFile(), $coffee);
            }

            return $coffee;
        } else {
            throw new ArhframeException("Resource '". $this->resources ."' is not supported in html format.");

        }
         
    }
    public function getHttpFile()
    {
        try {
            if ($this->typeResources[strtolower($this->extension)] == 'image'  && $this->extension!='ico' && $this->extension!='svg') {
                $force = null;
                if (!empty($this->force)) {
                    $force = '@'. $this->force .'/';
                }
                try {
                    $img = new ImageManager($this);
                } catch (Exception $e) {
                    throw new Exception("Error Processing Request", $e);
                    
                }
                

                return $img;
            }else if($this->typeResources[strtolower($this->extension)] == 'less'
                || $this->typeResources[strtolower($this->extension)] == 'sass'
                || $this->typeResources[strtolower($this->extension)] == 'scss'){
                $cssCompiler = FactoryCssCompilerManager::getCssCompilerManager(strtolower($this->extension), $this->resources, $this->moduleName);

                return $cssCompiler->getHttpName();
            } elseif ($this->typeResources[strtolower($this->extension)] == 'css') {
                $this->optimizerCss($this->getFolder() .'/'. $this->resources);

                return SERVERNAME . $this->getFolder() .'/'. $this->resources;
            } else if($this->typeResources[strtolower($this->extension)] == 'coffee' || $this->typeResources[strtolower($this->extension)] == 'markdown'){
                return __DIR__ .'/../'.$this->getFolder() .'/'. $this->resources;
            }else {
                return SERVERNAME . $this->getFolder() .'/'. $this->resources;
            }
        } catch (Exception $e) {
            throw new ArhframeException($e->getMessage());

        }

    }
    public function optimizerCss($filename)
    {
        $optimizerCss = new OptimizerCss($filename, $this);
        $optimizerCss->optimizeCss();
    }
    public function setHtml($bool)
    {
        $this->isHtml = $bool;
    }
    public static function doFolder($dirname)
    {
        $folder = new Folder(dirname(__FILE__) .'/../'. $dirname.'/nothing');
        $typeResources = self::$staticTyperesources;
        foreach ($typeResources as $key => $value) {
            $folder->replace($value);
            if ($folder->isFolder()) {
                continue;
            }
            $folder->create();
        }
    }
    public function getModule()
    {
        if (!empty($this->force)) {
            return $this->force;
        }

        return $this->moduleName;
    }
    public function __toString()
    {
        if (!empty($this->isHtml)) {
            return $this->getHtml();
        }

        return $this->getHttpFile();
    }
    public function getResourcesOrig(){
        return $this->resourcesOrig;
    }
    public function __call($name, $arguments)
    {
        $key = strtolower(trim(substr($name, 2)));
        $extension = str_replace('"', '', pathinfo($arguments[0], PATHINFO_EXTENSION));
        $extension = str_replace('\'', '', $extension);

        return strtolower($this->typeResources[$extension]) == $key;

    }
    public function getResourcesFolder($type)
    {
        return $this->typeResources[$type];
    }
}
