<?php

namespace Framework\Http\Pipeline;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Next {
    
    private $queue;
    private $next;
    
    //Инициализация через констр-р очередью и Action, что бы не крутились в итерации
    public function __construct(\SplQueue $queue, $next) {
        $this->queue = $queue;
        $this->next = $next;
    }
    
    //Итерация
    public function __invoke(ServerRequestInterface $request): ResponseInterface {
       //Если очередь опустела - запускаем итоговый Action и возвращаем результат
        if($this->queue->isEmpty()) {
            return ($this->next)($request);
        }
        //Извлечение из очереди
        $middleware = $this->queue->dequeue();
        //Возврат на очередную итерацию
        return $middleware($request, function(ServerRequestInterface $request) {
            return $this($request);
        });
    }
}