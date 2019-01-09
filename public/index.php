<?php
//Вкл вывод ошибок
ini_set('display_errors', 1);
//Активировали лог ошибок в полном режиме
error_reporting(E_ALL);

use App\Http\Action;
use Framework\Http\ActionResolver;
use Framework\Http\Router\AuraRouterAdapter;
use Framework\Http\Router\Exception\RequestNotMatchedException;
//use Framework\Http\Router\RouteCollection;
//use Framework\Http\Router\SimpleRouter;
use Zend\Diactoros\Response\HtmlResponse;
//use Zend\Diactoros\Response\SapiEmitter;
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

$routes->get('cabinet', '/cabinet', new Action\CabinetAction($params['users']));

$routes->get('blog', '/blog', Action\Blog\IndexAction::class);

$routes->get('blog_show', '/blog/{id}', Action\Blog\ShowAction::class)->tokens(['id' => '\d+']);

//
$router = new AuraRouterAdapter($aura);

//Определяет тип обработчика (объект Closure или строка имени класса или еще что либо) и по разному его обрабатывает
$resolver = new ActionResolver();

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
    
    //Получаем обработчик из результатов маршрутизации и передаем его в объект AcrionResolver
    //ActionResolver на основании анализа типа обработчика создаст и вернет нужный обработчик маршрута
    /** @var callable $action */
    $action = $resolver->resolve($result->getHandler());
    

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