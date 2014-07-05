<?php
use League\Flysystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

package('arhframe.deployer.steps');
import('arhframe.yamlarh.Yamlarh');
import('arhframe.exception.*');

/**
 *
 */
class FileStep extends AbstractStep
{

    public function execute()
    {
        $this->getFilesystem()->put($this->step['file'], $this->step['value']);
    }
}