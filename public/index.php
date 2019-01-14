<?php
//Вкл вывод ошибок
ini_set('display_errors', 1);
//Активировали лог ошибок в полном режиме
error_reporting(E_ALL);

use App\Http\Action;
use App\Http\Middleware;
use Framework\Http\Application;
use Framework\Http\Pipeline\MiddlewareResolver;
use Framework\Http\Pipeline\Pipeline;
use Framework\Http\Router\AuraRouterAdapter;
use Framework\Http\Router\Exception\RequestNotMatchedException;
use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Zend\Diactoros\ServerRequestFactory;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
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

$app->pipe(function(ServerRequestInterface $request, callable $next) use($params) {
    try {
            return $next($request);
        } catch (\Throwable $e) {
            if ($params['debug']) {
                return new JsonResponse([
                    'error' => 'Server error',
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                    'trace' => $e->getTrace(),
                ], 500);
            }
            return new HtmlResponse('Server error', 500);
        }
});
//для всех маршрутов добавляем общий первый посредник - credentials (строкой с именем класса)
//Предварительно резолвить уже не обязательно (выполняется в $app)
$app->pipe(Middleware\CredentialsMiddleware::class);

//для всех маршрутов добавляем общий второй посредник - Profiler в виде строки класса
$app->pipe(Middleware\ProfilerMiddleware::class);

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
    
    //Весь массив обработчиков в маршруте записываем в глобальную Трубу в виде внутренней вложенной Трубы
    //Резолвинг массива обработчиков произойдет в Application, 
    //создание внутренней Трубы и добавление обработчиков в нее - в MiddlewareResolwer
    //там же будет и вложение внутренней Трубы в глобальную
    
    //Сейчас обработчики маршрута передаем сразу как есть, без предварительного резолвинга
    $app->pipe($result->getHandler());
    

} catch (RequestNotMatchedException $ex) {}

//Передаем в Трубу реквест (в итоге попадет в Action)
//Возвращает либо результат выполнения Action либо результат дефолтной заглушки
$response = $app->run($request);

### Postprocessing
//Данные в хеадер ('X-Developer', 'Alex_Ringo') уже добавлены на уровне обработки в Посреднике

### Sending
$emitter = new SapiEmitter();
$emitter->emit($response);