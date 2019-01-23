<?php

namespace Tests\Framework\Http;

use Framework\Container\Container;
use Framework\Container\ServiceNotFoundException;

class DummyContainer extends Container
{
    //Создает класс, если еще не создан
    public function get($id)
    {
        if (!class_exists($id)) {
            throw new ServiceNotFoundException($id);
        }
        return new $id();
    }
    
    //Проверяет наличие класса
    public function has($id): bool
    {
        return class_exists($id);
    }
}