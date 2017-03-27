<?php
/**
 * This is entry point for every page of the application (Front Controller)
 *
 * Here we register autoloader for all classes and libraries,
 * load config files and initialize needed global environment
 *
 * Whenever server receives a request, Router is responsible for serving it
 * and sending back appropriate response
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/config.php';


// Environment init
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$session = new Symfony\Component\HttpFoundation\Session\Session();
$session->start();

$req = Symfony\Component\HttpFoundation\Request::createFromGlobals();

App\Controller\Logger::init();
App\Controller\Router::run();
