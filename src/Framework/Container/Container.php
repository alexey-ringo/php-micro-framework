<?php

namespace Framework\Container;

class Container {
    private $definitions;
    private $results = [];
    
    public function __construct(array $definitions = []) {
        $this->definitions = $definitions;
    }
    
    public function get($id)
    {
        //Если запрашиваемый параметр/объект уже есть в массиве $results
        //Сразу возвращаем его, т.е. - кэширование
        if(array_key_exists($id, $this->results)) {
            return $this->results[$id];
        }
        //Если переданная строка с именем класса отсутствует в предустановленных параметрах Контейнера
        if(!array_key_exists($id, $this->definitions)) {
            //Но такой класс существует в проекте
            if (class_exists($id)) {
                $reflection = new \ReflectionClass($id);
                
                $arguments = [];
                if (($constructor = $reflection->getConstructor()) !== null) {
                    foreach ($constructor->getParameters() as $parameter) {
                        if ($paramClass = $parameter->getClass()) {
                            $arguments[] = $this->get($paramClass->getName()); //Рекурсия, в случае вложенности зависимостей!
                        //Если параметром конструктора создаваемого класса является массив - отправляем в конструктор пустой массив    
                        } elseif ($parameter->isArray()) {
                            $arguments[] = [];
                        } else {
                            if (!$parameter->isDefaultValueAvailable()) {
                                throw new ServiceNotFoundException('Unable to resolve "' . $parameter->getName() . '"" in service "' . $id . '"');
                            }
                            //Если параметром конструктора создаваемого класса является скалярная переменная с дефольным значением
                            $arguments[] = $parameter->getDefaultValue();
                        }
                    }
                }
                $this->results[$id] = $reflection->newInstanceArgs($arguments);
                
                //то создаем объект этого класса с автоматисчески заполненным конструктором и записываем его в кэш-массив
                return $this->results[$id];
            }
            throw new ServiceNotFoundException('Unknown service "' . $id . '"');
        }
        
        //При первом обращении к параметру/объекту - записываем его в массив $results
        $definition = $this->definitions[$id];
        if($definition instanceof \Closure) {
            //Необходимо определить для анонимной функции возможность принимать объект самого себя как параметр
            $this->results[$id] = $definition($this);
        } else {
            $this->results[$id] = $definition;
        }
        return $this->results[$id];
    }
    //Проверка существования либо объекта в параметрах Контейнера либо аналогичного класса в проекте
    public function has($id): bool
    {
        return array_key_exists($id, $this->definitions) || class_exists($id);
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