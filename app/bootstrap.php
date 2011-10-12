<?php

/**
 * My Application bootstrap file.
 */


use Nette\Diagnostics\Debugger,
	Nette\Application\Routers\SimpleRouter,
	Nette\Application\Routers\Route;

// Load Nette Framework
// this allows load Nette Framework classes automatically so that
// you don't have to litter your code with 'require' statements
require LIBS_DIR . '/Nette/loader.php';


// Enable Nette\Debug for error visualisation & logging
Debugger::$strictMode = TRUE;
Debugger::enable();


// Load configuration from config.neon file
$configurator = new Nette\Configurator;
$configurator->loadConfig(__DIR__ . '/config.neon');
setlocale(LC_ALL, 'sk_SK.utf8'); 

$configurator->container->addService('sources', function($cont) {
    $s = new SourceContainer();
    $s->params['database'] = $cont->database;
    return $s;
});

$configurator->container->addService('authenticator', function($cont) {
    return new \Authenticator($cont->database);
});

$routes = $configurator->container->params['routes'];
 

// Configure application
$application = $configurator->container->application;
$application->errorPresenter = 'Error';
//$application->catchExceptions = TRUE;


// Setup router
$application->onStartup[] = function() use ($application, $routes) {
    $router = $application->getRouter();

    foreach ($routes as $route) {
      $metadata = \FlatArray::toArray($route['metadata']);
      $router[] = new Route(
          "//{$route['prefix']}/index.php",
          $metadata,
          Route::ONE_WAY);

      $mask = "//{$route['prefix']}/<presenter>/<action>[/<id>]";
      if ($route['secured']) {
          $router[] = new Route($mask, $metadata, Route::SECURED);
      } else {
          $router[] = new Route($mask, $metadata);
      }
    }
};

Nette\Forms\Container::extensionMethod('addDatePicker', function ($container, $name, $label = NULL) {
    return $container[$name] = new Forms\Controls\DatePicker($label);
});

// Run the application!
if (!$configurator->container->params['consoleMode']) {
    $application->run();
}
