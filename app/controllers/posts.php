<?

use Utopia\App;
use Utopia\CLI\Console;
use Utopia\Database\Document;
use Utopia\Database\Query;
use Utopia\Validator;
use Utopia\Validator\Text;

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

    App::get('/posts/:postId')
    ->param('postId', '', new Text(255))
    ->inject('request')
    ->inject('response')
    ->inject("db")
    ->action(
        function($postId, $request, $response, $database) {
            /** @var  Utopia\Database\Database $database */

           $post = $database->getDocument("posts", $postId);

           if($post->isEmpty()) {
              return $response->setStatusCode(404)->json([ "error" => "Post not found." ]);
           }

            $response
              ->json(['post' => $post]);
        }
    );

    App::post('/posts')
    ->param('author', '', new Text(255))
    ->param('title', '', new Text(255))
    ->param('text', '', new Text(16777216))
        ->inject('request')
        ->inject('response')
        ->inject("db")
       
        ->action(
            function($author, $title, $text, $request, $response, $database) {
                /** @var  Utopia\Database\Database $database */
    
               $newPost = $database->createDocument("posts", new Document([
                   "title" => $title,
                   "author" => $author,
                   "text" => $text,
                   '$write' => ["role:all"],
                   '$read' => ["role:all"],
               ]));
    
                $response
                  ->json(['post' => $newPost]);
            }
        );

App::delete('/posts')
->param('id', '', new Text(255))
    ->inject('request')
    ->inject('response')
    ->inject("db")
    
    ->action(
        function($postId, $request, $response, $database) {
            /** @var  Utopia\Database\Database $database */

            $post = $database->getDocument("posts", $postId);

            if($post->isEmpty()) {
                return $response->setStatusCode(404)->json([ "error" => "Post not found." ]);
            }

            $success = $database->deleteDocument("posts", $postId);

            $response
                ->json(['success' => $success]);
        }
    );


App::patch('/posts')
    ->param('id', '', new Text(255))
    ->param('title', '', new Text(255))
    ->param('text', '', new Text(16777216))
    ->inject('request')
    ->inject('response')
    ->inject("db")
    ->action(
        function($postId, $title, $text, $request, $response, $database) {
            /** @var  Utopia\Database\Database $database */

            $post = $database->getDocument("posts", $postId);

            if($post->isEmpty()) {
                return $response->setStatusCode(404)->json([ "error" => "Post not found." ]);
            }

            $post->setAttribute("title", $title);
            $post->setAttribute("text", $text);

           $success = $database->updateDocument("posts", $postId, $post);

            $response
              ->json(['post' => $post]);
        }
    );
