<?php

/**
 * Created by PhpStorm.
 * User: arthurhalet
 * Date: 11/04/14
 * Time: 02:10
 */
class DeployCommand extends ConsoleKit\Command
{
    private $progress;

    public function execute(array $args, array $options = array())
    {
        $box = new \ConsoleKit\Widgets\Box($this->console, 'Deployment');
        $box->write();

        $ioc = new IocArt('/arhframe/ioc/context/deploy.yml');
        $ioc->loadContext();
        $deploy = $ioc->getBean('arhframe.deployApi');
        $deploy->setObserverLoading($this);
        $deploy->deploy();
    }

    public function notify()
    {
        if (empty($this->progress)) {
            $this->progress = new ConsoleKit\Widgets\ProgressBar($this->console, 60, 60);
            $this->progress->setShowRemainingTime(false);
        }
        $this->progress->incr();
        if ($this->progress->getValue() >= 60) {
            $this->progress->stop();
        }
    }
} 