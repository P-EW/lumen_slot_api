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

$router->post('login', 'AuthController@login');
$router->post('register', 'AuthController@register');
$router->get('scores', 'ScoreController@index');
$router->post('freeroll', 'ScoreController@freeroll');

//have to be logged
$router->group(['middleware' => 'auth:api'], function () use ($router) {
    $router->post('logout', 'AuthController@logout');
    $router->post('roll', 'ScoreController@roll');
});
