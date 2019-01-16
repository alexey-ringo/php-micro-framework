<?php

namespace Framework\Http;

use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Pipeline\Pipeline;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Application extends Pipeline {
    
    private $resolver;
    //Обработчик исключения по умолчанию (дефолтная заглушка)
    private $default;
    
    public function __construct(MiddlewareResolver $resolver, callable $default) {
        parent::__construct();
        $this->resolver = $resolver;
        $this->default = $default;
    }
    
    public function pipe($middleware): void {
        //Резолвим переданный Посредник/Action
        //Если строка - восстанавливаем объект callable
        //И передаем callable в родительскую функцию pipe()
        parent::pipe($this->resolver->resolve($middleware));
    }
    
    //Вызывает объект Application-потомок Pipeline, как функцию (Pipeline::__invoke()) и передает 
    public function run(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface 
    {
        return $this($request, $response, $this->default);
    }
}