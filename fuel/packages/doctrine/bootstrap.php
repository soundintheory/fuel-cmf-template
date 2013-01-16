<?php

$dir = dirname(__FILE__).DS;

// Add all the required classes
Autoloader::add_namespace('Doctrine', $dir.'vendor/Doctrine/', true);
Autoloader::add_namespace('Gedmo', $dir.'vendor/Gedmo/', true);
Autoloader::add_namespace('Doctrine\\Fuel', $dir.'classes/', true);
Autoloader::add_namespace('Symfony', $dir.'vendor/Symfony/', true);

// Set up convenient aliases
Autoloader::alias_to_namespace('Doctrine\\Fuel\\DoctrineFuel');