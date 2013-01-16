<?php

namespace Doctrine\Fuel\Extensions;

use \Doctrine\DBAL\Types\Type;

/**
 * Extension to add the MySQL point type and some related functions
 */
class Spatial extends Extension
{
	
	/** @override */
	public static function init(&$config, &$reader, &$event_manager)
	{
		$config->addCustomNumericFunction('DISTANCE', 'Doctrine\\Fuel\\Spatial\\Distance');
		$config->addCustomNumericFunction('POINT_STR', 'Doctrine\\Fuel\\Spatial\\PointStr');
	}
	
}