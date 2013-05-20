<?php

$dir = dirname(__FILE__).DS;

// Add all the required classes
Autoloader::add_namespace('Doctrine', $dir.'vendor/Doctrine/', true);
Autoloader::add_namespace('Gedmo', $dir.'vendor/Gedmo/', true);
Autoloader::add_namespace('Doctrine\\Fuel', $dir.'classes/', true);

// Symfony namespaces
Autoloader::add_namespace('Symfony\\Component\\Console', $dir.'vendor/Symfony/Component/Console/', true);
Autoloader::add_namespace('Symfony\\Component\\HttpFoundation', $dir.'vendor/Symfony/Component/HttpFoundation/', true);
Autoloader::add_namespace('Symfony\\Component\\Validator', $dir.'vendor/Symfony/Component/Validator/', true);
Autoloader::add_namespace('Symfony\\Component\\Yaml', $dir.'vendor/Symfony/Component/Yaml/', true);

// Set up convenient aliases
Autoloader::alias_to_namespace('Doctrine\\Fuel\\DoctrineFuel');