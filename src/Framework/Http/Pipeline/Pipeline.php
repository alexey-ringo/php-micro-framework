<?php

namespace Framework\Http\Pipeline;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Pipeline {
    
    private $queue;
    
    //Инициализация очереди только через конструктор (напрямую создавать объект очереди нельзя!)
    public function __construct() {
        $this->queue = new \SplQueue();
    }
    
    //Добавляет каждый переданный в Трубу Посредник в конец очереди
    public function pipe(callable $middleware): void {
        $this->queue->enqueue($middleware);
    }
    
    //Обращается к классу Next (Запускает рекурсивную ф-ю __invoke()) и передает в нее изначально посланный в трубу запрос и финальный Action
    public function __invoke(ServerRequestInterface $request, callable $default): ResponseInterface {
        //Делегат для запуска рекурсивной функции в Next
        $delegate = new Next($this->queue, $default);
        return $delegate($request);
    }
    
}