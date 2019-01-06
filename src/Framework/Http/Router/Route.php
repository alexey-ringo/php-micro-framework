<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Framework\Http\Router;

use Psr\Http\Message\ServerRequestInterface;
/**
 * Description of Route
 *
 * @author alexringo
 */
class Route {
    
    private $name;
    private $pattern;
    private $handler;    
    private $tokens;
    private $methods;
    
    public function __construct($name, $pattern, $handler, array $methods, array $tokens = []) {
        $this->name = $name;
        $this->pattern = $pattern;
        $this->handler = $handler;
        $this->tokens = $tokens;
        $this->methods = $methods;
    }
    
    public function match(ServerRequestInterface $request): ?Result {


        //Если в текущем проверяемом $route указаны методы (GET, POST и т.д.)
        //и если метод из текущего запроса совпадает с методами, записанными для данного маршрута - продолжаем проверку на совпадение
        //иначе - переключаемя на сматчивание со следующим записанным маршрутом
        if ($this->methods && !\in_array($request->getMethod(), $this->methods, true)) {
            return null;
        }

        //Весь код в шаблоне маршрута, который заключен между открывающей и закрывающей фигурными скобками,
        //но который сам не содержит фигурных скобок - 
        //- нужно заменить на результат выполнения анонимной функции.
        //В анонимную функцию приходят $matches для каждого элемента.
        //$matches[0] - попадает вся строка, $matches[1] - все, что внутри фигурных скобок, т.е. - 'id'
        $pattern = preg_replace_callback('~\{([^\}]+)\}~', function ($matches) {
            $argument = $matches[1]; //'id'
            $replace = $this->tokens[$argument] ?? '[^}]+';
            return '(?P<' . $argument . '>' . $replace . ')';
        }, $this->pattern);

        $path = $request->getUri()->getPath();
        
        if (!preg_match('~^' . $pattern . '$~i', $path, $matches)) {
            return null;
        }

        return new Result(
                $this->name,
                $this->handler,
                //отфильтровываем из массива $matches только значение ключей строкового типа (имя аргумента, напр - 'id')
                array_filter($matches, '\is_string', ARRAY_FILTER_USE_KEY)
        );
    }
    
    public function generate($name, array $params = []): ?string {
        $arguments = array_filter($params);
        if ($name !== $this->name) {
            return null;
        }
        $url = preg_replace_callback('~\{([^\}]+)\}~', function ($matches) use (&$arguments) {
            $argument = $matches[1];
            if (!array_key_exists($argument, $arguments)) {
                throw new \InvalidArgumentException('Missing parameter "' . $argument . '"');
            }
            return $arguments[$argument];
        }, $this->pattern);
        return $url;
    }

}
