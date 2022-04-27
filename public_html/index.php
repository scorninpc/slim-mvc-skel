<?php

ini_set("display_errors", "On");
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);


// Get the config file
$config = require(__DIR__ . "/../application/Configs/config.development.php");

// Define a path to application directory
defined("APPLICATION_PATH") || define("APPLICATION_PATH", realpath($config['application']['location']));

// Include autoload
require __DIR__ . "/../vendor/autoload.php";

// Create Container
$container = new \DI\Container();
\Slim\Factory\AppFactory::setContainer($container);

// Create the app
$app = \Slim\Factory\AppFactory::create();

// Set view in Container
$container->set("view", function($container) use ($config) {

	// Create smarty view
	$view = new \Slim\Views\Smarty($config['smarty']);

	return $view;
});

// Set the config
$container->set("config", function($container) use ($config) {
	return $config;
});

// Routes
$routes = require(APPLICATION_PATH . "/Configs/routes.php");
$container->set("routes", $routes);
foreach($routes as $name => $route) {
	$app->map($route['type'], $route['pattern'], function (\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Message\ResponseInterface $response, $args) use ($config) {

			// Call MVC bootstrap
			$bootstrap = new \Slim\Mvc\Bootstrap($this, $request, $response, $args, $config);

			// Get response from controller
			return $bootstrap->getResponse();
			
		})
		->setName($name);
}

// Run
$app->run();