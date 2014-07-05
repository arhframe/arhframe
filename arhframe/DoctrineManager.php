<?php
use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Tools\Export\ClassMetadataExporter;
import('arhframe.Doctrine.ORM.Tools.Setup');
import('arhframe.Doctrine.Common.ClassLoader');
import('arhframe.Config');
import('arhframe.DependanceManager');

/**
*
*/
class DoctrineManager
{
    private $entityManager;
    private $entityManagers;
    private $config;
    private $entitiesFileName = 'entities.yml';
    private $dependanceManager;
    private $fileSch = '/tmp/sch';
    private $classes = array();
    private $folderClasses;
    private $debugBarManager;
    private $bddName;
    private $configMetadata;
    private $debugStack;
    public function __construct()
    {
        $classLoader = new \Doctrine\Common\ClassLoader('Doctrine', __DIR__);
        $classLoader->register();

        $classLoader = new \Doctrine\Common\ClassLoader('Symfony', __DIR__.'/Doctrine');
        $classLoader->register();
        $this->dependanceManager = DependanceManager::getInstance();
        $this->config = Config::getInstance();
        $this->debugStack = new Doctrine\DBAL\Logging\DebugStack();
        // Create a simple "default" Doctrine ORM configuration for XML Mapping

    }
    private function connect()
    {
        $this->fileSch = $this->fileSch . $this->bddName;
        $this->loadEntities();
        if (empty($this->folderClasses)) {
            return;
        }
        $cacheDoctrine = $this->getCacheDoctrine();
        $this->configMetadata = Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration($this->folderClasses, $this->config->config->devmode);
        $this->configMetadata->setMetadataCacheImpl($cacheDoctrine);
        $this->configMetadata->setQueryCacheImpl($cacheDoctrine);
        $bddName = $this->bddName;
        if (empty($this->config->bdd->$bddName)) {
            throw new ArhframeException("Database '$bddName' doesn't exist in config file.");

        }
        $conn = array(
            'driver'   => strtolower($this->config->bdd->$bddName->driver),
            'host'     => $this->config->bdd->$bddName->host,
            'user'     => $this->config->bdd->$bddName->username,
            'password' => $this->config->bdd->$bddName->password,
            'dbname'   => $this->config->bdd->$bddName->dbname,
            'memory'   => (empty($this->config->$bddName->bdd->memory) ? false : $this->config->bdd->$bddName->memory),
            'charset'  => (empty($this->config->config->charset) ? "utf8" : str_replace('-', '', $this->config->config->charset)),
            'path'     => __DIR__ .'/..'.$this->config->bdd->$bddName->path,
        );
        if (!empty($config->bdd->port)) {
            $conn['port'] = $this->config->bdd->$bddName->port;
        }
        // obtaining the entity manager
        $em = \Doctrine\ORM\EntityManager::create($conn, $this->configMetadata);
        $this->entityManager = $em;
        $this->entityManagers[$this->bddName] = $em;
        // database configuration parameters
        if (empty($this->classes)) {
            return;
        }
        $arrayClassMetadata = null;
        foreach ($this->classes as $className) {
            $arrayClassMetadata[] = $this->entityManager->getClassMetadata($className);
        }
        $classes = $arrayClassMetadata;
        if (!empty($classes)) {
            $this->schemaInit($classes);
        }
    }
    private function schemaInit($classes)
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
        $file = __DIR__ . $this->fileSch;
        if (!empty($this->config->config->devmode)) {
            $tool->updateSchema($classes);
            file_put_contents($file, '1');

            return;
        }
        $content = @file_get_contents($file);
        if (!is_file($file) || empty($content)) {
	            $tool->createSchema($classes);
	            file_put_contents($file, '1');

            return;
        }
    }
    public function loadEntities()
    {
        $fileEntities = __DIR__ .'/..'. MODULE_DIRECTORY .'/'. $this->dependanceManager->getCurrentModule() .'/'. $this->entitiesFileName;
        if (!is_file($fileEntities)) {
            return null;
        }
        $entitiesYml = Yaml::parse($fileEntities);
        if (empty($entitiesYml['entities'])) {
            return null;
        }
        $entities = $entitiesYml['entities'];
        $entitiesFile = array();
        foreach ($entities as $entity) {
            $this->loadEntitity($entity);
        }
    }
    private function loadClassFromFile($file, $namespace)
    {
        $namespace = str_replace('.', '\\', $namespace);
        $filePathInfo = pathinfo($file);
        $className = $namespace .'\\'. $filePathInfo['filename'];
        if (!in_array($className, $this->classes)) {
            $this->classes[] = $className;
            $this->folderClasses[] = $filePathInfo['dirname'];
        }
    }
    private function loadEntitity($nameEntity)
    {
    	$isModuleArhframe = false;
    	$moduleDirectory = MODULE_DIRECTORY;
    	if(DependanceManager::isModuleArhframe($nameEntity)){
    		$moduleDirectory = MODULE_DIRECTORY_ARHFRAME;
    		$nameEntity = DependanceManager::getModuleFromArhframe($nameEntity);
    		$moduleName = $nameEntity;
    		$nameEntity = '@'. $nameEntity;
    		$isModuleArhframe = true;
    	}
        $force = DependanceManager::parseForce($nameEntity);
        if (!empty($force)) {
            $moduleName = $force;
        } elseif(!$isModuleArhframe){
            $moduleName = $this->dependanceManager->getCurrentModule();
        }

        $namespace = $moduleName .'.'. str_replace('/', '', ENTITIES_DIRECTORY);
        $moduleDirectory = substr($moduleDirectory, 1);
        $arrayFile = import(str_replace('/', '.', $moduleDirectory) .'.'. $namespace .'.'. DependanceManager::parseForceFileName($nameEntity));
        foreach ($arrayFile as $file) {
            $this->loadClassFromFile($file, $namespace);
        }
    }
    public function getCacheDoctrine()
    {
        if ($this->config->config->devmode) {
            return new \Doctrine\Common\Cache\ArrayCache();
        }
        switch (strtolower(trim($this->cacheConfig['type']))) {
            case 'memcache':
            case 'memcached':
                $cache = new \Doctrine\Common\Cache\MemcacheCache();
                $cache->setMemcache($this->getMemcache());

                return $cache;
            case 'xcache':
                return new \Doctrine\Common\Cache\XcacheCache();
            case 'file':
                return new \Doctrine\Common\Cache\ArrayCache();
            case 'apc':
                return new \Doctrine\Common\Cache\ApcCache();
            default:
                return new \Doctrine\Common\Cache\ArrayCache();
        }
    }
    private function getMemcache()
    {
        $config = Config::getInstance(null, true);
        $servers = $config['cache']['server'];
        if (empty($servers)) {
            throw new ArhframeException("You must specified one server for memcache at least.");
        }
        $currentServer = current($servers);
        $currentServerName = key($servers);
        if (empty($currentServer['port'])) {
            $port = '11211';
        } else {
            $port = (int) $currentServer['port'];
        }
        try {
            $this->verifyConnection($currentServerName, $port);
        } catch (Exception $e) {
            throw new ArhframeException($e->getMessage());
        }
        $memcache = new \Memcache();
        unset($servers[$currentServerName]);
        if (empty($servers)) {
            return;
        }
        foreach ($servers as $serverName => $server) {
            $memcache->addServer($serverName, (int) $server['port'], (boolean) $server['persistent'], (int) $server['cost']);
        }

        return $memcache;
    }
    private function getFirstDbName()
    {
        $tabBddName = (array) Config::getInstance()->bdd;
        if (empty($tabBddName)) {
            return null;
        }
        foreach ($tabBddName as $name => $value) {
            return $name;
        }
    }
    public function getEntityManager($bddName=null)
    {
        if (empty($bddName)) {
            $this->bddName = $this->getFirstDbName();
        } else {
            $this->bddName = $bddName;
        }
        $entityManagers = $this->entityManagers;
        if (!empty($entityManagers[$this->bddName])) {
            $this->entityManager = $entityManagers[$this->bddName];

            return $this->entityManager;
        }
        $this->connect();
        if (!empty($this->entityManager)) {
            $this->entityManager->getConnection()->getConfiguration()->setSQLLogger($this->debugStack);
        }

        return $this->entityManager;
    }
    public function dropSchema()
    {
        $tool = new \Doctrine\ORM\Tools\SchemaTool($this->entityManager);
        $file = __DIR__ . $this->fileSch;
        $content = @file_get_contents($file);
        if (is_file($file) && !empty($content)) {
            file_put_contents($file, '');
            $tool->dropSchema($classes);

            return;
        }
    }
	public function export($exportPath, $bddName=null){
		$em = $this->getEntityManager($bddName);
		$cmf = new DisconnectedClassMetadataFactory();
		$cmf->setEntityManager($em);
		$metadata = $cmf->getAllMetadata();
		$exportPath = ROOT . $exportPath;
		if(!is_dir($exportPath)){
			throw new ArhframeException("Doctrine path for exportation '". $exportPath ."' is not valid");
		}
		$cme = new ClassMetadataExporter();
		$exporter = $cme->getExporter('yml', $exportPath);
		$exporter->setMetadata($metadata);
		$exporter->export();
		echo print_r($em);

	}
    /**
     * @Required
     */
    public function setDebugBarManager(DebugBarManager $debugBarManager)
    {
        $this->debugBarManager = $debugBarManager;
        $this->debugBarManager->addDoctrineCollector($this->debugStack);
    }
    public function getDebugBarManager()
    {
        return $this->debugBarManager;
    }
}
