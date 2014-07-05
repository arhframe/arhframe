<?php
package('arhframe.deployer.steps');

/**
 *
 */
abstract class AbstractStep
{
    protected $step;
    protected $deployerApi;

    function __construct()
    {

    }

    /**
     * @return mixed
     */
    public function getDeployConfig()
    {
        return $this->deployerApi->getDeployConfig();
    }

    /**
     * @param mixed $deployerApi
     */
    public function setDeployerApi($deployerApi)
    {
        $this->deployerApi = $deployerApi;
    }

    /**
     * @return mixed
     */
    public function getDeployerApi()
    {
        return $this->deployerApi;
    }


    /**
     * @return mixed
     */
    public function getFilesystem()
    {
        return $this->deployerApi->getFilesystem();
    }

    /**
     * @param mixed $step
     */
    public function setStep($step)
    {
        $this->step = $step;
    }

    /**
     * @return mixed
     */
    public function getStep()
    {
        return $this->step;
    }

    abstract public function execute();
}