<?

use Utopia\App;
use Utopia\CLI\Console;
use Utopia\Database\Document;
use Utopia\Database\Query;
use Utopia\Validator;
use Utopia\Validator\Text;

App::get('/v1/posts')
    ->label('sdk.namespace', 'posts')
    ->label('sdk.method', 'listPosts')
    ->inject('request')
    ->inject('response')
    ->inject("db")
    ->action(
        function ($request, $response, $database) {
            /** @var  Utopia\Database\Database $database */
            $posts = $database->find("posts");

            $response
                ->json(['posts' => $posts]);
        }
    );

App::get('/v1/posts/:postId')
    ->label('sdk.namespace', 'posts')
    ->label('sdk.method', 'getPost')
    ->param('postId', '', new Text(255))
    ->inject('request')
    ->inject('response')
    ->inject("db")
    ->action(
        function ($postId, $request, $response, $database) {
            /** @var  Utopia\Database\Database $database */

            $post = $database->getDocument("posts", $postId);

            if ($post->isEmpty()) {
                return $response->setStatusCode(404)->json(["error" => "Post not found."]);
            }

            $response
                ->json(['post' => $post]);
        }
    );

App::post('/v1/posts')
    ->label('sdk.namespace', 'posts')
    ->label('sdk.method', 'createPost')
    ->param('author', '', new Text(255))
    ->param('title', '', new Text(255))
    ->param('text', '', new Text(16777216))
    ->inject('request')
    ->inject('response')
    ->inject("db")
    ->action(
        function ($author, $title, $text, $request, $response, $database) {
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

App::delete('/v1/posts')
    ->label('sdk.namespace', 'posts')
    ->label('sdk.method', 'deletePost')
    ->param('id', '', new Text(255))
    ->inject('request')
    ->inject('response')
    ->inject("db")
    ->action(
        function ($postId, $request, $response, $database) {
            /** @var  Utopia\Database\Database $database */

            $post = $database->getDocument("posts", $postId);

            if ($post->isEmpty()) {
                return $response->setStatusCode(404)->json(["error" => "Post not found."]);
            }

            $success = $database->deleteDocument("posts", $postId);

            $response
                ->json(['success' => $success]);
        }
    );


App::patch('/v1/posts')
    ->label('sdk.namespace', 'posts')
    ->label('sdk.method', 'updatePost')
    ->param('id', '', new Text(255))
    ->param('title', '', new Text(255))
    ->param('text', '', new Text(16777216))
    ->inject('request')
    ->inject('response')
    ->inject("db")
    ->action(
        function ($postId, $title, $text, $request, $response, $database) {
            /** @var  Utopia\Database\Database $database */

            $post = $database->getDocument("posts", $postId);

            if ($post->isEmpty()) {
                return $response->setStatusCode(404)->json(["error" => "Post not found."]);
            }

            $post->setAttribute("title", $title);
            $post->setAttribute("text", $text);

            $success = $database->updateDocument("posts", $postId, $post);

            $response
                ->json(['post' => $post]);
        }
    );
