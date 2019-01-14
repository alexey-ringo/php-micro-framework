<?php
//Вкл вывод ошибок
ini_set('display_errors', 1);
//Активировали лог ошибок в полном режиме
error_reporting(E_ALL);

use App\Http\Action;
use App\Http\Middleware;
use Framework\Http\Application;
use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Router\AuraRouterAdapter;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Zend\Diactoros\ServerRequestFactory;

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

### Initialization
$params = [
    'debug' => true,
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
    new Middleware\BasicAuthMiddleware($params['users']),
    Action\CabinetAction::class,
]);

$routes->get('blog', '/blog', Action\Blog\IndexAction::class);

$routes->get('blog_show', '/blog/{id}', Action\Blog\ShowAction::class)->tokens(['id' => '\d+']);

//
$router = new AuraRouterAdapter($aura);

//Приводит разные типы обработчика (объект Closure или строка имени класса или еще что либо) к единому типу callable
$resolver = new MiddlewareResolver();
//Создаем Трубу глобально, для всех маршрутов, и инициализируем ее резолвером и дефолтной заглушкой
$app = new Application($resolver, new Middleware\NotFoundHandler());

//для всех маршрутов добавляем общий первый посредник для дебага
$app->pipe(new Middleware\ErrorHandlerMiddleware($params['debug']));
//для всех маршрутов добавляем общий второй посредник - credentials (строкой с именем класса)
//Предварительно резолвить уже не обязательно (выполняется в $app)
$app->pipe(Middleware\CredentialsMiddleware::class);

//для всех маршрутов добавляем общий третий посредник - Profiler в виде строки класса
$app->pipe(Middleware\ProfilerMiddleware::class);

//Разделение последнего посредника на 2 части:
//RouteMiddleware - определяет маршрут
$app->pipe(new Framework\Http\Middleware\RouteMiddleware($router));
//DispatchMiddleware - запускает итоговый обработчик маршрута на исполнение
$app->pipe(new Framework\Http\Middleware\DispatchMiddleware($resolver));

### Running
//Извлекаем $request из суперглобальных массивов $_GET и т.д.
$request = ServerRequestFactory::fromGlobals();

//Запуск всей цепочки Посредников (в т.ч. и вложенных)
//Передаем в глобальную Трубу изначальный входящий реквест (в итоге попадет в конечный  Action)
//Возвращает либо результат выполнения конечного Action либо результат дефолтной заглушки
$response = $app->run($request);

### Postprocessing
//Данные в хеадер ('X-Developer', 'Alex_Ringo') уже добавлены на уровне обработки в Посреднике

### Sending
$emitter = new SapiEmitter();
$emitter->emit($response);