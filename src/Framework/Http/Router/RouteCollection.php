<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Framework\Http\Router;

use Framework\Http\Router\Route;
/**
 * Description of RouteCollection
 *
 * @author alexringo
 */
class RouteCollection {
    
    private $routes = [];
    
    public function addRoute(Route $route): void {
        $this->routes[] = $route;
    }
    
    //Передаем: имя, шаблон пути, обработчик и токены
    public function get($name, $pattern, $handler, array $tokens = []): void {
        $this->addRoute(new Route($name, $pattern, $handler, ['GET'], $tokens));
    }
            
    public function post($name, $pattern, $handler, array $tokens = []): void {
        $this->addRoute(new Route($name, $pattern, $handler, ['POST'], $tokens));
    }
    
    public function add($name, $pattern, $handler, array $methods, array $tokens = []): void {
        $this->addRoute(new Route($name, $pattern, $handler, $methods, $tokens));
    }
    
    public function any($name, $pattern, $handler, array $tokens = []): void {
        $this->addRoute(new Route($name, $pattern, $handler, [], $tokens));
    }
    
    /**
     * @return Route[]
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
    
}
