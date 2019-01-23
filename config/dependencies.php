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
########################################
use App\Http\Middleware\CredentialsMiddleware;
use App\Http\Middleware\ProfilerMiddleware;
use App\Http\Action;

/** @var Container $container */

$container->set(BasicAuthMiddleware::class, function(Container $container) {
    return new BasicAuthMiddleware($container->get('config')['users']);
});

$container->set(ErrorHandlerMiddleware::class, function(Container $container) {
    return new ErrorHandlerMiddleware($container->get('config')['debug']);
});

$container->set(MiddlewareResolver::class, function (Container $container) {
    return new MiddlewareResolver($container, new Response());
});

$container->set(RouteMiddleware::class, function (Container $container) {
    return new RouteMiddleware($container->get(RouterInterface::class));
});

$container->set(DispatchMiddleware::class, function (Container $container) {
    return new DispatchMiddleware($container->get(MiddlewareResolver::class));
});

$container->set(RouterInterface::class, function() {
    return new AuraRouterAdapter(new Aura\Router\RouterContainer());
});

$container->set(Application::class, function (Container $container) {
    return new Application(
        $container->get(MiddlewareResolver::class),
        $container->get(RouterInterface::class),
        new NotFoundHandler());
});

######################################################################

$container->set(CredentialsMiddleware::class, function () {
    return new CredentialsMiddleware();
});
$container->set(ProfilerMiddleware::class, function () {
    return new ProfilerMiddleware();
});
$container->set(Action\HelloAction::class, function () {
    return new Action\HelloAction();
});
$container->set(Action\AboutAction::class, function () {
    return new Action\AboutAction();
});
$container->set(Action\CabinetAction::class, function () {
    return new Action\CabinetAction();
});
$container->set(Action\Blog\IndexAction::class, function () {
    return new Action\Blog\IndexAction();
});
$container->set(Action\Blog\ShowAction::class, function () {
    return new Action\Blog\ShowAction();
});