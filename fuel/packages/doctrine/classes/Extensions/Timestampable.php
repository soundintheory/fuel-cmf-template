<?php

namespace Doctrine\Fuel\Extensions;

/**
 * Extension to wrap Gedmo's Timestampable behaviour
 */
class Timestampable extends Extension
{
	
	/** @override */
	public static function init(&$config, &$reader, &$event_manager)
	{
		$listener = new \Gedmo\Timestampable\TimestampableListener();
		$listener->setAnnotationReader($reader);
		$event_manager->addEventSubscriber($listener);
	}
	
}