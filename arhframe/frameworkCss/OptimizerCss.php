<?php
package('arhframe.frameworkCss');
import('arhframe.eden.eden');
import('arhframe.ResourcesManager');
import('arhframe.ResourcesHttp');
/**
*
*/
class OptimizerCss
{
    const OPTIMIZED ="/*OPTIMIZED BY ARHFRAME*/\n";
    private $resourcesManager;
    private $fileName;
    public function __construct($fileName, $resourcesManager)
    {
        $this->fileName = $fileName;
        $this->resourcesManager = get_class($resourcesManager);

    }
    public function optimizeCss()
    {
        $fileName = __DIR__ .'/../..'. $this->fileName;
        $f = fopen($fileName, 'r');
        $line = fgets($f);
        fclose($f);
        if ($line==OptimizerCss::OPTIMIZED) {
            return;
        }
        $file = new File($fileName);
        //var_dump($file->getFolder());
        //die();
        $oParser = new Sabberworm\CSS\Parser($file->getContent());
        $resourcesManagerClass = $this->resourcesManager;
        $oCss = $oParser->parse();
        foreach ($oCss->getAllValues() as $mValue) {
            if (!($mValue instanceof Sabberworm\CSS\Value\URL)) {
                continue;
            }
            $resourcesManager = new $resourcesManagerClass();
            $url = trim($mValue->getURL()->__toString());
            $url = str_replace('"', '', $url);
            $url = str_replace('\'', '', $url);
            if ($url[0]=='/') {
                continue;
            }
            if (!$resourcesManager->isImage($url)) {
                continue;
            }
            if(!ResourcesHttp::isResourcesHttp($url)){
                $filetmp = new File($url);
                $folder = new Folder($filetmp->getFolder());
                $arrayFolder = $folder->getArray();
                $folder->popReverse();
                for ($i=0; $i < count($arrayFolder); $i++) { 
                    try {
                        $resourcesManager = new $resourcesManagerClass($folder->absolute() .'/'. $filetmp->getName());
                    } catch (Exception $e) {
                        $resourcesManager = null;
                        $folder->popReverse();
                        continue;
                    }
                }   
            }else{
                $resourcesManager = new $resourcesManagerClass($url);
            }
            if(empty($resourcesManager)){
                return;
            }
            //var_dump($resourcesManager->getHttpFile()->getFinalImage());
            $url = new Sabberworm\CSS\Value\String($resourcesManager->getHttpFile()->getFinalImage($isEncode));
            if ($isEncode) {
                $mValue->setURL($url);
            }
        }
        $file->setContent(OptimizerCss::OPTIMIZED .$oCss->__toString());

    }
}
