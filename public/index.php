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
use App\Http\Middleware\BasicAuthMiddleware;
use App\Http\Middleware\ErrorHandlerMiddleware;
use Framework\Http\Middleware\DispatchMiddleware;
use Framework\Http\Middleware\RouteMiddleware;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

### Configuration
$container = new Container();
$container->set('config', [
    'debug' => true,
    'users' => [
        'admin' => 'adminpass',
        'user' => 'userpass'
        ],
    ]);

$container->set(BasicAuthMiddleware::class, function(Container $container) {
    return new BasicAuthMiddleware($container->get('config')['users']);
});

$container->set(ErrorHandlerMiddleware::class, function(Container $container) {
    return new ErrorHandlerMiddleware($container->get('config')['debug']);
});

$container->set(MiddlewareResolver::class, function () {
    return new MiddlewareResolver(new Response());
});

$container->set(RouteMiddleware::class, function (Container $container) {
    return new RouteMiddleware($container->get(RouterInterface::class));
});

$container->set(DispatchMiddleware::class, function (Container $container) {
    return new DispatchMiddleware($container->get(MiddlewareResolver::class));
});

$container->set(RouterInterface::class, function() {
    $aura = new Aura\Router\RouterContainer();
    $routes = $aura->getMap();
    $routes->get('home', '/', Action\HelloAction::class);
    $routes->get('about', '/about', Action\AboutAction::class);
    $routes->get('cabinet', '/cabinet', Action\CabinetAction::class);
    $routes->get('blog', '/blog', Action\Blog\IndexAction::class);
    $routes->get('blog_show', '/blog/{id}', Action\Blog\ShowAction::class)->tokens(['id' => '\d+']);

    return new AuraRouterAdapter($aura);
});

$container->set(Application::class, function (Container $container) {
    return new Application($container->get(MiddlewareResolver::class), 
            new Middleware\NotFoundHandler());
});

### Initialization
/** @var Application $app */
$app = $container->get(Application::class);

$app->pipe($container->get(ErrorHandlerMiddleware::class));
$app->pipe(Middleware\CredentialsMiddleware::class);
$app->pipe(Middleware\ProfilerMiddleware::class);
$app->pipe($container->get(RouteMiddleware::class));

$app->pipe('cabinet', $container->get(BasicAuthMiddleware::class));

$app->pipe($container->get(DispatchMiddleware::class));


//Извлекаем $request из суперглобальных массивов $_GET и т.д.
$request = ServerRequestFactory::fromGlobals();


$response = $app->handle($request);

### Sending
$emitter = new SapiEmitter();
$emitter->emit($response);