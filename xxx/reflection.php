<?php

require dirname(__DIR__) . '/vendor/autoload.php';

$class = Framework\Http\Middleware\RouteMiddleware::class;

$reflection = new \ReflectionClass($class);
//Если конструктор не пустой
if(($constructor = $reflection->getConstructor()) !== null) {
    foreach($constructor->getParameters() as $param) {
        $paramClass = $param->getClass();
        echo $paramClass ? $paramClass->getName() : '';
    }
}

echo PHP_EOL;