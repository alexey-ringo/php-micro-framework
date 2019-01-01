<?php
//Вкл вывод ошибок
ini_set('display_errors', 1);
//Активировали лог ошибок в полном режиме
error_reporting(E_ALL);

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
if ($path === '/') {
    $name = $request->getQueryParams()['name'] ?? 'Guest';
    $response = new HtmlResponse('Hello, ' . $name . '!');
} elseif ($path === '/about') {
    $response = new HtmlResponse('I am a simple site');
    
    //Возвращаем список существующих постов
} elseif ($path === '/blog') {
    $response = new JsonResponse([
        ['id' => 2, 'title' => 'The Second Post'],
        ['id' => 1, 'title' => 'The First Post'],
    ]);
    
//Вычисление регуляркой числового значения после blog/... 
//и присваиваение его параметру, именнованному как id    
} elseif (preg_match('#^/blog/(?P<id>\d+)$#i', $path, $matches)) {
    $id = $matches['id'];
    if ($id > 2) {
        $response = new JsonResponse(['error' => 'Undefined page'], 404);
    } else {
        $response = new JsonResponse(['id' => $id, 'title' => 'Post #' . $id]);
    }
}

else {
    $response = new HtmlResponse('Undefined page', 404);
}

### Postprocessing
$response = $response->withHeader('X-Developer', 'Alex_Ringo');

### Sending
$emitter = new SapiEmitter();
$emitter->emit($response);