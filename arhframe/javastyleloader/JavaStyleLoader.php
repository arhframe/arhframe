<?php
require_once dirname(__FILE__) . '/../eden/eden.php';
require_once dirname(__FILE__) . '/../fct_recur.php';
require_once dirname(__FILE__) . '/../exception/ArhframeException.php';
function package($dirName)
{
    return import($dirName . '.*');
}

function import($className)
{
    return JavaStyleLoader::getInstance()->load($className);
}

/**
 *
 */
class JavaStyleLoader
{
    private static $_instance = null;
    private $prepend;
    public $alreadyLoad = array();
    private $filter = array();

    private function __construct()
    {
    }

    public function load($className)
    {
        $className = trim($className);
        if ($this->isLoadingAllFileInDirectory($className)) {
            $classNameOrig = $className;
            if ($this->isLoadingAllFileInDirectoryRecursive($className)) {
                $className = substr($className, 0, strlen($className) - 3);
            } else {
                $className = substr($className, 0, strlen($className) - 2);
            }

            $folder = ROOT . '/' . $this->filePathConvert($className);

            if (!is_dir($folder)) {
                throw new ArhframeException($classNameOrig . " is not a folder, file inside cannot be found.");
            }

            if ($this->isLoadingAllFileInDirectoryRecursive($classNameOrig)) {
                $folder = new Folder($folder);
                $filesName = $folder->getFiles('/\.php$/i', true);
                foreach ($filesName as $name) {
                    $files[] = $name;
                }


            } else {
                $filesName = glob($folder . '/*.php');
                foreach ($filesName as $name) {
                    $files[] = $name;
                }

            }
            foreach ($files as $file) {
                if (!in_array($file, $this->alreadyLoad)) {
                    $this->alreadyLoad[] = $file;
                    require_once $file;
                }
            }

            return $files;
        }
        $file = $this->filePath($className);
        if (empty($file)) {
            throw new ArhframeException($className . " is not found.");
        }
        $file = str_replace('\\', '/', $file);
        if (!in_array($file, $this->alreadyLoad)) {
            $this->alreadyLoad[] = $file;
            require_once $file;
        }

        return array($file);
    }

    public function parseCLassName($className)
    {
        $className = str_replace('.', '\\', $className);

        return $className;
    }

    public function filePath($className)
    {
        $className = $this->parseCLassName($className);
        $className = str_replace('\\', '/', $className);
        if (isset($className[0]) && '@' == $className[0]) {
            if (false === $pos = strpos($className, '/')) {
                throw new ArhframeException(sprintf('Malformed module name "%s" (expecting "@modulename.import_name").', $className));
            }

            $moduleName = substr($className, 1, $pos - 1);
            $className = substr($className, $pos + 1);

            $file = ROOT . DependanceManager::getModuleDirectory($moduleName) . '/' . $className . '.php';
        }
        $file = ROOT . '/' . $className . '.php';
        if (!is_file($file)) {
            return null;
        }

        return $file;
    }

    public function filePathConvert($className)
    {
        $className = str_replace('.', '\\', $className);

        return str_replace('\\', '/', $className);
    }

    public function isLoadingAllFileInDirectory($className)
    {
        if ($className[strlen($className) - 1] == '*') {
            return true;
        }

        return false;
    }

    public function isLoadingAllFileInDirectoryRecursive($className)
    {
        if ($className[strlen($className) - 1] == '*' && $className[strlen($className) - 2] == '*') {
            return true;
        }

        return false;
    }

    public static function getSimpleClassName($className, $namespace = null)
    {
        $simpleClassName = makeArray($className);
        if (empty($namespace)) {
            $class = implode('\\', $simpleClassName);
            if (class_exists($class)) {
                return $class;
            }
        }
        $simpleClassName = $simpleClassName[count($simpleClassName) - 1];

        if (!empty($namespace)) {
            $simpleClassName = $namespace . '\\' . $simpleClassName;
        } else {
            $simpleClassName = '\\' . $simpleClassName;
        }
        if ($className[strlen($className) - 1] == '*'
            || ($className[strlen($className) - 1] == '*' && $className[strlen($className) - 2] == '*')
            || !class_exists($simpleClassName)
        ) {
            throw new ArhframeException($simpleClassName . ' is not a class');
        }

        return $simpleClassName;
    }

    public static function getInstance()
    {
        if (empty(self::$_instance)) {
            self::$_instance = new JavaStyleLoader();
        }

        return self::$_instance;
    }

}
