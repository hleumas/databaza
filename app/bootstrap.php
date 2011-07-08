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
 


// Configure application
$application = $configurator->container->application;
$application->errorPresenter = 'Error';
//$application->catchExceptions = TRUE;


// Setup router
$application->onStartup[] = function() use ($application) {
	$router = $application->getRouter();

	$router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);

	$router[] = new Route('<presenter>/<action>[/<id>]', 'Zoznamy:riesitelia');
};


// Run the application!
if (!$configurator->container->params['consoleMode']) {
    $application->run();
}
