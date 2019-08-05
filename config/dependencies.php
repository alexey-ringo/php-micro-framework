<?php

use App\Http\Middleware;
use Framework\Http\Application;
use Framework\Http\Pipeline\MiddlewareResolver;
//use Framework\Http\Router\AuraRouterAdapter;
use Framework\Http\Router\SimpleRouter\SimpleRouter;
use Framework\Http\Router\RouterInterface;
use Zend\Diactoros\Response;
use App\Http\Middleware\BasicAuthMiddleware;
use App\Http\Middleware\ErrorHandlerMiddleware;
use App\Http\Middleware\NotFoundHandler;
use Psr\Container\ContainerInterface;



return [
    'dependencies' => [
        'abstract_factories' => [
            Zend\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory::class,
        ],
        
        'factories' => [
            Application::class => function (ContainerInterface $container) {
                return new Application(
                    $container->get(MiddlewareResolver::class),
                    $container->get(RouterInterface::class),
                    new NotFoundHandler()
                );
            },
            /*
            RouterInterface::class => function() {
                return new AuraRouterAdapter(new Aura\Router\RouterContainer());
            },
            */
            
            RouterInterface::class => function() {
                return new SimpleRouter(new Framework\Http\Router\SimpleRouter\RouteCollection());
            },
            
            
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
        ],
    ],
];