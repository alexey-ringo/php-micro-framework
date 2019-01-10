<?php
namespace App\Http\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;

class BasicAuthMiddleware {
    
    public const ATTRIBUTE = '_user';
    private $users;
    
    public function __construct(array $users)
    {
        $this->users = $users;
    }
    
    public function __invoke(ServerRequestInterface $request, callable $next) {
        //$username = $_SERVER['PHP_AUTH_USER'] ?? null;
        //$password = $_SERVER['PHP_AUTH_FW'] ?? null;
        $username = $request->getServerParams()['PHP_AUTH_USER'] ?? null;
        $password = $request->getServerParams()['PHP_AUTH_PW'] ?? null;
        //Аутентификация
        if(!empty($username) && !empty($password)) {
            foreach($this->users as $name => $pass) {
                if($username === $name && $password === $pass) {
                    //Вызываем Action, в данном примере - CabinetAction
                    //Передаем в него реквест с упаковкой имени пользователя, прошедшего в конструктор, в доп аттрибут запроса
                    return $next($request->withAttribute(self::ATTRIBUTE, $name));
                }
            }
        }
        
        return new EmptyResponse(401, ['WWW-Authenticate' => 'Basic realm=Restricted area']);
    }
    
}