<?php
//Вкл вывод ошибок
ini_set('display_errors', 1);
//Активировали лог ошибок в полном режиме
error_reporting(E_ALL);

use App\Http\Action;
use Framework\Http\Router\Exception\RequestNotMatchedException;
use Framework\Http\Router\RouteCollection;
use Framework\Http\Router\Router;
use Zend\Diactoros\Response\HtmlResponse;
//use Zend\Diactoros\Response\SapiEmitter;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Zend\Diactoros\ServerRequestFactory;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

### Initialization

//Создаем коллекцию маршрутов
$routes = new RouteCollection();

//И заполняем ее записями о трех маршрутах
//Обработчики марштутов переложил из анонимных функций в отдельные классы
//Объект класса с одной единственной функцией __invoke() 

$routes->get('home', '/', new Action\HelloAction());

$routes->get('about', '/about', new Action\AboutAction());

$routes->get('blog', '/blog', new Action\Blog\IndexAction());

$routes->get('blog_show', '/blog/{id}', new Action\Blog\ShowAction(), ['id' => '\d+']);

//Создаем экземпляр роутера и инициализируем его созданной коллекцией маршрутов
$router = new Router($routes);

### Running
//Извлекаем $request из суперглобальных массивов $_GET и т.д.
$request = ServerRequestFactory::fromGlobals();
try {
    //И передаем его в роутер на сматчивание с соответствующим ему маршрутом
    $result = $router->match($request);
        
    //Если все успешно, то роутер вернет название маршрута, обработчик и аттрибуты
    foreach ($result->getAttributes() as $attribute => $value) {
        //Проходим по всем аттрибутам и Примешиваем в реквест аттрибуты и их значения
        $request = $request->withAttribute($attribute, $value);
    }
    /** @var callable $action */
    //Возвращает анонимную функцию-обработчик, привязанную к данному маршруту
    $action = $result->getHandler();

    //Запускаем анонимную функцию, передавая в нее реквест с примешанными аттрибутами
    $response = $action($request);
} catch (RequestNotMatchedException $ex) {
    $response = new HtmlResponse('Undefined page', 404);
}

### Postprocessing
$response = $response->withHeader('X-Developer', 'Alex_Ringo');

### Sending
$emitter = new SapiEmitter();
$emitter->emit($response);