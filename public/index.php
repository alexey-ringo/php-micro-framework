<?php
//Вкл вывод ошибок
ini_set('display_errors', 1);
//Активировали лог ошибок в полном режиме
error_reporting(E_ALL);

use App\Http\Action;
use App\Http\Middleware;
use Framework\Http\Application;
use Framework\Container\Container;
use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Router\AuraRouterAdapter;
use Framework\Http\Router\RouterInterface;
use Zend\Diactoros\Response;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Zend\Diactoros\ServerRequestFactory;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

$container = new Container();
$container->set('config', [
    'debug' => true,
    'users' => [
        'admin' => 'adminpass',
        'user' => 'userpass'
        ],
    ]);

$container->set(Middleware\BasicAuthMiddleware::class, function(Container $container) {
    return new Middleware\BasicAuthMiddleware($container->get('config')['users']);
});

$container->set(Middleware\ErrorHandlerMiddleware::class, function(Container $container) {
    return new Middleware\ErrorHandlerMiddleware($container->get('config')['debug']);
});

$container->set(MiddlewareResolver::class, function (Container $container) {
    return new MiddlewareResolver(new Response());
});

$container->set(RouterInterface::class, function(Container $container) {
    $aura = new Aura\Router\RouterContainer();
    $routes = $aura->getMap();
    $routes->get('home', '/', Action\HelloAction::class);
    $routes->get('about', '/about', Action\AboutAction::class);
    $routes->get('cabinet', '/cabinet', Action\CabinetAction::class);
    $routes->get('blog', '/blog', Action\Blog\IndexAction::class);
    $routes->get('blog_show', '/blog/{id}', Action\Blog\ShowAction::class)->tokens(['id' => '\d+']);

    return new AuraRouterAdapter($aura);
});

$app = new Application($container->get(MiddlewareResolver::class), new Middleware\NotFoundHandler());

$app->pipe($container->get(Middleware\ErrorHandlerMiddleware::class));
$app->pipe(Middleware\CredentialsMiddleware::class);
$app->pipe(Middleware\ProfilerMiddleware::class);
$app->pipe(new Framework\Http\Middleware\RouteMiddleware($container->get(RouterInterface::class)));

$app->pipe('cabinet', $container->get(Middleware\BasicAuthMiddleware::class));

$app->pipe(new Framework\Http\Middleware\DispatchMiddleware($container->get(MiddlewareResolver::class)));


//Извлекаем $request из суперглобальных массивов $_GET и т.д.
$request = ServerRequestFactory::fromGlobals();


$response = $app->handle($request);

### Sending
$emitter = new SapiEmitter();
$emitter->emit($response);