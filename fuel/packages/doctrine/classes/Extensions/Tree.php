<?php

namespace Doctrine\Fuel\Extensions;

/**
 * Extension to wrap Gedmo's Tree behaviour
 */
class Tree extends Extension
{
	
	/** @override */
	public static function init(&$config, &$reader, &$event_manager)
	{
		$listener = new \Gedmo\Tree\TreeListener();
		$listener->setAnnotationReader($reader);
		$event_manager->addEventSubscriber($listener);
	}
	
}