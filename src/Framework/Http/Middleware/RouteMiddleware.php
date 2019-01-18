<?php

namespace Framework\Http\Middleware;

use Framework\Http\Router\Exception\RequestNotMatchedException;
use Framework\Http\Router\RouterInterface;
use Framework\Http\Router\Result;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RouteMiddleware implements MiddlewareInterface {
    
    private $router;
    
    public function __construct(RouterInterface $router) {
        $this->router = $router;
    }
    
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface 
    {
        try {
            //Парсим роутером выбранный маршрут
            $result = $this->router->match($request);        
            //Если все успешно, то роутер вернет название маршрута, его обработчик и аттрибуты
            
            //Проходим по всем аттрибутам маршрута и Примешиваем в реквест эти аттрибуты и их значения
            foreach ($result->getAttributes() as $attribute => $value) {
                $request = $request->withAttribute($attribute, $value);
            }
            //Передаем в последний Посредник финальный обработчик маршрута (Action) 
            //и реквест с подмешанным атрибутом $result. Газвание аттрибута - строка с именем класса Result 
            return $handler->handle($request->withAttribute(Result::class, $result));
            
        }   catch(RequestNotMatchedException $e) {
                return $handler->handle($request);
        }

    }
}