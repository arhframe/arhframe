<?php
import('arhframe.LoggerManager');
import('arhframe.Config');
/**
*
*/
class SecurityIssueIdentifier
{
    private $exposeManager;
    private static $_instance = null;
    private $secureConfig;
    private function __construct()
    {
        $this->secureConfig = Config::getInstance();
        $this->secureConfig = $this->secureConfig->secure;

    }
    private function setRestrictions()
    {
        $restrictions = (array) $this->secureConfig->restrictions;
        if (empty($restrictions)) {
            return;
        }
        $this->exposeManager->setRestriction($restrictions);
    }
    private function setExceptions()
    {
        $exceptions = (array) $this->secureConfig->exceptions;
        if (empty($exceptions)) {
            return;
        }
        $this->exposeManager->setException($exceptions);
    }
    private function setTreshold()
    {
        $this->exposeManager->setThreshold($this->secureConfig->securitythreshold);
    }
    public function identify(array $data, $dataName=null)
    {
        if (!$this->secureConfig->issueidentifier) {
            return;
        }
        $filters = new \Expose\FilterCollection();
        $filters->load();
        $this->exposeManager = new \Expose\Manager($filters);
        $this->setTreshold();
        $this->setRestrictions();
        $this->setExceptions();
        if (empty($dataName)) {
            $dataName = key($data);
            $finalData = $data;
        } else {
            $finalData[$dataName] = $data;
        }
        $logger = logger($dataName.'.Vulnerability');
        $this->exposeManager->setLogger($logger);
        $this->exposeManager->run($finalData);
        $export = $this->exposeManager->export();
        if (empty($export)) {
            return;
        }
        $logger->addWarning($export."\nFrom IP: ". $_SERVER['REMOTE_ADDR']);
    }
    public static function getInstance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new SecurityIssueIdentifier();
        }

        return self::$_instance;
    }
}
