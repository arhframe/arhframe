<?php
use Ikimea\Browser\Browser;

import('arhframe.eden.eden');
import('arhframe.ResourcesManager');
import('arhframe.basefile.const');
import('arhframe.Config');
import('arhframe.DeviceManager');
import('arhframe.cache.CacheManager');
import('arhframe.file.File');

/**
 *
 */
class ImageManager
{
    private $resourcesManager;
    private $file;
    private $image;
    private $imageName;
    private $extensionValid = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'ico');
    private $dirty = false;
    private $favicon = false;
    private $config;
    private $isHtml = false;
    private $cache;
    private $type = 'png';
    private $folderAbsolute;
    private $folderName;
    private $registerMethod;
    private $imageFolder;
    private $encode2Base64 = true;

    public function __construct($imageName)
    {

        $this->cache = cache($this, false);
        $this->config = Config::getInstance();
        $this->config = $this->config->config;
        if (is_object($imageName)) {
            $this->resourcesManager = $imageName;
        } else {
            $this->resourcesManager = new ResourcesManager($imageName);
        }

        $this->file = $this->resourcesManager->getFile();
        $this->imageFolder = $this->resourcesManager->getResourcesFolder($this->type);

        if ($this->cache->isCacheActive()) {
            $this->folderAbsolute = $this->cache->getCacheFolder();
            if (!is_dir($this->folderAbsolute . '/image')) {
                mkdir($this->folderAbsolute . '/image');
            }
            $this->folderName = $this->cache->getCacheFolderName();
        } else {
            $this->folderAbsolute = dirname(__FILE__) . '/..' . $this->resourcesManager->getFolder();
            $this->folderName = $this->resourcesManager->getFolder();
        }
        if (!in_array(strtolower($this->file->getExtension()), $this->extensionValid)) {
            throw new ArhframeException($imageName + " is not an image");
        }

        $this->imageName = new File($this->resourcesManager->getNameFile());

        $this->image = eden('image', $this->file->absolute(), strtolower($this->file->getExtension()));
    }

    public function setName($data)
    {
        $this->imageName = new File($this->imageName->getFolder() . '/' . $this->imageName->getBase() . $data . '.' . $this->type);
    }

    public function save()
    {
        if ($this->imageName->isFile() && $this->file->getTime() == $this->getFile()->getTime()) {
            $this->image = eden('image', $this->imageName->absolute(), strtolower($this->imageName->getExtension()));
            return;
        }
        $file = new File($this->folderAbsolute . '/' . $this->imageFolder . '/' . $this->resourcesManager->getModule() . $this->imageName->getFolder() . '/' . $this->imageName->getName());

        if ($file->isFile() && $file->getTime() <= $this->file->getTime()) {
            $this->image = eden('image', $file->absolute(), strtolower($file->getExtension()));
            return;
        }
        $this->doRegister();
        $this->file->touch();
        $folder = new Folder($this->folderAbsolute . '/' . $this->imageFolder . '/' . $this->resourcesManager->getModule() . $this->imageName->getFolder());
        $folder->create();
        $type = $this->type;

        $this->image->save($this->folderAbsolute . '/' . $this->imageFolder . '/' . $this->resourcesManager->getModule() . $this->imageName->getFolder() . '/' . $this->imageName->getName(), $type);
        $this->dirty = false;
    }

    private function register($value)
    {
        $this->registerMethod[] = $value;
    }

    private function doRegister()
    {
        if (empty($this->registerMethod)) {
            return;
        }
        $registerMethod = $this->registerMethod;
        foreach ($registerMethod as $method) {
            eval($method);
        }
    }

    public function crop($w = null, $h = null)
    {
        $this->dirty = true;
        if ($w <= 0 || !is_numeric($w)) {
            $w = 'null';
        }
        if ($h <= 0 || !is_numeric($h)) {
            $h = 'null';
        }
        $this->setName("_c" . $w . 'x' . $h);
        $this->register('$this->image->crop(' . $w . ', ' . $h . ');');

        return $this;
    } // Crops an image
    public function scale($w = null, $h = null)
    {
        $this->dirty = true;
        if ($w <= 0 || !is_numeric($w)) {
            $w = 'null';
        }
        if ($h <= 0 || !is_numeric($h)) {
            $h = 'null';
        }
        $this->setName("_s" . $w . 'x' . $h);
        $this->register('$this->image->scale(' . $w . ', ' . $h . ');');

        return $this;
    } // Scales an image
    public function resize($w = null, $h = null)
    {
        $this->dirty = true;
        if ($w <= 0 || !is_numeric($w)) {
            $w = 'null';
        }
        if ($h <= 0 || !is_numeric($h)) {
            $h = 'null';
        }
        $this->setName("_r" . $w . 'x' . $h);
        $this->register('$this->image->resize(' . $w . ', ' . $h . ');');

        return $this;
    } // Scales an image while keeping aspect ration
    public function rotate($r = 0)
    {
        if (!is_numeric($r)) {
            $r = 0;
        }
        $this->dirty = true;
        $this->setName("_rot" . $r);
        $this->register('$this->image->rotate(' . $r . ');');

        return $this;
    } // Rotates image
    public function invertH()
    {
        $this->dirty = true;
        $this->setName("_invertH");
        $this->register('$this->image->invert();');

        return $this;
    } // Invert horizontal
    public function invertV()
    {
        $this->dirty = true;
        $this->setName("_invertV");
        $this->register('$this->image->invert(true);');

        return $this;
    } // Invert vertical
    public function greyscale()
    {
        $this->dirty = true;
        $this->setName("_greyscale");
        $this->register('$this->image->greyscale();');

        return $this;
    }

    public function negative()
    {
        $this->dirty = true;
        $this->setName("_negative");
        $this->register('$this->image->negative();');

        return $this;
    } // inverses all the colors
    public function brightness($value = 0)
    {
        if (!is_numeric($value)) {
            $value = 0;
        }
        $this->dirty = true;
        $this->setName("_bri" . $value);
        $this->register('$this->image->brightness(' . $value . ');');

        return $this;
    }

    public function contrast($value = 0)
    {
        if (!is_numeric($value)) {
            $value = 0;
        }
        $this->dirty = true;
        $this->setName("_contrast" . $value);
        $this->register('$this->image->contrast(' . $value . ');');

        return $this;
    }

    public function colorize($r = 0, $g = 0, $b = 0)
    {
        if (!is_numeric($r)) {
            $r = 0;
        }
        if (!is_numeric($g)) {
            $g = 0;
        }
        if (!is_numeric($b)) {
            $b = 0;
        }
        $this->dirty = true;
        $this->setName("_color" . $r . "x" . $g . "x" . $b);
        $this->register('$this->image->colorize(' . $r . ', ' . $g . ', ' . $b . ');');

        return $this;
    } // colorize to blue (R, G, B)
    public function edgedetect()
    {
        $this->dirty = true;
        $this->setName("_edge");
        $this->register('$this->image->edgedetect();');

        return $this;
    } // highlight edges
    public function emboss()
    {
        $this->dirty = true;
        $this->setName("_emboss");
        $this->register('$this->image->emboss();');

        return $this;
    }

    public function gaussianBlur()
    {
        $this->dirty = true;
        $this->setName("_gblur");
        $this->register('$this->image->gaussianBlur();');

        return $this;
    }

    public function blur()
    {
        $this->dirty = true;
        $this->setName("_blur");
        $this->register('$this->image->blur();');

        return $this;
    }

    public function meanRemoval()
    {
        $this->dirty = true;
        $this->setName("_mremoval");
        $this->register('$this->image->meanRemoval();');

        return $this;
    } // achieve a "sketchy" effect.
    public function smooth($value = 0)
    {
        if (!is_numeric($value)) {
            $value = 0;
        }
        $this->dirty = true;
        $this->setName("_smooth" . $value);
        $this->register('$this->image->smooth(' . $value . ');');

        return $this;
    }

    public function setTransparency($value = 0)
    {
        if (!is_numeric($value)) {
            $value = 0;
        }
        $this->dirty = true;
        $this->setName("_t" . $value);
        $this->register('$this->image->setTransparency(' . $value . ');');

        return $this;
    } // set the transparent color
    public function getImageName()
    {
        $this->responsive();
        $this->save();

        return $this->folderAbsolute . '/' . $this->imageFolder . '/' . $this->resourcesManager->getModule() . $this->imageName->getFolder() . '/' . $this->imageName->getName();
    }

    private function responsive()
    {
        if (!$this->config->responsive) {
            return;
        }
        $device = DeviceManager::getInstance();
        $device->init();
        if ($device->getWidth() <= 0 && $device->height() <= 0) {
            return;
        }
        if ($this->dirty) {
            return;
        }
        $this->resize($device->getWidth(), $device->height());
    }

    public function setHtml($bool)
    {
        $this->isHtml = $bool;
    }

    public function getFile()
    {
        $this->responsive();
        $this->save();

        return new File($this->folderAbsolute . '/' . $this->imageFolder . '/' . $this->resourcesManager->getModule() . $this->imageName->getFolder() . '/' . $this->imageName->getName());
    }

    public function getHttpImageName()
    {
        $this->responsive();
        $this->save();

        return SERVERNAME . $this->folderName . '/' . $this->imageFolder . '/' . $this->resourcesManager->getModule() . $this->imageName->getFolder() . '/' . urlencode($this->imageName->getName());
    }

    public function getFinalImage(&$isEncode = false)
    {
        $file = $this->getFile();
        $browser = new Browser();
        //encode image in base64 if the browser is not IE7 or less
        if ($file->getSize() <= 1024 && ($browser->getBrowser() != $browser::BROWSER_IE ||
                ($browser->getBrowser() == $browser::BROWSER_IE && $browser->getVersion() > 7)) && $this->encode2Base64
        ) {
            $isEncode = true;
            $img2base64 = base64_encode($file->getContent());
            $pathinfo = pathinfo($file->absolute());

            $imageField = 'data:image/' . strtolower($pathinfo['extension']) . ';base64,' . $img2base64;
        } else {
            $imageField = $this->getHttpImageName();
        }

        return $imageField;
    }

    public function getHtml()
    {

        if ($this->favicon) {
            return '<link rel="icon" type="image/x-icon" href="' . $this->getFinalImage() . '" />';
        }
        $text = '<img src="' . $this->getFinalImage()
            . '" alt="' . basename($this->getHttpImageName());
        $dimensions = $this->image->getDimensions();

        $text .= '" width="' . $dimensions[0] . '" height="' . $dimensions[1] . '"/>';
        return $text;
    }

    public function forToString()
    {

        if (!empty($this->isHtml)) {
            return $this->getHtml();
        } else {
            return $this->getHttpImageName();
        }
    }

    public function getType()
    {
        return $this->type;
    }

    public function noEncode2Base64()
    {
        $this->encode2Base64 = false;
        return $this;
    }

    public function setType($type)
    {
        $type = strtolower($type);
        if (!in_array($type, $this->extensionValid)) {
            throw new ArhframeException("Not a valid image type for '" . $type
                . "' use one of this: " . implode(',', $this->extensionValid));
        }
        $this->type = $type;
    }

    public function __toString()
    {
        return $this->forToString();


    }
}
