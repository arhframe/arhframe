<?php
import('arhframe.eden.eden');
import('arhframe.basefile.const');
import('arhframe.compressor.*');
import('arhframe.Router');
/**
*
*/
class Compressor
{
    private $router;
    private static $dirJs;
    private static $dirCss;
    private $config;
    public function __construct($dirJs = null, $dirCss=null)
    {
        $this->router = Router::getInstance();
        $this->config = Config::getInstance();
        if (empty($dirJs)) {
            Compressor::$dirJs = MODULE_DIRECTORY .'/'. $this->router->getModule() .'/resources/js';
        } else {
            Compressor::$dirJs =  $dirJs;
        }
        if (empty($dirCss)) {
            Compressor::$dirCss = MODULE_DIRECTORY .'/'. $this->router->getModule() .'/resources/css';
        } else {
            Compressor::$dirCss = $dirCss;
        }

    }
    public function compressJs(array $files=null)
    {
        $folder = new Folder(dirname(__FILE__).'/../..'. Compressor::$dirJs);
        if (empty($files)) {
            $files = $folder->getFiles('/\.js$/', true);
        }
        $folders = $folder->getFolders();
        foreach ($files as $key => $value) {
            if ($time<$value->getTime()) {
                $time = $value->getTime();
            }
            $jsCompress .= $value->getContent(); ;
        }
        $file = new File(dirname(__FILE__).'/../..'. Compressor::$dirJs .'/compressjs.js');
        if (empty($jsCompress) && $file->isFile()) {
            $file->remove();

            return;
        }
        if ($file->isFile() && $time<=$file->getTime()) {
            return;
        }
        if (empty($jsCompress)) {
            return;
        }
        if ($this->config->assetics->yuglify->installed) {
           $file->setContent('');
           $fileTmp = new File(dirname(__FILE__).'/../..'.Compressor::$dirCss .'/compressjstmp.css');
           $fileTmp->setContent($cssCompress);
           exec('tail '. $fileTmp->absolute() .' | yuglify --terminal --type js -o '. $file->absolute());
           unlink($fileTmp->absolute());

           return;
        } else {
            $packer = new JavaScriptPacker($jsCompress, 'Normal', true, false);
            $packed = $packer->pack();
        }

        $file->setContent($packed);
    }
    public function compressCss(array $files=null)
    {
        $folder = new Folder(dirname(__FILE__).'/../..'. Compressor::$dirCss);
        if (empty($files)) {
            $files = $folder->getFiles('/\.css$/', true);
        }

        $folders = $folder->getFolders();
        foreach ($files as $key => $value) {
            if ($time<$value->getTime()) {
                $time = $value->getTime();
            }
            $cssCompress .= $value->getContent();
        }
        $file = new File(dirname(__FILE__).'/../..'.Compressor::$dirCss .'/compresscss.css');
        if (empty($cssCompress) && $file->isFile()) {
            $file->remove();

            return;
        }
        if ($file->isFile() && $time<=$file->getTime()) {
              return;
        }
        if (empty($cssCompress)) {
            return;
        }
        if ($this->config->assetics->yuglify->installed) {
           $file->setContent('');
           $fileTmp = new File(dirname(__FILE__).'/../..'.Compressor::$dirCss .'/compresscsstmp.css');
           $fileTmp->setContent($cssCompress);
           exec('tail '. $fileTmp->absolute() .' | yuglify --terminal --type css -o '. $file->absolute());
           unlink($fileTmp->absolute());

           return;
        } else {
            $compressor = new CSSmin();
            $packed = $compressor->run($cssCompress);
        }
        $file->setContent($packed);
    }
    public static function compress()
    {
        $compressor = new Compressor();
        $compressor->compressJs();
        $compressor->compressCss();
    }
    public function getCssCompressFile(array $filnames=null)
    {
        $this->compressCss($filnames);
        $file = new File( dirname(__FILE__) .'/../..'. Compressor::$dirCss .'/compresscss.css');
        if (!$file->isFile()) {
            return null;
        }

        return SERVERNAME . Compressor::$dirCss .'/compresscss.css';
    }
    public function getJsCompressFile(array $filnames=null)
    {
        $this->compressJs($filnames);
        $router = Router::getInstance();
        $file = new File(dirname(__FILE__).'/../..'. Compressor::$dirJs .'/compressjs.js');
        if (!$file->isFile()) {
            return null;
        }

        return SERVERNAME . Compressor::$dirCss .'/compressjs.js';
    }
    public function getCssCompressHtml(array $filnames=null)
    {
        $link = $this->getCssCompressFile($filnames);
        if (empty($link)) {
            return null;
        }

        return '<link href="'. $link .'" rel="stylesheet" type="text/css" />';
    }
    public function getJsCompressHtml(array $filnames=null)
    {
        $link = $this->getJsCompressFile($filnames);
        if (empty($link)) {
            return null;
        }

        return '<script type="text/javascript" src="'. $link .'"></script>';
    }
}
