<?php
use League\Flysystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

package('arhframe.deployer.steps');
import('arhframe.yamlarh.Yamlarh');
import('arhframe.exception.*');

/**
 *
 */
class CommandStep extends AbstractStep
{

    public function execute()
    {
        if (empty($this->step['commands'])) {
            throw new ArhframeException("Error in deploy step " . $this->step['number'] . " no commands have been set", 1);
        }
        if (!is_array($this->step['commands'])) {
            $this->step['commands'] = array($this->step['commands']);
        }
        foreach ($this->step['commands'] as $command) {
            echoer("Run command: '" . $command . "'. \n");
            $result = shell_exec($command);
            echoer("Result command:\n$result\n");
        }
    }
}