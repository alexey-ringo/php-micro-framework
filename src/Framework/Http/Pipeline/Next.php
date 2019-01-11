<?php

namespace Framework\Http\Pipeline;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Next {
    
    private $queue;
    private $default;
    
    //Очередь и финальный Action 
    public function __construct(\SplQueue $queue, $default) {
        $this->queue = $queue;
        $this->default = $default;
    }
    
     //Функция работает рекурсивно, при каждой итерации извлекая первый элемент из очереди Посредников
    //пока в очереди $queue не закончатся объекты.
    //После этого возвращение финального Action в $default
    public function __invoke(ServerRequestInterface $request): ResponseInterface {
        //Если очередь пуста
        //Возврящаем финальный Action в $default и передаем в него итоговый реквест (который может быть уже измененным в результате итераций)
        if($this->queue->isEmpty()) {
            //И возвращаем нго как итог всей работы Трубы
            return ($this->default)($request);
        }
        
        //Извлекаем из очереди посредников первый элемент с начала очеред
        $current = $this->queue->dequeue();
        
        return $current($request, function(ServerRequestInterface $request) {
            //Первоначально полученный $default сохраняем и передаем снова дальше на рекурсию для финального исполнения в конце всех итераций
            return $this($request);
        });
    }
}