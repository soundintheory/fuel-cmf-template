<?php

return array(
	
	'auto_generate_proxy_classes' => true,
	'proxy_dir' => APPPATH.'classes/proxy',
	'proxy_namespace' => 'Proxy',
	'metadata_path' => array(),
	
	/**
	 * These are used to filter entities for mapping. Doctrine will use 
	 * strpos($entity_class) === 0 to do this
	 */
	'entity_namespaces' => array(),
	
	/**
	 * Extensions to enable. Use fully qualified class names that extend
	 * the 'Doctrine\Fuel\Extension' class
	 */
	'extensions' => array(
		//'Doctrine\\Fuel\\Extensions\\Tree',
		//'Doctrine\\Fuel\\Extensions\\Timestampable',
		//'Doctrine\\Fuel\\Extensions\\Sortable',
		//'Doctrine\\Fuel\\Extensions\\Spatial',
		//'Doctrine\\Fuel\\Extensions\\Translatable',
		//'Doctrine\\Fuel\\Extensions\\Loggable',
		//'Doctrine\\Fuel\\Extensions\\SoftDeletable'
	),
	
	/**
	 * You can map additional types here. Use fully qualified class names
	 * that extend the 'Doctrine\DBAL\Types\Type' class
	 */
	'types' => array(
		'binary' => array( 'class' => 'Doctrine\\Fuel\\Types\\Binary', 'dbtype' => 'varbinary' )
		//'point' => array( 'class' => 'Doctrine\\Fuel\\Types\\Point', 'dbtype' => 'point' )
	)
	
);