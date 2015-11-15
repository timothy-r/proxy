<?php

use Silex\Application;
use Silex\Provider\MonologServiceProvider;
//use Ace\Provider\ConfigProvider;
use Ace\Provider\ErrorHandlerProvider;

require_once __DIR__ . '/vendor/autoload.php';

$app = new Application();
//$app->register(new ConfigProvider());
$app->register(new MonologServiceProvider());
$app['monolog.logfile'] = "php://stdout";
$app['monolog.name'] = 'render';

$app->register(new ErrorHandlerProvider());

$app->get("/blog{path}", function(Request $req) use ($app){

    $app['logger']->info("Proxying request to remote app");

    $remote = 'http://www.flatfishflesh.net';
    $prefix = '/blog';
    $proxy = new \Ace\Proxy($remote, $prefix);

    return $proxy->fromRequest($req);

})->assert('path', '.+');

return $app;