<?php
//Вкл вывод ошибок
ini_set('display_errors', 1);
//Активировали лог ошибок в полном режиме
error_reporting(E_ALL);

use App\Http\Action;
use App\Http\Middleware;
use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Pipeline\Pipeline;
use Framework\Http\Router\AuraRouterAdapter;
use Framework\Http\Router\Exception\RequestNotMatchedException;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Zend\Diactoros\ServerRequestFactory;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

### Initialization
$params = [
    'users' => [
        'admin' => 'adminpass',
        'user' => 'userpass'
        ],
    ];

//Создаем объект маршрутизатора-контейнера Аура
$aura = new Aura\Router\RouterContainer();
//Извлекаем из него объект коллекции маршрутов (карта маршрутов)
//это аналог нешего RouteCollection
$routes = $aura->getMap();

//И заполняем ее записями о трех маршрутах
//Обработчики марштутов переложил из анонимных функций в отдельные классы
//Объект класса с одной единственной функцией __invoke() 
//Для универсализации - вместо создания объекта получаю строковое имя класса с обработчиком
$routes->get('home', '/', Action\HelloAction::class);

$routes->get('about', '/about', Action\AboutAction::class);
//Через анонимную функцию вызываем Посредник аутентификации
$routes->get('cabinet', '/cabinet', [
    Middleware\ProfilerMiddleware::class,
    new Middleware\BasicAuthMiddleware($params['users']),
    Action\CabinetAction::class,
]);

$routes->get('blog', '/blog', Action\Blog\IndexAction::class);

$routes->get('blog_show', '/blog/{id}', Action\Blog\ShowAction::class)->tokens(['id' => '\d+']);

//
$router = new AuraRouterAdapter($aura);

//Приводит разные типы обработчика (объект Closure или строка имени класса или еще что либо) к единому типу callable
$resolver = new MiddlewareResolver();

### Running
//Извлекаем $request из суперглобальных массивов $_GET и т.д.
$request = ServerRequestFactory::fromGlobals();
try {
    //И передаем его в роутер на сматчивание с соответствующим ему маршрутом (парсим текущий маршрут)
    $result = $router->match($request);        
    //Если все успешно, то роутер вернет название маршрута, его обработчик и аттрибуты
    
    foreach ($result->getAttributes() as $attribute => $value) {
        //Проходим по всем аттрибутам и Примешиваем в реквест аттрибуты и их значения
        $request = $request->withAttribute($attribute, $value);
    }
    //Получаем массив всех записанных в маршрут обработчиков (Посредники и Action)
    $handlers = $result->getHandler();
    $pipeline = new Pipeline();
    
    //Либо проходим по массиву, либо преобразовываем в массив и все равно проходим по нему
    foreach (is_array($handlers) ? $handlers : [$handlers] as $handler) {
        $pipeline->pipe($resolver->resolve($handler));
    }
    //Передаем реквест (в итоге попадет в Action) и дефолтное иселючение
    //Возвращает либо результат выполнения Action либо результат дефольного исключения
    $response = $pipeline($request, new Middleware\NotFoundHandler());
} catch (RequestNotMatchedException $ex) {
    $handler = new Middleware\NotFoundHandler();
    $response = $handler($request);
    
}

### Postprocessing
$response = $response->withHeader('X-Developer', 'Alex_Ringo');

### Sending
$emitter = new SapiEmitter();
$emitter->emit($response);