<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Framework\Http\Pipeline;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Description of ActionResolver
 *
 * @author alexringo
 */
class MiddlewareResolver {
    public function resolve($handler): callable
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
            return function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use ($handler) {
                $middleware = $this->resolve(new $handler());
                return $middleware($request, $response, $next);
            };
        }
        
        //Если $handler - уже созданный объект (может прийти на вход резолвера извне,
        //а может вернуться в анонимной функции из проверки is_string
        //то возращаем его в Трубу без изменений
         if ($handler instanceof MiddlewareInterface) {
            return function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use ($handler) {
                //PsrHandlerWrapper - объект-прототип, реализующий PsrMiddlewareInterface
                return $handler->process($request, new PsrHandlerWrapper($next));
            };
        }
        
        if (\is_object($handler)) {
            $reflection = new \ReflectionObject($handler);
            if ($reflection->hasMethod('__invoke')) {
                $method = $reflection->getMethod('__invoke');
                $parameters = $method->getParameters();
                if (\count($parameters) === 2 && $parameters[1]->isCallable()) {
                    return function (ServerRequestInterface $request, ResponseInterface $response, callable $next) use ($handler) {
                        return $handler($request, $next);
                    };
                }
                return $handler;
            }
        }
        
        throw new UnknownMiddlewareTypeException($handler);
    }
    
    //Если $handler - массив (объектов - обработчиков/Посредников)
    private function createPipe(array $handlers): Pipeline
    {
        //Новая внутренняя Труба и в цикле добавляем туда все обработчики из массива
        $pipeline = new Pipeline();
        foreach($handlers as $handler) {
            $pipeline->pipe($this->resolve($handler));
        }
        return $pipeline;
    }
}
