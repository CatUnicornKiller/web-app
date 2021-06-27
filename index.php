<?php

// Uncomment this line if you must temporarily take down your site for maintenance.
// require __DIR__ . '/.maintenance.php';

define('WWW_DIR',  dirname(__FILE__));

require __DIR__ . './vendor/autoload.php';

$configurator = App\Bootstrap::boot();
$container = $configurator->createContainer();
$application = $container->getByType(Nette\Application\Application::class);
$application->run();
