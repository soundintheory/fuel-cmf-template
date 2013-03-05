<?php

namespace Doctrine\Fuel;

use \Doctrine\DBAL\Types\Type,
	\Doctrine\Common\Persistence\PersistentObject,
	\Doctrine\Common\Annotations\AnnotationRegistry,
	\Doctrine\Common\Annotations\AnnotationReader,
	\Doctrine\ORM\Mapping\Driver\AnnotationDriver,
	\Doctrine\Common\Annotations\CachedReader,
	\Doctrine\ORM\Mapping\Driver\DriverChain,
	\Doctrine\ORM\Configuration,
	\Doctrine\Common\EventManager,
	\Doctrine\Common\ClassLoader,
	\Symfony\Component\Validator\Validator,
	\Symfony\Component\Validator\Mapping\ClassMetadataFactory as ValidatorMetadataFactory,
    \Symfony\Component\Validator\Mapping\Loader\AnnotationLoader as ValidatorAnnotationLoader,
    \Symfony\Component\Validator\ConstraintValidatorFactory;

/**
 * Convenience class to wrap Doctrine configuration with FuelPHP features.
 */
class DoctrineFuel
{
	protected static $initialized = false;
	
	/**
	 * Entity managers stored by connection name
	 * 
	 * @var array
	 */
	protected static $managers;
	
	/**
	 * The Symfony Validator
	 * 
	 * @var \Symfony\Component\Validator\Validator
	 */
	protected static $validator = null;
	
	/**
	 * List of enabled extensions
	 * 
	 * @var array
	 */
	protected static $extensions;
	
	/**
	 * Settings loaded from config
	 * 
	 * @var array
	 */
	protected static $settings;
	
	/**
	 * The globally used cache driver
	 * 
	 * @var \Doctrine\Common\Cache\CacheProvider
	 */
	protected static $cache_driver = null;
	
	/**
	 * The globally used cache reader
	 * 
	 * @var \Doctrine\Common\Annotations\CachedReader
	 */
	protected static $cached_reader = null;
	
	/**
	 * Map cache types to class names
	 * Memcache/Memcached can't be set up automatically the way the other types can, so they're not included
	 * 
	 * @var array
	 */
	protected static $cache_drivers = array(
		'array' => 'ArrayCache',
		'apc' => 'ApcCache',
		'xcache' => 'XcacheCache',
		'wincache' => 'WinCache',
		'zend' => 'ZendDataCache',
		'file' => 'FilesystemCache',
		'phpfile' => 'PhpFileCache'
	);
	
	/**
	 * Map metadata driver types to class names
	 */
	protected static $metadata_drivers = array(
		'annotation' => '', // We'll use the factory method; just here for the exception check
		'php' => 'PHPDriver',
		'simplified_xml' => 'SimplifiedXmlDriver',
		'simplified_yaml' => 'SimplifiedYamlDriver',
		'xml' => 'XmlDriver',
		'yaml' => 'YamlDriver'
	);
	
	/**
	 * Read configuration and add custom mapping types
	 */
	public static function _init()
	{
		static::$settings = \Config::load('doctrine', true);
		static::$initialized = true;
	}
	
	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public static function manager($connection = 'default')
	{
		if (!static::$initialized) static::_init();
		if (!isset(static::$managers[$connection])) static::initManager($connection); 
		return static::$managers[$connection];
	}
	
	/**
	 * Sets up an entity manager for a connection
	 * 
	 * @param  string $connection The name of the connection
	 * @return void
	 */
	protected static function initManager($connection)
	{
		// Config settings etc
		\Config::load('db', true);
		$db_settings = \Config::get('db', true);
		
		$settings = static::$settings;
		$cache = static::cache();
		
		if (!isset($db_settings[$connection]))
			throw new \Exception('No connection configuration for '.$connection);
        
		// Ensure standard doctrine annotations are registered
		AnnotationRegistry::registerFile(
			PKGPATH.'doctrine/vendor/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php'
		);
		
		// Symfony Validator constraints
		AnnotationRegistry::registerAutoloadNamespace(
			'Symfony\\Component\\Validator\\Constraints', PKGPATH.'doctrine/vendor'
		);
		
		// Clear the cache if the query string says so
		if (!is_null(\Input::get('clearcache', null))) {
		    $cache->deleteAll();
		}
		
		// Create the annotation reader and wrap it in a cached reader
		$annotation_reader = new AnnotationReader();
		static::$cached_reader = new CachedReader(
		    $annotation_reader,
		    $cache
		);
		
		// Create a driver chain for metadata reading
		$driver_chain = new DriverChain();
		
		// Initialize the Gedmo extensions
		\Gedmo\DoctrineExtensions::registerAbstractMappingIntoDriverChainORM(
		    $driver_chain,
		    static::$cached_reader
		);
		
		// Set up the driver with the configured paths
		$annotation_driver = new AnnotationDriver(
		    static::$cached_reader,
		    $settings['metadata_path']
		);
		
		// Tell the driver chain to use the configured namespaces
		$namespaces = array_unique(\Arr::get($settings, 'entity_namespaces', array()));
		foreach ($namespaces as $namespace)
		{
			$driver_chain->addDriver($annotation_driver, $namespace);
		}

		// General ORM configuration
		$config = new Configuration;
		$config->setProxyDir($settings['proxy_dir']);
		$config->setProxyNamespace($settings['proxy_namespace']);
		$config->setAutoGenerateProxyClasses($settings['auto_generate_proxy_classes']);
		$config->setMetadataDriverImpl($driver_chain);
		$config->setMetadataCacheImpl($cache);
		$config->setQueryCacheImpl($cache);
		
		// Create event manager and hook preferred extensions
		$evm = new EventManager();
		$evm->addEventSubscriber(new \Doctrine\DBAL\Event\Listeners\MysqlSessionInit());
		$extensions = static::$extensions = \Arr::get($settings, 'extensions', array());
		foreach ($extensions as $extension_class)
		{
			if (!is_subclass_of($extension_class, 'Doctrine\\Fuel\\Extensions\\Extension'))
				throw new \Exception($extension_class.' is not a subclass of Doctrine\\Fuel\\Extensions\\Extension');
			
			$extension_class::init($config, static::$cached_reader, $evm);
		}
		
		// Create the manager
		$db_settings[$connection]['connection']['user'] = $db_settings[$connection]['connection']['username'];
		static::$managers[$connection] = \Doctrine\ORM\EntityManager::create($db_settings[$connection]['connection'], $config, $evm);
		PersistentObject::setObjectManager(static::$managers[$connection]);
		
		static::initTypes($connection);
		
		// register custom types?
		// $platform = static::$managers[$connection]->getConnection()->getDatabasePlatform();
		// $platform->registerDoctrineTypeMapping('varbinary', 'binary');
		
		if (\Arr::get($db_settings, "$connection.profiling", false) === true) {
		    static::$managers[$connection]->getConnection()->getConfiguration()->setSQLLogger(new Logger($connection));
		}
	}
	
	/**
	 * @return \Doctrine\ORM\EntityManager
	 */
	public static function validator()
	{
		if (!static::$initialized) static::_init();
		if (static::$validator === null) static::initValidator();
		
		return static::$validator;
	}
	
	/**
	 * Sets up the Symfony Validator
	 * 
	 * @return void
	 */
	protected static function initValidator()
	{
	    $annotationLoader = new ValidatorAnnotationLoader(static::$cached_reader);
	    $metadataFactory = new ValidatorMetadataFactory($annotationLoader);
	    $validatorFactory = new ConstraintValidatorFactory();
	    
	    static::$validator = new Validator($metadataFactory, $validatorFactory);
	}
	
	/**
	 * @return \Doctrine\Common\Cache\CacheProvider
	 */
	public static function cache()
	{
		if (static::$cache_driver !== null) return static::$cache_driver;
		
		$type = \Arr::get(static::$settings, 'cache_driver', 'array');
		$prefix = \Arr::get(static::$settings, 'cache_prefix', 'cmf');
		
		if (!array_key_exists($type, static::$cache_drivers))
			throw new \Exception('Invalid Doctrine2 cache driver: ' . $type);
		
		$class = '\\Doctrine\\Common\\Cache\\' . static::$cache_drivers[$type];
		
		switch ($type) {
			case 'file':
				$cache = new $class(APPPATH.'cache/doctrine');
				break;
			
			case 'phpfile':
				$cache = new $class(APPPATH.'cache/doctrine');
				break;
				
			default:
				$cache = new $class();
				$cache->setNamespace($prefix);
				break;
		}
		
		return $cache;
		
	}
	
	/**
	 * Adds the configured types
	 * 
	 * @return void
	 */
	protected static function initTypes($connection)
	{
		$platform = static::$managers[$connection]->getConnection()->getDatabasePlatform();
		$types = \Arr::get(static::$settings, 'types', array());
		foreach ($types as $type => $info) {
			Type::addType($type, $info['class']);
			if (isset($info['dbtype'])) $platform->registerDoctrineTypeMapping($info['dbtype'], $type);
		}
	}
	
	/**
	 * @return array Doctrine version information
	 */
	public static function version()
	{
		return array(
			'common' => \Doctrine\Common\Version::VERSION,
			'dbal' => \Doctrine\DBAL\Version::VERSION,
			'orm' => \Doctrine\ORM\Version::VERSION
		);
	}
}
