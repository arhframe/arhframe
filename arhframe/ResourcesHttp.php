<?php
use Symfony\Component\Yaml\Yaml;
import('arhframe.eden.eden');
import('arhframe.Router');

/**
*
*/
class ResourcesHttp
{
    private static $pattern='#^http(s){0,1}://#';
    private $expireTime = 86400;
    private $typeResources= array('js'=>'js',
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
                                        'cs'=>'coffee');
    private $resource;
    private $dirname = null;
    private $extension;
    public function __construct($resource, $dirname)
    {
        $this->dirname = $dirname;
        $this->resource = $resource;
        $pathinfo = pathinfo($this->resource);

        $this->extension = strtolower($pathinfo['extension']);
        if(empty($this->extension)){
            $this->extension = $this->getTypeFromHeader();
        }
    }
    private function getFolderResources()
    {
        if (!$this->isCorrectResource()) {
            throw new ArhframeException("Resource '". $this->resource ."' is not supported.");
        }
        
        return $this->typeResources[$this->extension];
    }
    public function isCorrectResource()
    {
        
        foreach ($this->typeResources as $key => $value) {
            if ($key==strtolower($this->extension)) {
                return true;
            }
        }

        return false;
    }
    public function getFile()
    {
        $pathinfo = pathinfo($this->resource);
        $filename = $pathinfo['extension'];
        if (!$this->isHttp()) {
            throw new ArhframeException("'". $this->resource ."' is not resource from web.");
        }
        $folder = $this->getFolderFromUrl();
        $file = new File($folder->absolute() .'/'. $pathinfo['basename']);
        if($file->isFile() && $file->getTime() < $file->getTime() + $this->expireTime){
            return $file->absolute();
        }
        $fileResource = new File($this->resource);
        $file->setExtension($this->extension);
        $file->setContent($fileResource->getContent());
        return $file->absolute();
    }
    public function getFolderFromUrl()
    {
        $pathinfo = pathinfo($this->parseUrl());
        if (empty($this->dirname)) {
            $folder = __DIR__ .'/..'.MODULE_DIRECTORY . '/'. Router::getInstance()->getModule() .'/resources/'. $this->getFolderResources(). '/'. $pathinfo['dirname'];
        } else {
            $folder = __DIR__ .'/../'. $this->dirname .'/'.$this->getFolderResources(). '/'. $pathinfo['dirname'];
        }

        $folder = new Folder($folder);
        $folder->create();

        return $folder;
    }
    public function parseUrl()
    {
        if (!$this->isHttp()) {
            return;
        }

        return preg_replace(ResourcesHttp::$pattern, '', $this->resource);
    }
    public function getTypeFromHeader(){
        $headers = get_headers($this->resource ,1);
        $contentType = explode('/', $headers['Content-Type']);
        return $contentType[1];
    }
    public function getResource()
    {
        $this->getFile();
        $parseUrl = $this->parseUrl();
        $pathinfo = pathinfo($parseUrl);
        if(empty($pathinfo['extension'])){
            return $parseUrl .'.'. $this->extension;
        }
        return $parseUrl;
    }
    public static function isResourcesHttp($resource)
    {
        return preg_match(ResourcesHttp::$pattern, trim($resource));
    }
    public function isHttp()
    {
        return ResourcesHttp::isResourcesHttp($this->resource);
    }
}
