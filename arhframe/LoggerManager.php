<?php
import('arhframe.Config');
import('arhframe.eden.eden');
import('arhframe.BeanLoader');
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
function logger($name)
{
    $loggerManager = LoggerManager::getInstance();

    return $loggerManager->log($name);
}
class LoggerManager
{
    private $config;
    private $streamHandler;
    private $logger;
    private static $_instance = null;
    private $file;
    private function __construct()
    {
        $this->config = Config::getInstance();
        $this->config = $this->config->logger;
        $level = $this->config->level;
        $this->file = __DIR__ .'/..'. $this->config->path. '/'. LOGGER_FILE;
        $finalLevel = Logger::DEBUG;
        $class = new ReflectionClass("Monolog\Logger");
        $constants = $class->getConstants();
        foreach ($constants as $name => $value) {
            if ($name == $level) {
                $finalLevel = $value;
                break;
            }
        }
        $this->streamHandler = new StreamHandler($this->file, $finalLevel);
    }
    public function removeOldLog()
    {
        $time = strtotime("-". $this->config->maxretention);
        $fileEden = new File($this->file);
        $nameLog = 'LOG_'. date('Y-m-d', $time);
        $savedLogPath = $fileEden->getFolder() .'/'. $nameLog;
        while (is_file($savedLogPath.'.1.'. $fileEden->getExtension())) {
            $i=1;
            while (is_file($savedLogPath.'.'. $i .'.'. $fileEden->getExtension())) {
                unlink($savedLogPath.'.'. $i .'.'. $fileEden->getExtension());
                $i++;
            }
            $time = strtotime("-1 day", $time);
            $nameLog = 'LOG_'. date('Y-m-d', $time);
            $savedLogPath = $fileEden->getFolder() .'/'. $nameLog;
        }
    }
    public function saveOldLog()
    {
        $fileEden = new File($this->file);
        if ($fileEden->getSize()<(1024*512)) {
            return;
        }
        $nameLog = 'LOG_'. date('Y-m-d');
        $savedLogPath = $fileEden->getFolder() .'/'. $nameLog;
        $i=1;
        while (is_file($savedLogPath.'.'. $i .'.'. $fileEden->getExtension())) {
            $i++;
        }
        $nameLogSaved = new File($savedLogPath.'.'. $i .'.'. $fileEden->getExtension());
        $nameLogSaved->setContent($fileEden->getContent());
        $fileEden->setContent('');
    }
    public function log($value)
    {
        $logName = null;
        if (is_object($value)) {
            $logName = get_class($value);
        } elseif (is_array($value)) {
            $logName = serialize($value);
        } else {
            $logName = $value;
        }
        $log = new Logger($logName);
        $debugBarManager = BeanLoader::getInstance()->getBean('arhframe.debugBarManager');
        $debugBarManager->addMonologCollector($log);
        $log->pushHandler($this->streamHandler);

        return $log;
    }

    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
        self::$_instance = new LoggerManager();
        }

        return self::$_instance;
    }
    public function __destruct()
    {
        $this->saveOldLog();
        $this->removeOldLog();
    }
}
