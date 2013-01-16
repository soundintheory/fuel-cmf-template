<?php

namespace Doctrine\Fuel\Extensions;

/**
 * Extension to wrap Gedmo's Sortable behaviour
 */
class Sortable extends Extension
{
	
	/** @override */
	public static function init(&$config, &$reader, &$event_manager)
	{
		$listener = new \Gedmo\Sortable\SortableListener();
		$listener->setAnnotationReader($reader);
		$event_manager->addEventSubscriber($listener);
	}
	
}