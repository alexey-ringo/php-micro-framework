<?php

namespace Framework\Http\Middleware;

use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Router\Exception\RequestNotMatchedException;
use Framework\Http\Router\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouteMiddleware {
    
    private $router;
    private $resolver;
    
    public function __construct(RouterInterface $router, MiddlewareResolver $resolver) {
        $this->router = $router;
        $this->resolver = $resolver;
    }
    
    public function __invoke(ServerRequestInterface $request, callable $next) {
        try {
            //Парсим роутером выбранный маршрут
            $result = $this->router->match($request);        
            //Если все успешно, то роутер вернет название маршрута, его обработчик и аттрибуты
            
            //Проходим по всем аттрибутам маршрута и Примешиваем в реквест эти аттрибуты и их значения
            foreach ($result->getAttributes() as $attribute => $value) {
                $request = $request->withAttribute($attribute, $value);
            }
            //Резолвит весь массив обработчиков маршрута (с уже подмешанными аттрибутами)
            $middleware = $this->resolver->resolve($result->getHandler());
            //Возврящает обработчик либо на финальное исполнение либо дальше в цепочку
            return $middleware($request, $next);
            
        }   catch(RequestNotMatchedException $ex) {
                return $next($request);
        }

    }
}