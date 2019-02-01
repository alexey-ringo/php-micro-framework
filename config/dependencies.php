<?php

use App\Http\Action;
use App\Http\Middleware;
use Framework\Http\Application;
use Framework\Http\Middleware\DispatchMiddleware;
use Framework\Http\Middleware\RouteMiddleware;
use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Router\AuraRouterAdapter;
//use Framework\Http\Router\SimpleRouter\SimpleRouter;
use Framework\Http\Router\RouterInterface;
use Zend\Diactoros\Response;
use App\Http\Middleware\BasicAuthMiddleware;
use App\Http\Middleware\ErrorHandlerMiddleware;
use App\Http\Middleware\NotFoundHandler;
use Psr\Container\ContainerInterface;



return [
    'dependencies' => [
        'invokables' => [
            Middleware\CredentialsMiddleware::class,
            Middleware\ProfilerMiddleware::class,
            Action\HelloAction::class,
            Action\AboutAction::class,
            Action\CabinetAction::class,
            Action\Blog\IndexAction::class,
            Action\Blog\ShowAction::class,
        ],
        'factories' => [
            Application::class => function (ContainerInterface $container) {
                return new Application(
                    $container->get(MiddlewareResolver::class),
                    $container->get(RouterInterface::class),
                    new NotFoundHandler()
                );
            },
            
            RouterInterface::class => function() {
                return new AuraRouterAdapter(new Aura\Router\RouterContainer());
            },
            
            /*
            RouterInterface::class => function() {
                return new SimpleRouter(new Framework\Http\Router\SimpleRouter\RouteCollection());
            },
            */
            
            MiddlewareResolver::class => function (ContainerInterface $container) {
                return new MiddlewareResolver($container, new Response());
            },
            
            //Оставляем указание зависимостей явным образом, поскольку autoworing в Контейнере
            //не может восстановить значение массивов, передаваемых в конструкторы этих посредников
            BasicAuthMiddleware::class => function(ContainerInterface $container) {
                return new BasicAuthMiddleware($container->get('config')['users']);
            },

            ErrorHandlerMiddleware::class => function(ContainerInterface $container) {
                return new ErrorHandlerMiddleware($container->get('config')['debug']);
            },
            
            DispatchMiddleware::class => function (ContainerInterface $container) {
                return new DispatchMiddleware($container->get(MiddlewareResolver::class));
            },
            
            RouteMiddleware::class => function (ContainerInterface $container) {
                return new RouteMiddleware($container->get(RouterInterface::class));
            },
        ],
    ],
];