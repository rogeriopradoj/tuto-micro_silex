<?php
require 'vendor/.composer/autoload.php';

$app = new Silex\Application(); 
$app['debug'] = true;


$app->get('/hello/{name}', function($name) use($app) { 
    return 'Hello '.$app->escape($name); 
}); 

$blogPosts = array(
    1 => array(
        'date'      => '2011-03-29',
        'author'    => 'igorw',
        'title'     => 'Using Silex',
        'body'      => '...',
    ),
);

$app->get('/blog', function () use ($blogPosts) {
    $output = '';
    foreach ($blogPosts as $post) {
        $output .= $post['title'];
        $output .= '<br />';
    }

    return $output;
});

$app->get('/blog/show/{id}', function (Silex\Application $app, $id) use ($blogPosts) {
    if (!isset($blogPosts[$id])) {
        $app->abort(404, "Post $id does not exist.");
    }

    $post = $blogPosts[$id];

    return  "<h1>{$post['title']}</h1>".
            "<p>{$post['body']}</p>";
});

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->post('/feedback', function (Request $request) {
    $message = $request->get('message');
    mail('feedback@yoursite.com', '[YourSite] Feedback', $message);

    return new Response('Thank you for your feedback!', 201);
});

$app->error(function (\Exception $e, $code) {
    switch ($code) {
        case 404:
            $message = 'The requested page could not be found.';
            break;
        default:
            $message = 'We are sorry, but something went terribly wrong.';
    }

    return new Response($message, $code);
});

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options'            => array(
        'driver'    => 'pdo_sqlite',
        'path'      => __DIR__.'/data/app.db',
    ),
    'db.dbal.class_path'    => __DIR__.'/vendor/doctrine/doctrine-dbal/lib',
    'db.common.class_path'  => __DIR__.'/vendor/doctrine/doctrine-common/lib',
));

$app->get('/', function() use ($app) {
    
});

$app->get('/article/show/{id}', function ($id) use ($app) {
    $sql = "SELECT * FROM article WHERE id = ?";
    $post = $app['db']->fetchAssoc($sql, array((int) $id));

    return  "<h1>{$post['title']}</h1>".
            "<p>{$post['content']}</p>";
});

$app->run(); 