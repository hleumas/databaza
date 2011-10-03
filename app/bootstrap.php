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

$dbFKSprefix     = $configurator->container->params['dbfksprefix'];
$submitFKSprefix = $configurator->container->params['submitfksprefix'];
 


// Configure application
$application = $configurator->container->application;
$application->errorPresenter = 'Error';
//$application->catchExceptions = TRUE;


// Setup router
$application->onStartup[] = function() use ($application, $dbFKSprefix, $submitFKSprefix) {
	$router = $application->getRouter();

    $router[] = new Route('index.php', array(
        'module' => 'Admin',
        'presenter' => 'Riesitelia',
        'action' => 'default',
        'kategoria_id' => '1'),
        Route::ONE_WAY);

    $router[] = new Route("$dbFKSprefix<presenter>/<action>[/<id>]", array(
        'module' => 'Admin',
        'presenter' => 'Riesitelia',
        'action' => 'default',
        'kategoria_id' => '1'),
        Route::SECURED);

    $router[] = new Route("$submitFKSprefix<presenter>/<action>[/<id>]", array(
        'module' => 'Submit',
        'presenter' => 'Priklady',
        'action' => 'zoznam',
        'kategoria_id' => '1'),
        Route::SECURED);

};

Nette\Forms\Container::extensionMethod('addDatePicker', function ($container, $name, $label = NULL) {
    return $container[$name] = new Forms\Controls\DatePicker($label);
});

// Run the application!
if (!$configurator->container->params['consoleMode']) {
    $application->run();
}
