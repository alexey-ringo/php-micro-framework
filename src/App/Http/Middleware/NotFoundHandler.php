<?php

namespace App\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Diactoros\Response\HtmlResponse;

class NotFoundHandler implements RequestHandlerInterface {
    public function handle(ServerRequestInterface $request): ResponseInterface 
    {
        return new HtmlResponse('Error, Not Found Handler', 484);
    }
}