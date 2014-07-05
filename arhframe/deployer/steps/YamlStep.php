<?php
use League\Flysystem\Filesystem;
use Symfony\Component\Yaml\Yaml;

package('arhframe.deployer.steps');
import('arhframe.yamlarh.Yamlarh');
import('arhframe.exception.*');

/**
 *
 */
class YamlStep extends AbstractStep
{


    public function execute()
    {
        try {
            $yamlarh = new Yamlarh($this->step['file']);
            $value = $yamlarh->parse();
        } catch (Exception $e) {
            throw new ArhframeException("Error in deploy step " . $this->step['number'] . " :", $e->getMessage);
        }
        $value = array_merge_recursive_distinct($value, $this->step['value']);
        $value = Yaml::dump($value, 6);
        $this->getFilesystem()->put($yamlarh->getFilename(), $value);
    }
}