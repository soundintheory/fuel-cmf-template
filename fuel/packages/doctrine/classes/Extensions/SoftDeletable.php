<?php

namespace Doctrine\Fuel\Extensions;

/**
 * Extension to wrap Gedmo's SoftDeletable behaviour
 */
class SoftDeletable extends Extension
{
	
	/** @override */
	public static function init(&$config, &$reader, &$event_manager)
	{
		$listener = new \Gedmo\SoftDeleteable\SoftDeleteableListener();
		$listener->setAnnotationReader($reader);
		$event_manager->addEventSubscriber($listener);
		$config->addFilter('soft-deleteable', 'Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter');
	}
	
}