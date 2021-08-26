<?

use Utopia\App;
use Utopia\Database\Document;
use Utopia\Validator;

App::get('/posts')
    ->inject('request')
    ->inject('response')
    ->inject("db")
    ->action(
        function($request, $response, $database) {
            /** @var  Utopia\Database\Database $database */
           $posts = $database->find("posts");

            $response
              ->json(['posts' => $posts]);
        }
    );

App::post('/posts')
    ->inject('request')
    ->inject('response')
    ->inject("db")
    ->param('author', '', Validator::TYPE_STRING)
    ->param('title', '', Validator::TYPE_STRING)
    ->param('text', '', Validator::TYPE_STRING)
    ->action(
        function($request, $response, $database, $author, $title, $text) {
            /** @var  Utopia\Database\Database $database */
           $newPost = $database->createDocument("posts", new Document([
               "title" => $title,
               "author" => $author,
               "text" => $text
           ]));

            $response
              ->json(['post' => $newPost]);
        }
    );