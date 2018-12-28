<?php
//Вкл вывод ошибок
ini_set('display_errors', 1);
//Активировали лог ошибок в полном режиме
error_reporting(E_ALL);

use Zend\Diactoros\Response\HtmlResponse;
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
$name = $request->getQueryParams()['name'] ?? 'Guest';
$response = new HtmlResponse('Hello, ' . $name . '!');

### Postprocessing
$response = $response->withHeader('X-Developer', 'Alex_Ringo');

### Sending
$emitter = new SapiEmitter();
$emitter->emit($response);