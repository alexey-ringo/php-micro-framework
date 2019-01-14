<?php
namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CredentialsMiddleware {
    public function __invoke(ServerRequestInterface $request, callable $next) {
        /**  @var Psr\Http\Message\ResponseInterface $response */
        //Запускаем $next на дальнейшее исполнение цепочки обработчиков,
        $response = $next($request);
        // а в response добавляем заголовки
        return $response->withHeader('X-Developer', 'Alex_Ringo');
    }
}