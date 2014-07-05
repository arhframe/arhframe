<?php
use RomaricDrigon\MetaYaml\MetaYaml;

use Symfony\Component\Yaml\Yaml;

import('arhframe.file.File');
import('arhframe.basefile.simpleconst');

/**
 * Yaml wrapper to permit override from module
 * allow import other yaml file by passing @import:
 *                                             - /path/of/your/file
 * allow to pass value directly from key in yaml with %keyYaml%
 * but also variable from scope or constant like this %YOURCONSTANT%
 *  and allow you to inject object in yaml like this: !! your.class(your, parameter)
 *
 * import: @import
 * key variable from yaml file: %keyYaml%
 * variable from scope ($test in scope): %test%
 * constant from scope (define("CONSTANTTEST", "testr")): %CONSTANTTEST%
 * object: !! your.class(your, parameter)
 *
 */
class Yamlarh
{
    private $override = false;
    private $arrayToReturn = array();
    private $fileName;
    private $validator;

    public function __construct($filename, $validator = null)
    {
        $this->validator = $validator;
        $this->fileName = $this->getFileFromDefault($filename);
        try {
            if (preg_match('#^' . preg_quote(ROOT) . '#', $filename)) {
                $this->parseFile(new File($this->fileName));
            } else {
                $this->parseFile(new File(ROOT . $this->fileName));
            }
        } catch (Exception $e) {
            throw new ArhframeException("Error when validating yaml file '" . $this->fileName . "' (" . $e->getMessage() . ")");
        }

        Yamlarh::browseVar($this->arrayToReturn);
    }

    public function parse()
    {
        if (!empty($this->validator)) {
            $validatorSchema = Yaml::parse(__DIR__ . '/validator/' . $this->validator);
            if (!empty($validatorSchema)) {
                $schema = new MetaYaml($validatorSchema); //, true);
                try {
                    $schema->validate($this->arrayToReturn);
                } catch (Exception $e) {
                    throw new ArhframeException("Error when validating yaml file '" . $this->fileName . "' (" . $e->getMessage() . ")");
                }

            }
        }

        return $this->arrayToReturn;
    }

    public static function browseVar(&$arrayToReturn, $completeArray = null)
    {
        if (empty($completeArray)) {
            $completeArray = $arrayToReturn;
        }

        foreach ($arrayToReturn as $key => &$value) {
            if (is_array($value)) {
                Yamlarh::browseVar($value, $completeArray);
            } else {
                $arrayToReturn[$key] = Yamlarh::inject($value, $arrayToReturn, $completeArray);
            }

        }
    }

    public static function inject($value, $arrayToReturn, $completeArray)
    {
        if (!is_string($value)) {
            return $value;
        }
        $value = trim($value);
        if (preg_match('#%([^%]*)%#', $value)) {
            return Yamlarh::insertVar($value, $arrayToReturn, $completeArray);
        }
        if ($value[0] == "!" && $value[1] == "!") {
            return Yamlarh::insertObject($value, $arrayToReturn);
        }

        return $value;
    }

    public static function insertObject($value, $arrayToReturn)
    {
        $value = trim(substr($value, 2));
        preg_match('#\((.*)\)$#', $value, $matchesModule);
        $args = null;
        $value = preg_replace('#\((.*)\)$#', '', $value);
        if (!empty($matchesModule[1])) {
            $args = explode(',', $matchesModule[1]);
            array_walk($args, 'trim_value');
        }

        try {
            import($value);
            $classNameSimple = JavaStyleLoader::getSimpleClassName($value);
        } catch (Exception $e) {
            throw new ArhframeException("Error while processing yaml parsing: '" . $value . "' " . $e->getMessage());
        }
        $object = new ReflectionClass($classNameSimple);
        if (!empty($args)) {
            return $object->newInstanceArgs($args);
        } else {
            return $object->newInstance();
        }

    }

    public static function insertVar($value, $arrayToReturn, $completeArray)
    {

        $value = preg_replace('#%s%#', '%s%%', $value);
        $value = preg_replace('#%s %#', '%s% %', $value);
        preg_match_all('#%([^%]*)%#', $value, $matchesVar);
        $matchesVar = $matchesVar[1];
        $startValue = $value;
        foreach ($matchesVar as $value) {
            if ($value == "s" || ($value[0] == "s" && $value[1] == " ")) {
                $startValue = preg_replace('#%' . preg_quote($value) . '%#', '%s', $startValue);
                continue;
            }
            $varArray = explode('.', $value);
            if (count($varArray) > 1) {
                $finalVar = $completeArray;
                foreach ($varArray as $var) {
                    $finalVar = $finalVar[$var];
                }
                $startValue = preg_replace('#%' . preg_quote($value) . '%#', $finalVar, $startValue);

                continue;
            }
            $var = $arrayToReturn[$value];
            $varFromFile = $$value;
            if (!empty($varFromFile)) {
                $var = $varFromFile;
            }
            if (defined($value)) {
                $var = constant($value);
            }
            try {
                $startValue = preg_replace('#%' . preg_quote($value) . '%#', $var, $startValue);
            } catch (Exception $e) {

            }

        }

        return $startValue;
    }

    private function getFileFromDefault($filename)
    {
        if ($filename == DEFAULTFILE) {
            return $filename;
        }
        $schema = new MetaYaml(Yaml::parse(__DIR__ . '/validator/yamlarh.yml'), true);
        $yamlarh = new Yamlarh(DEFAULTFILE);
        $defaultFile = $yamlarh->parse();
        try {
            $schema->validate($defaultFile);
        } catch (Exception $e) {
            throw new ArhframeException("Error when validating yaml file '" . $this->fileName . "' (" . $e->getMessage() . ")");
        }

        if (empty($defaultFile[$filename])) {
            return $filename;
        }
        if ($defaultFile[$filename]['validation']) {
            if (empty($defaultFile[$filename]['validator'])) {
                $fileInfo = pathinfo($defaultFile[$filename]['filename']);
                $defaultFile[$filename]['validator'] = $fileInfo['filename'] . 'Validator.yml';
            }
            $this->validator = $defaultFile[$filename]['validator'];
        }

        return $defaultFile[$filename]['folder'] . '/' . $defaultFile[$filename]['filename'];
    }

    private function parseFile($file)
    {
        $parsedYml = Yaml::parse($file->getContent());
        if (empty($parsedYml)) {
            return;
        }
        $this->arrayToReturn = array_merge_recursive_distinct($this->arrayToReturn, $parsedYml);
        foreach ($this->arrayToReturn as $key => $value) {
            if ($key == "@import") {
                unset($this->arrayToReturn[$key]);
                if (!is_array($value)) {
                    $this->getFromImport($value, $file);
                } else {
                    foreach ($value as $fileName) {
                        $this->getFromImport($fileName, $file);
                    }
                }

            }
        }
        $this->arrayToReturn = $this->searchForInclude();
    }

    private function searchForInclude(&$arrayYaml = null)
    {
        if (empty($arrayYaml)) {
            $arrayYaml = $this->arrayToReturn;
        }
        $includeYaml = null;
        foreach ($arrayYaml as $key => $value) {
            if (is_array($value) && $key !== '@include') {
                $includeYaml[$key] = $this->searchForInclude($value);
                continue;
            }
            if ($key !== '@include') {
                $includeYaml[$key] = $value;
                continue;
            }
            if (!is_array($value)) {
                $value = array($value);
            }
            $includeYaml = array();
            foreach ($value as $includeFile) {
                $yamlArh = new Yamlarh($includeFile);
                $includeYaml = array_merge($yamlArh->parse(), $arrayYaml, $includeYaml);
            }

            unset($includeYaml['@include']);
        }
        return $includeYaml;
    }

    private function getFromImport($fileName, $file)
    {
        if ($fileName[0] == '/') {
            $fileFinalName = dirname(__FILE__) . '/..' . $fileName;
        } else {
            $fileFinalName = $file->getFolder() . '/' . $fileName;
        }
        $moduleExist = is_file(dirname(__FILE__) . '/../..' . DependanceManager::getModuleDirectory($fileName) . $this->fileName);
        if (!is_file($fileFinalName) && $moduleExist) {

            $fileFinalName = dirname(__FILE__) . '/../..' . DependanceManager::getModuleDirectory($fileName) . $this->fileName;
        } elseif (!is_file($fileFinalName)) {
            throw new ArhframeException("The yml file " . $file->absolute() . " can't found yml file " . $fileName . " for import");
        }
        $this->parseFile(new File($fileFinalName));

    }

    public static function getDefaultFiles()
    {
        $defaultFile = Yaml::parse(DEFAULTFILE);
        $defaultFileName = null;
        foreach ($defaultFile as $name => $value) {
            $defaultFileName[] = $name;
        }

        return $defaultFileName;
    }

    public function loadDependance($dependanceManager)
    {
        $depend = $dependanceManager->getDependance();
        $depend[] = $dependanceManager->getCurrentModule();
        $depend = array_reverse($depend);
        if (!empty($depend)) {
            foreach ($depend as $moduleName) {
                $this->loadYmlFromModule($moduleName);
            }
        }
    }

    public function loadYmlFromModule($moduleName)
    {
        $folder = dirname(__FILE__) . '/../..' . DependanceManager::getModuleDirectory($moduleName);
        if (!is_file($folder . $this->fileName)) {
            return;
        }
        $this->parseFile(new File($folder . $this->fileName));
    }

    public static function dump($array)
    {
        return Yaml::dump($array);
    }

    public function getFilename()
    {
        return $this->fileName;
    }
}
