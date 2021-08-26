<?
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    require __DIR__.'/../vendor/autoload.php';
}

use Swoole\Http\Server;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Utopia\App;
use Utopia\Cache\Adapter\None;
use Utopia\Cache\Cache;
use Utopia\CLI\Console;
use Utopia\Database\Adapter\MariaDB;
use Utopia\Database\Database;
use Utopia\Preloader\Preloader;
use Utopia\Registry\Registry;
use Utopia\Swoole\Request;
use Utopia\Swoole\Response;

// PREPARE HTTP SERVER
$http = new Server("0.0.0.0", 8005);
$http->set([
    'worker_num' => 1,
]);
App::setMode(App::MODE_TYPE_PRODUCTION);

App::error(function ($error, $response) {
    Console::error($error);
    
    $response
    ->addHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
    ->addHeader('Expires', '0')
    ->addHeader('Pragma', 'no-cache')
    ->setStatusCode(500)
    ->json(['error' => "Unexpeced error: " . $error]);
}, ['error', 'response']);

// INIT REQUIRED STUFF
$preloader = new Preloader();
$preloader
    ->paths(realpath(__DIR__ . '/../app/controllers'))
    ->ignore(realpath(__DIR__ . '/../vendor'))
    ->load();

    $register = new Registry();
    $register->set('db', function () use($http) {

        $dbHost = App::getEnv('DB_HOST', '');
        $dbPort = App::getEnv('DB_PORT', '');
        $dbUser = App::getEnv('DB_USER', '');
        $dbPass = App::getEnv('DB_PASSWORD', '');
        $dbName = App::getEnv('DB_NAME', '');
        $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};charset=utf8mb4", $dbUser, $dbPass, [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
            PDO::ATTR_TIMEOUT => 3, // Seconds
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);


        $cache = new Cache(new None());

        $database = new Database(new MariaDB($pdo), $cache);

        $database->setNamespace($dbName);

        if(!$database->exists()) {
            $database->create();
        }

        return $database;
    });

    
    // INIT DATABASE (MIGRATE+SEEDS)
    $database = $register->get("db");

    $collectionName = "posts";
    if($database->getCollection($collectionName)->isEmpty()) {
        $database->createCollection($collectionName);
        $database->createAttribute($collectionName, 'text', Database::VAR_STRING, 16777216, true);
        $database->createAttribute($collectionName, 'author', Database::VAR_STRING, 255, true);
        $database->createAttribute($collectionName, 'title', Database::VAR_STRING, 255, true);    
    }


// PARSE THE HTTP REQUEST
$http->on('request', function (SwooleRequest $swooleRequest, SwooleResponse $swooleResponse) use ($register) {
    $request = new Request($swooleRequest);
    $response = new Response($swooleResponse);

    Console::success('Request start: ' . $request->getURI());
    
    App::setResource('db', function () use($register) {
        return $register->get("db");
    });
    

    $app = new App('UTC');

    $app->run($request, $response);
});

// START HTTP SERVER
Console::info('Server started on :8005');
$http->start();
