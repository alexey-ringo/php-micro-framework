<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Framework\Http\Pipeline;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Stratigility\Middleware\CallableMiddlewareDecorator;
use Zend\Stratigility\Middleware\DoublePassMiddlewareDecorator;
use Zend\Stratigility\Middleware\RequestHandlerMiddleware;
use Zend\Stratigility\MiddlewarePipe;

/**
 * Description of ActionResolver
 *
 * @author alexringo
 */
class MiddlewareResolver {
    
    private $responsePrototype;
    
    public function __construct(ResponseInterface $responsePrototype)
    {
        $this->responsePrototype = $responsePrototype;
    }
    
    
    public function resolve($handler): MiddlewareInterface
    {
        //В зависимости от типа $handler:
        
        //Если $handler - массив (объектов - обработчиков/Посредников)
        if (\is_array($handler)) {
            return $this->createPipe($handler);
        }
        
        //Если $handler - сторока
        //Возвращаем анонимную функцию с 3-мя аргументами и в ней уже создаем объект Обработчика
        //затем повторно резолвим но уже как объект - с анализом тиав объекта ниже
        if (\is_string($handler)) {
            return new CallableMiddlewareDecorator(function (ServerRequestInterface $request, RequestHandlerInterface $next) use ($handler) {
                $middleware = $this->resolve(new $handler());
                return $middleware->process($request, $next);
            });
        }
        
        //Если $handler - уже созданный объект (может прийти на вход резолвера извне,
        //а может вернуться в анонимной функции из проверки is_string
        //то возращаем его в Трубу без изменений
         if ($handler instanceof MiddlewareInterface) {
            return $handler;
        }
        
        if ($handler instanceof RequestHandlerInterface) {
            return new RequestHandlerMiddleware($handler);
        }
        
        if (\is_object($handler)) {
            $reflection = new \ReflectionObject($handler);
            if ($reflection->hasMethod('__invoke')) {
                $method = $reflection->getMethod('__invoke');
                $parameters = $method->getParameters();
                if (\count($parameters) === 2 && $parameters[1]->isCallable()) {
                    return new SinglePassMiddlewareDecorator($handler);
                }
                return new DoublePassMiddlewareDecorator($handler, $this->responsePrototype);
            }
        }
        
        throw new UnknownMiddlewareTypeException($handler);
    }
    
    //Если $handler - массив (объектов - обработчиков/Посредников)
    private function createPipe(array $handlers): MiddlewarePipe
    {
        //Новая внутренняя Труба и в цикле добавляем туда все обработчики из массива
        $pipeline = new MiddlewarePipe();
        foreach($handlers as $handler) {
            $pipeline->pipe($this->resolve($handler));
        }
        return $pipeline;
    }
}
