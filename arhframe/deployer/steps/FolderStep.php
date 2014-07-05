<?php
use League\Flysystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

package('arhframe.deployer.steps');
import('arhframe.yamlarh.Yamlarh');
import('arhframe.exception.*');

/**
 *
 */
class FolderStep extends AbstractStep
{

    public function execute()
    {
        $this->stepFolderCreate($this->step);
        $this->stepFolderRemove($this->step);
    }

    private function stepFolderCreate($step)
    {
        if (empty($step['create'])) {
            return;
        }
        if (!is_array($step['create'])) {
            $step['create'] = array($step['create']);
        }
        foreach ($step['create'] as $file) {
            $this->getFilesystem()->createDir($file);
        }
    }

    private function stepFolderRemove($step)
    {
        if (empty($step['remove'])) {
            return;
        }
        if (!is_array($step['remove'])) {
            $step['remove'] = array($step['remove']);
        }
        foreach ($step['remove'] as $file) {
            $this->getFilesystem()->deleteDir($file);
        }
    }
}