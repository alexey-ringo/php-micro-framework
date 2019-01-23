<?php

use App\Http\Middleware;
use Framework\Container\Container;
use Framework\Http\Application;
use Framework\Http\Middleware\DispatchMiddleware;
use Framework\Http\Middleware\RouteMiddleware;
use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Router\AuraRouterAdapter;
use Framework\Http\Router\RouterInterface;
use Zend\Diactoros\Response;
use App\Http\Middleware\BasicAuthMiddleware;
use App\Http\Middleware\ErrorHandlerMiddleware;
use App\Http\Middleware\NotFoundHandler;

/** @var Container $container */

$container->set(Application::class, function (Container $container) {
    return new Application(
        $container->get(MiddlewareResolver::class),
        $container->get(RouterInterface::class),
        new NotFoundHandler());
});

$container->set(RouterInterface::class, function() {
    return new AuraRouterAdapter(new Aura\Router\RouterContainer());
});

$container->set(MiddlewareResolver::class, function (Container $container) {
    return new MiddlewareResolver($container, new Response());
});



//Оставляем указание зависимостей явным образом, поскольку autoworing в Контейнере
//не может восстановить значение массивов, передаваемых в конструкторы этих посредников
$container->set(BasicAuthMiddleware::class, function(Container $container) {
    return new BasicAuthMiddleware($container->get('config')['users']);
});

$container->set(ErrorHandlerMiddleware::class, function(Container $container) {
    return new ErrorHandlerMiddleware($container->get('config')['debug']);
});
