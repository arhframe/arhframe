<?php
use League\Flysystem\Filesystem;
use Symfony\Component\Yaml\Yaml;
use ConsoleKit\Widgets\ProgressBar;
use ConsoleKit\Widgets\Box;

ignore_user_abort(true);
set_time_limit(0);
package('arhframe.deployer');
import('arhframe.deployer.steps.*');
import('arhframe.file.*');
import('arhframe.exception.*');
import('arhframe.basefile.const');
import('arhframe.yamlarh.Yamlarh');

/**
 *
 */
class DeployerApi
{
    private $filesToUpload = null;
    private $deployConfig;
    private $filesystem;
    private $startTime;
    private $loaded = 0;
    private $ssh;
    private $steppers;
    private $observerLoading;

    public function __construct()
    {

    }

    public function deployBegin()
    {
        $this->startTime = microtime(true);
        $this->echoer("\nDeployment start at " . date('H:i:s') . ".\n");
        $this->echoer("Get all files to deploy.\n");
        $folder = new Folder(__DIR__ . '/../..');
        $yamlarh = new Yamlarh(DEPLOYFILENAME);
        $this->deployConfig = $yamlarh->parse();
        $files = $folder->getFiles('/.*/', true);
        foreach ($files as $file) {
            $this->filesToUpload[] = $file->absolute();
        }
        $this->echoer("Filtering files with ignore list.\n");
        $this->removeIgnoredFile();

        if (trim(strtolower($this->deployConfig['filesystem']['type'])) == 'sftp') {
            if (empty($this->deployConfig['filesystem']['port'])) {
                $this->deployConfig['filesystem']['port'] = 22;
            }
        }
        $factory = new AdapterFactory($this->deployConfig['filesystem']['type'], $this->deployConfig['filesystem']);
        $this->filesystem = new Filesystem($factory->getInstance());
        foreach ($this->filesToUpload as $key => $value) {
            $value = str_replace(__DIR__ . '/../..', '', $value);
            $this->filesToUpload[$key] = $value;
        }

        $this->echoer("Files can be uploaded.\n");
        $this->echoer("===========================================================\n");
    }

    private function removeIgnoredFile()
    {
        $folder = new Folder(__DIR__ . '/../../');
        foreach ($this->deployConfig['ignore'] as $ignore) {
            $ignore = trim($ignore);
            if ($ignore[0] == '/') {
                $ignore = __DIR__ . '/../..' . $ignore;
            } else {
                $ignore = __DIR__ . '/../../' . $ignore;
            }
            $this->removeFromFolder(rglob($ignore));
        }
    }

    private function removeFromFolder($files)
    {
        if (empty($files)) {
            return;
        }
        $toUnset = null;
        foreach ($files as $file) {
            foreach ($this->filesToUpload as $key => $fileUpload) {
                if ($fileUpload == $file) {
                    $toUnset[] = $key;
                    break;
                }
            }
        }
        if (empty($toUnset)) {
            return;
        }

        foreach ($toUnset as $value) {
            unset($this->filesToUpload[$value]);
        }
    }

    public function doSteps()
    {
        if (!is_array($this->deployConfig['steps'])) {
            return;
        }
        $i = 1;
        foreach ($this->deployConfig['steps'] as $step) {
            if ($i > 1) {
                $this->echoer("------\n");
            }
            if (!empty($step['skip'])) {
                $this->echoer("Step $i skipped.\n");
                $i++;
                continue;
            }
            $this->echoer("Start step $i.\n");
            $step['type'] = strtolower($step['type']);
            $step['number'] = $i;
            if (!empty($this->steppers[$step['type']])
                && $this->steppers[$step['type']] instanceof AbstractStep
            ) {
                $this->steppers[$step['type']]->setStep($step);
                $this->steppers[$step['type']]->execute();
            }

            $this->echoer("Step $i finished.\n\n");

            $i++;
        }
        $this->echoer("===========================================================\n");
    }


    public function loading($percent)
    {
        $percent = (int)$percent;

        $load = (int)($percent * 60 / 100);
        $toload = $load - $this->loaded;

        for ($i = 0; $i < $toload; $i++) {
            if (empty($this->observerLoading)) {
                $this->echoer("=");
            } else {
                $this->observerLoading->notify();
            }
        }
        $this->loaded = $load;
    }

    public function deploy()
    {
        $this->deployBegin();
        $this->echoer("Start deployment with file system: \n" . Yaml::dump($this->deployConfig['filesystem']) . "\n");
        $i = 1;
        $final = count($this->filesToUpload);
        $this->echoer("Uploading files... \n");
        foreach ($this->filesToUpload as $key => $file) {
            $this->loading($i * 100 / $final);
            if ($this->isModified($file)) {
                $localFile = new File(__DIR__ . '/../..' . $file);
                $this->filesystem->put($file, $localFile->getContent());
            }
            $i++;
        }
        $this->echoer("Upload in file system finished.\n");
        $this->echoer("===========================================================\n");
        $this->doSteps();
        $this->echoer("Deployment finished in " . formatDuration(microtime(true) - $this->startTime) . " at " . date('H:i:s') . ".");

    }

    private function echoer($string)
    {
        echo $string;
        flush();
    }

    private function isModified($file)
    {
        $localFile = new File(__DIR__ . '/../..' . $file);
        return $localFile->getSize() != $this->filesystem->getSize($file);
    }

    /**
     * @param mixed $filesystem
     */
    public function setFilesystem($filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * @return mixed
     */
    public function getFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * @param mixed $deployConfig
     */
    public function setDeployConfig($deployConfig)
    {
        $this->deployConfig = $deployConfig;
    }

    /**
     * @return mixed
     */
    public function getDeployConfig()
    {
        return $this->deployConfig;
    }

    /**
     * @Required
     */
    public function setSteppers(array $steppers)
    {
        $this->steppers = $steppers;
        foreach ($steppers as $stepper) {
            if ($stepper instanceof AbstractStep) {
                $stepper->setDeployerApi($this);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getSteppers()
    {
        return $this->steppers;
    }

    /**
     * @param mixed $observerLoading
     */
    public function setObserverLoading($observerLoading)
    {
        $this->observerLoading = $observerLoading;
    }

    /**
     * @return mixed
     */
    public function getObserverLoading()
    {
        return $this->observerLoading;
    }

}
