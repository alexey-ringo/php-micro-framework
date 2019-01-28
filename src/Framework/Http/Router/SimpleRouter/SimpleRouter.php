<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Framework\Http\Router\SimpleRouter;

use Framework\Http\Router\Exception\RequestNotMatchedException;
use Framework\Http\Router\Exception\RouteNotFoundException;
use Framework\Http\Router\RouterInterface;
use Framework\Http\Router\SimpleRouter\RouteCollection;
use Framework\Http\Router\SimpleRouter\Route\RegexpRoute;
use Framework\Http\Router\Result;
use Framework\Http\Router\RouteData;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Description of SimpleRouter
 *
 * @author alexringo
 */
class SimpleRouter implements RouterInterface {
    
    private $routes;
    
    public function __construct(RouteCollection $routes) {
        $this->routes = $routes;
    }
    
    
    //Принимает реквест, обходит все имеющиеся маршруты, матчит на соответствие со всеми правилами
    //и возврящает распарсенный результат в Result
    public function match(ServerRequestInterface $request): Result {
        //Обходим все имеющиеся маршруты
        foreach ($this->routes->getRoutes() as $route) {
            /** @var RegexpRoute $route */
            //Каждый маршрут отправляем на проматчивание в вынесенную в Route match()
            //если у маршрута совпали данные в request с условиями данного маршрута
            if ($result = $route->match($request)) {
                //Возвращяем объект Result 
                return $result;
            }
        }
        throw new RequestNotMatchedException($request);
    }

    public function generate($name, array $params = []): string {
        foreach ($this->routes->getRoutes() as $route) {
            if (null !== $url = $route->generate($name, array_filter($params))) {
                return $url;
            }
        }
        throw new RouteNotFoundException($name, $params);
    }
    
    public function addRoute(RouteData $data): void
    {
        $this->routes->addRoute(new RegexpRoute($data->name, $data->path, $data->handler, $data->methods, $data->options));
    }

}
