<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Framework\Http\Router;

use Framework\Http\Router\Exception\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

/**
 *
 * @author alexringo
 */
interface RouterInterface {
    /**
     * @param ServerRequestInterface $request
     * @throws RequestNotMatchedException
     * @return Result
     */
    public function match(ServerRequestInterface $request): Result;

    /**
     * @param $name
     * @param array $params
     * @throws RouteNotFoundException
     * @return string
     */
    public function generate($name, array $params): string;
    
    /**
     * @param RouteData $data
     */
    public function addRoute(RouteData $data): void;
}
