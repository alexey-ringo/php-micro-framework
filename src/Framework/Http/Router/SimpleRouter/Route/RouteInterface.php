<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Framework\Http\Router\SimpleRouter\Route;

use Framework\Http\Router\Result;
use Psr\Http\Message\ServerRequestInterface;
/**
 *
 * @author alexringo
 */
interface RouteInterface {
    
public function match(ServerRequestInterface $request): ?Result;

public function generate($name, array $params = []): ?string;
}
