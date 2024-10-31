<?php
@session_start();

require_once(dirname(__DIR__) . '/vendor/autoload.php');

$app = new Silex\Application();

$app['debug'] = true;
//define('APP_ENV', 'production');
define('APP_ENV', 'test');

// registers template engine system
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'views',
));

$app->mount('/3ds', include(__DIR__ . "/../views/3DS/app.php"));
$app->mount('/gateway', include(__DIR__ . "/../views/Gateway/app.php"));
$app->mount('/fraud_prevention', include(__DIR__ . "/../views/FraudPrevention/app.php"));
$app->mount('/insurance', include(__DIR__ . "/../views/Insurance/app.php"));

$app->run();
