<?php
//Вкл вывод ошибок
ini_set('display_errors', 1);
//Активировали лог ошибок в полном режиме
error_reporting(E_ALL);

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
//use Zend\Diactoros\Response\SapiEmitter;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Zend\Diactoros\ServerRequestFactory;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

### Initialization
$request = ServerRequestFactory::fromGlobals();

### Preprocessing
//Если в запросе есть заголокок 'Content-Type' и в нем есть строка '#json#1'
//То перекодируем его в массив withParsedBody
//if(preg_match('#json#1', $request->getHeader('Content-Type'))) {
//    $request = $request->withParsedBody(json_decode($request->getBody()->getContents()));
//}

### Action
$path = $request->getUri()->getPath();
$action = null;

if ($path === '/') {
    $action = function(ServerRequestInterface $request) {
        $name = $request->getQueryParams()['name'] ?? 'Guest';
        return new HtmlResponse('Hello, ' . $name . '!');
    };
} elseif ($path === '/about') {
    $action = function() {
        return new HtmlResponse('I am a simple site');
    };
    
    //Возвращаем список существующих постов
} elseif ($path === '/blog') {
    $action = function() {
        return new JsonResponse([
            ['id' => 2, 'title' => 'The Second Post'],
            ['id' => 1, 'title' => 'The First Post'],
        ]);
    };
    
//Вычисление регуляркой числового значения после blog/...  
//и присваиваение его параметру, именнованному как id - в $matches   
} elseif (preg_match('#^/blog/(?P<id>\d+)$#i', $path, $matches)) {
    $request = $request->withAttribute('id', $matches['id']);
    //Передали id в массиве пользовательских аттирбутов объекта $request
    $action = function(ServerRequestInterface $request) {
        //Получили id из аттрибутов $request
        $id = $request->getAttribute('id');
        if ($id > 2) {
            return new JsonResponse(['error' => 'Undefined page'], 404);
        } else {
            return new JsonResponse(['id' => $id, 'title' => 'Post #' . $id]);
        }
    };
}

if($action) {
    //Передаем соответствующий (выбранный в условиях) $request в анонимную функцию
    $response = $action($request);
}
else {
    $response = new HtmlResponse('Undefined page', 404);
}

### Postprocessing
$response = $response->withHeader('X-Developer', 'Alex_Ringo');

### Sending
$emitter = new SapiEmitter();
$emitter->emit($response);