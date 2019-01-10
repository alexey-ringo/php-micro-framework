<?php
namespace App\Http\Action;

use App\Http\Middleware\BasicAuthMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

class CabinetAction {

    public function __invoke(ServerRequestInterface $request) {
        //Получаем имя прошедшего регистрацию пользователя из аттрибутов, упакованных в декораторе
        $username = $request->getAttribute(BasicAuthMiddleware::ATTRIBUTE);
        
        return new HtmlResponse('I am logged as ' . $username);
 
    }
}
