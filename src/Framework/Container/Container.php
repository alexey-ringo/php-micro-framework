<?php

namespace Framework\Container;

class Container {
    private $definitions = [];
    private $results = [];
    
    public function get($id)
    {
        //Если запрашиваемый параметр/объект уже есть в массиве $results
        //Сразу возвращаем его, т.е. - кэширование
        if(array_key_exists($id, $this->results)) {
            return $this->results[$id];
        }
        
        if(!array_key_exists($id, $this->definitions)) {
            throw new ServiceNotFoundException('Unknown service "' . $id . '"');
        }
        
        //При первом обращении к параметру/объекту - записываем его в массив $results
        $definition = $this->definitions[$id];
        if($definition instanceof \Closure) {
            $this->results[$id] = $definition();
        } else {
            $this->results[$id] = $definition;
        }
        return $this->results[$id];
    }
    
    public function set($id, $value): void 
    {
        //Удаление из кэш-массива при изменении
        if(array_key_exists($id, $this->results)) {
            unset($this->results[$id]);
        }
        $this->definitions[$id] = $value;
    }
    
}