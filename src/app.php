<?php

use Silex\Application;
use Silex\Provider\MonologServiceProvider;
//use Ace\Provider\ConfigProvider;
use Ace\Provider\ErrorHandlerProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/vendor/autoload.php';

$app = new Application();
//$app->register(new ConfigProvider());
$app->register(new MonologServiceProvider());
$app['monolog.logfile'] = "php://stdout";
$app['monolog.name'] = 'render';

$app->register(new ErrorHandlerProvider());

$app->get("/", function(Request $req) use ($app){

    return new Response(file_get_contents(__DIR__.'/templates/index.html'));

});

$app->get("/art{path}", function(Request $req) use ($app){

    $app['logger']->info("Proxying request to remote app");

    $remote = 'http://www.artcoeur.co.uk';
    $prefix = '/art';
    $proxy = new \Ace\Proxy($remote, $prefix);

    return $proxy->fromRequest($req);

})->assert('path', '.+');

return $app;