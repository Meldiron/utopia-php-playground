<?php

use Utopia\App;
use Utopia\View;

App::setResource('layout', function() {
    $layout = new View(__DIR__.'/../views/default.phtml');
    $layout->setParam("litespeedLib", new View(__DIR__.'/../assets/Litespeed.js'));
    $layout->setParam("serviceLib", new View(__DIR__.'/../assets/Service.js'));

    return $layout;
});

App::shutdown(function ($response, $layout) {
    /** @var Appwrite\Utopia\Response $response */
    /** @var Utopia\View $layout */

    $response->html($layout->render());
}, ['response', 'layout'], 'home');

App::get('/')
    ->groups(["home"])
    ->inject('layout')
    ->action(function ($layout) {
        /** @var Utopia\View $layout */

        $page = new View(__DIR__.'/../views/pages/home.phtml');
        $page
            ->setParam('code',"a512");

        $layout
            ->setParam('body', $page);
    });