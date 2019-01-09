<?php
namespace App\Http\Action;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\EmptyResponse;

class BasicAuthActionDecorator {
    
    private $next;
    private $users;
    
    public function __construct(callable $next, array $users)
    {
        $this->next = $next;
        $this->users = $users;
    }
    
    public function __invoke(ServerRequestInterface $request) {
        //$username = $_SERVER['PHP_AUTH_USER'] ?? null;
        //$password = $_SERVER['PHP_AUTH_FW'] ?? null;
        $username = $request->getServerParams()['PHP_AUTH_USER'] ?? null;
        $password = $request->getServerParams()['PHP_AUTH_PW'] ?? null;
        //Аутентификация
        if(!empty($username) && !empty($password)) {
            foreach($this->users as $name => $pass) {
                if($username === $name && $password === $pass) {
                    //Вызываем упакованный в декоратор Action, в данном примере - CabinetAction
                    //Упаковываем имя пользователя, прошедшего вход, в доп аттрибут запроса
                    return ($this->next)($request->withAttribute('username', $username));
                }
            }
        }
        
        return new EmptyResponse(401, ['WWW-Authenticate' => 'Basic realm=Restricted area']);
    }
    
}