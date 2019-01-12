<?php

namespace Framework\Http\Pipeline;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Pipeline {
    
    private $queue;
    
    public function __construct() {
        $this->queue = new \SplQueue();
    }
    
    public function pipe(callable $middleware): void {
        $this->queue->enqueue($middleware);
    }
    
    public function __invoke(ServerRequestInterface $request, callable $next): ResponseInterface {
        $delegate = new Next(clone $this->queue, $next);
        //передача в Next на итерации
        return $delegate($request);
    }
    
}