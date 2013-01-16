<?php

namespace Doctrine\Fuel\Extensions;

/**
 * Extension to wrap Gedmo's Loggable behaviour
 */
class Loggable extends Extension
{
	
	/** @override */
	public static function init(&$config, &$reader, &$event_manager)
	{
		$listener = new \Gedmo\Loggable\LoggableListener();
		$listener->setAnnotationReader($reader);
		$event_manager->addEventSubscriber($listener);
	}
	
}