<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Framework\Http\Pipeline;

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
        //то вместо того, чтобы сразу здесь создавать объект обработчика - 
        //возвращаем в Трубу анонимную функцию с интерфейсом, соответствующим Трубе ($request, $next), 
        //которая запустится уже в Трубе, и сама там создаст объект.
        if (\is_string($handler)) {
            return function (ServerRequestInterface $request, callable $next) use ($handler) {
                $object = new $handler();
                //Если это будет Action, то параметр $next при передаче в него будет проигнорирован
                return $object($request, $next);
            };
        }
        //Если $handler - уже был созданный объект - то возращаем его в Трубу без изменений
        return $handler;
    }
    
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
