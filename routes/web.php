<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// $router->get( '/photos[/{max}/{page}/{search_by}]', function () use ($router) {
//     return $router->app->version();
// });
$router->group([ 'prefix' => 'api'], function() use( $router) {

    $router->get( '/photos[/{per_page}/{page}/{search_term}]', 'PhotoController@index' );
    $router->get( '/photos/random', 'PhotoController@get_random_photo' );
    // $router->get( '/photos/random_o', 'PhotoController@get_random_photo_o' );

});
