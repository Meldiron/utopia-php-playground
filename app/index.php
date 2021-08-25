<?

// This is a demo showcasing that we create a resource and we expect to only have one instance.. If you visit GET /config-keys, it should add one item to the resource and then display them all. Instead, it creates new instance so you can see array with only 1 item with every refresh. This can be fixed by using Utopia register

require_once __DIR__.'/../vendor/autoload.php';

use Swoole\Http\Server;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Utopia\App;
use Utopia\CLI\Console;
use Utopia\Swoole\Request;
use Utopia\Swoole\Response;

class TodoStore {
    private $todos = array();

    function addTodo(string $message) {
        array_push($this->todos, array(
            "message" => $message,
            "id" => "todo_" . microtime(true)
        ));

        return;
    }

    function getTodos() {
        return $this->todos;
    }

}

$http = new Server("0.0.0.0", 8005);
$http->set([
    'worker_num' => 1,
]);

App::get('/config-keys')
    ->inject('request')
    ->inject('response')
    ->inject("todoStore")
    ->action(
        function($request, $response, $todoStore) {
            $todoStore->addTodo("Ahoj");

            $response
              ->json(['keys' => $todoStore->getTodos()]);
        }
    );

App::setMode(App::MODE_TYPE_PRODUCTION);




$http->on('request', function (SwooleRequest $swooleRequest, SwooleResponse $swooleResponse) {
    $request = new Request($swooleRequest);
    $response = new Response($swooleResponse);

    Console::success('Request start: ' . $request->getURI());
   
    App::setResource('todoStore', function () {
        Console::success("Create new resource");

        $todoStore = new TodoStore();
        return $todoStore;
    });
    

    $app = new App('UTC');

    $app->run($request, $response);
    
});

Console::info('Server started on :8005');
$http->start();
