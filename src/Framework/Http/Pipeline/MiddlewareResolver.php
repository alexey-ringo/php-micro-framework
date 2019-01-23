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
use Framework\Container\Container;

/**
 * Description of ActionResolver
 *
 * @author alexringo
 */
class MiddlewareResolver {
    
    private $responsePrototype;
    private $container;
    
    public function __construct(Container $container, ResponseInterface $responsePrototype)
    {
        $this->responsePrototype = $responsePrototype;
        $this->container = $container;
    }
    
    
    public function resolve($handler): MiddlewareInterface
    {
        if (\is_array($handler)) {
            return $this->createPipe($handler);
        }
        
        if (\is_string($handler) && $this->container->has($handler)) {
            return new CallableMiddlewareDecorator(function (ServerRequestInterface $request, RequestHandlerInterface $next) use ($handler) {
                $middleware = $this->resolve($this->container->get($handler));
                return $middleware->process($request, $next);
            });
        }
        
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
    
    
    private function createPipe(array $handlers): MiddlewarePipe
    {
        $pipeline = new MiddlewarePipe();
        foreach($handlers as $handler) {
            $pipeline->pipe($this->resolve($handler));
        }
        return $pipeline;
    }
}
