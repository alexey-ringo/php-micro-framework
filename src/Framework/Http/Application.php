<?php

namespace Framework\Http;

use Framework\Http\Pipeline\MiddlewareResolver;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Stratigility\Middleware\PathMiddlewareDecorator;
use Zend\Stratigility\MiddlewarePipe;

class Application implements MiddlewareInterface, RequestHandlerInterface {
    
    private $resolver;
    //Обработчик исключения по умолчанию (дефолтная заглушка)
    private $default;
    private $pipeline;
    
    public function __construct(MiddlewareResolver $resolver, RequestHandlerInterface $default) {
        $this->resolver = $resolver;
        $this->pipeline = new MiddlewarePipe();
        $this->default = $default;
    }
    
    public function pipe($path, $middleware = null): void {
        if ($middleware === null) {
         $this->pipeline->pipe($this->resolver->resolve($path));
        } else {
            $this->pipeline->pipe(new PathMiddlewareDecorator($path, $this->resolver->resolve($middleware)));
        }
    }
    
    //Передача результата выполнения всей цепочки Посредников в предфинальный DispathMiddleware
    public function handle(ServerRequestInterface $request): ResponseInterface 
    {
        return $this->pipeline->process($request, $this->default);
    }
    
    //Для последовательной обработки Middleware
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->pipeline->process($request, $handler);
    }
}