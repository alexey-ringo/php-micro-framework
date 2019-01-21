<?php

namespace Framework\Http;

use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Stratigility\Middleware\PathMiddlewareDecorator;
use Zend\Stratigility\MiddlewarePipe;

class Application implements MiddlewareInterface, RequestHandlerInterface {
    
    private $resolver;
    private $router;
    //Обработчик исключения по умолчанию (дефолтная заглушка)
    private $default;
    private $pipeline;
    
    public function __construct(MiddlewareResolver $resolver, RouterInterface $router, RequestHandlerInterface $default) {
        $this->resolver = $resolver;
        $this->router = $router;
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
    
    //Передаем: имя, шаблон пути, обработчик и токены
    public function get($name, $path, $handler, array $options = []): void 
    {
        $this->router->addRoute($name, $path, $handler, ['GET'], $options);
    }
    
    public function post($name, $path, $handler, array $options = []): void 
    {
        $this->router->addRoute($name, $path, $handler, ['POST'], $options);
    }
    
    public function any($name, $path, $handler, array $options = []): void
    {
        $this->route($name, $path, $handler, [], $options);
    }
    
    public function put($name, $path, $handler, array $options = []): void
    {
        $this->route($name, $path, $handler, ['PUT'], $options);
    }
    
    public function patch($name, $path, $handler, array $options = []): void
    {
        $this->route($name, $path, $handler, ['PATCH'], $options);
    }
    
    public function delete($name, $path, $handler, array $options = []): void
    {
        $this->route($name, $path, $handler, ['DELETE'], $options);
    }
    //Пользовательский тип маршрута с кастомными (напр - миксированными) методами
    public function route($name, $pattern, $handler, array $methods, array $options = []): void 
    {
        $this->router->addRoute($name, $path, $handler, $methods, $tokens);
    }
}