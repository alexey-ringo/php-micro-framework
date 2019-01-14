<?php

namespace Framework\Http;

use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Pipeline\Pipeline;

class Application extends Pipeline {
    
    private $resolver;
    
    public function __construct(MiddlewareResolver $resolver) {
        parent::__construct();
        $this->resolver = $resolver;
    }
    
    public function pipe($middleware): void {
        //Резолвим переданный Посредник/Action
        //Если строка - восстанавливаем объект callable
        //И передаем callable в родительскую функцию pipe()
        parent::pipe($this->resolver->resolve($middleware));
    }
}