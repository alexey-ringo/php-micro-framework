<?php

namespace Framework\Http\Middleware;

use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Router\Result;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

//Последний, завершающий Посредник в цепочке 
class DispatchMiddleware implements MiddlewareInterface
{
    private $resolver;
    
    public function __construct(MiddlewareResolver $resolver) {
        $this->resolver = $resolver;
    }
    
    //$request - от предпоследнего посредника RouteMiddleware
    //$next - заглушка по умолчанию
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface 
    {
        /** @var Result $result */
        //Если аттрибута в реквесте с именем Result нет - то вызываем заглушку 404
        if (!$result = $request->getAttribute(Result::class)) {
            return $handler->handle($request);
        }
        
        //Резолвим весь массив обработчиков маршрута (с уже подмешанными аттрибутами)
        $middleware = $this->resolver->resolve($result->getHandler());
        //Возвращает набор обработчиков в Трубе на финальное исполнение
        return $middleware->process($request, $handler);
    }
}