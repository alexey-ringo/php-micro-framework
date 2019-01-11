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
    
    //Добавляет каждый переданный Посредник в конец очереди
    public function pipe(callable $middleware): void {
        $this->queue->enqueue($middleware);
    }
    
    //Запускает рекурсивную ф-ю next() и передает в нее посленный в трубу запрос и финальный Action
    public function __invoke(ServerRequestInterface $request, callable $default): ResponseInterface {
        return $this->next($request, $default);
    }
    
    
    //Функция работает рекурсивно, при каждой итерации извлекая первый верний элемент из массива Посредников
    //пока в массиве $middleware не закончатся объекты.
    //После этого вызовется $default
    private function next(ServerRequestInterface $request, callable $default): ResponseInterface {
        //Если очередь пуста
        //Возврящаем финальный Action в $default и передаем в него итоговый реквест (который может быть уже измененным в результате итераций)
        if($this->queue->isEmpty()) {
            //И возвращаем нго как итор всей работы Трубы
            return $default($request);
        }
        
        //Извлекаем из очереди посредников первый элемент с начала очеред
        $current = $this->queue->dequeue();
        
        return $current($request, function(ServerRequestInterface $request) use($default) {
            //Первоначально полученный $default сохраняем и передаем снова дальше на рекурсию для финального исполнения в конце всех итераций
            return $this->next($request, $default);
        });
    }
}