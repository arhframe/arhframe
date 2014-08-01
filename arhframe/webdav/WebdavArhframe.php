<?php

import('arhframe.AbstractControllerArhframe');

/**
 * Created by IntelliJ IDEA.
 * User: arthurhalet
 * Date: 28/07/14
 * Time: 00:39
 *
 * value:
 * path: /to/path/to/show/for/webdav
 */
class WebdavArhframe extends AbstractControllerArhframe
{
    private $dataFolder;

    private function getFsDav()
    {
        $data = $this->getExtractData();
        $path = $data['path'];
        if (empty($path)) {
            throw new \Exception("Webdav need a path");
        }
        if ($path[0] != '/') {
            $path = ROOT . '/' . $path;
        }
        return $path;
    }

    public function buildDir($dir)
    {
        $folder = new Folder($dir);
        $folder->create();
        $folder->append('../dataWebdav/' . $this->getRouter()->getNameRoute());
        $folder->create();
        $this->dataFolder = $folder->absolute();
    }

    public function action()
    {
        $router = $this->getRouter();
        $path = $this->getFsDav();
        $this->buildDir($path);
        $rootDirectory = new \Sabre\DAV\FS\Directory($path);
        $server = new \Sabre\DAV\Server($rootDirectory);
        $servername = dirname($_SERVER['SCRIPT_NAME']);
        if ($servername == '/') {
            $servername = null;
        }

        $server->setBaseUri($servername . $router->getPattern());
        $lockBackend = new \Sabre\DAV\Locks\Backend\File($this->dataFolder . '/locks');
        $lockPlugin = new \Sabre\DAV\Locks\Plugin($lockBackend);
        $server->addPlugin($lockPlugin);

        $server->addPlugin(new \Sabre\DAV\Browser\Plugin());
        $server->exec();
        return null;
    }
} 