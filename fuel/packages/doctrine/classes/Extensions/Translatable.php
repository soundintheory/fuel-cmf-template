<?php

namespace Doctrine\Fuel\Extensions;

/**
 * Extension to wrap Gedmo's Translatable behaviour
 */
class Translatable extends Extension
{
	
	/** @override */
	public static function init(&$config, &$reader, &$event_manager)
	{
		$listener = new \Gedmo\Translatable\TranslatableListener();
		
		// Current translation locale should be set from session or hook later into the listener
		// Most importantly, before the entity manager is flushed
		$listener->setTranslatableLocale('en');
		$listener->setDefaultLocale('en');
		
		$listener->setAnnotationReader($reader);
		$event_manager->addEventSubscriber($listener);
	}
	
}