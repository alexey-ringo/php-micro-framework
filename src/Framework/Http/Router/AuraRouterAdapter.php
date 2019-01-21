<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Framework\Http\Router;

use Aura\Router\Exception\RouteNotFound;
use Aura\Router\RouterContainer;
use Aura\Router\Route;
use Framework\Http\Router\Exception\RequestNotMatchedException;
use Framework\Http\Router\Exception\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Description of AuraRouterAdapter
 *
 * @author alexringo
 */
class AuraRouterAdapter implements RouterInterface {
    
    private $aura;
    
    public function __construct(RouterContainer $aura)
    {
        $this->aura = $aura;
    }
    
    public function match(ServerRequestInterface $request): Result
    {
        //В Аура нельзя напрямую из маршрутизатора получать методы match() и generate()
        //В рамках одного роутера у Ауры несколько  специфических отдельных объектов
        //Для парсинга и матчинга сначала из роутера получаем объект $matcher
        $matcher = $this->aura->getMatcher();
        //А затем уже из внутреннего объекта $matcher обращаемся к match()
        //Если маршрутизатор Аура вернул подходящее маршруту правило, то:
        if ($route = $matcher->match($request)) {
            return new Result($route->name, $route->handler, $route->attributes);
        }
        throw new RequestNotMatchedException($request);
    }
    
    public function generate($name, array $params): string
    {
        //Получаем из маршрутизатора отдельный объект $generator
        $generator = $this->aura->getGenerator();
        try {
            //А уже из объекта $generator получаем метод generate()
            return $generator->generate($name, $params);
        } catch (RouteNotFound $e) {
            throw new RouteNotFoundException($name, $params, $e);
        }
    }
    
    public function addRoute($name, $path, $handler, array $methods, array $options): void
    {
        $map = $this->aura->getMap();
        //AuraRouter object
        $route = new Route();
        
        $route->name($name);
        $route->path($path);
        $route->handler($handler);
        
        //Обход всего массива $options на предмет правильности указания названий ключей
        foreach ($options as $key => $value) {
            switch ($key) {
                case 'tokens':
                    $route->tokens($value);
                    break;
                case 'defaults':
                    $route->defaults($value);
                    break;
                case 'wildcard':
                    $route->wildcard($value);
                    break;
                default:
                    throw new \InvalidArgumentException('Undefined option "' . $name . '"');
            }
        }
        $map->addRoute($route);
    }
}
